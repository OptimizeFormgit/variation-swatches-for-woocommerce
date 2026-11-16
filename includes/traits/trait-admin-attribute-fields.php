<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TAWCVS_Admin_Attribute_Fields_Trait
 *
 * Handles attribute term field rendering, saving, and column display.
 */
trait TAWCVS_Admin_Attribute_Fields_Trait {

	/**
	 * Init hooks for adding fields to attribute screen
	 * Save new term meta
	 * Add thumbnail column for attribute term
	 */
	public function init_attribute_hooks() {
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if ( empty( $attribute_taxonomies ) ) {
			return;
		}

		foreach ( $attribute_taxonomies as $tax ) {
			add_action( 'pa_' . $tax->attribute_name . '_add_form_fields', array( $this, 'add_attribute_fields' ) );
			add_action( 'pa_' . $tax->attribute_name . '_edit_form_fields', array(
				$this,
				'edit_attribute_fields'
			), 10, 2 );

			add_filter( 'manage_edit-pa_' . $tax->attribute_name . '_columns', array(
				$this,
				'add_attribute_columns'
			) );
			add_filter( 'manage_pa_' . $tax->attribute_name . '_custom_column', array(
				$this,
				'add_attribute_column_content'
			), 10, 3 );
		}

		add_action( 'created_term', array( $this, 'save_term_meta' ), 10, 2 );
		add_action( 'edit_term', array( $this, 'save_term_meta' ), 10, 2 );
	}

	/**
	 * Create hook to add fields to add attribute term screen
	 *
	 * @param string $taxonomy
	 */
	public function add_attribute_fields( $taxonomy ) {
		$attr = TA_WCVS()->get_tax_attribute( $taxonomy );
		if ( ! $attr || ! isset( $attr->attribute_type ) ) {
			return;
		}
		do_action( 'tawcvs_product_attribute_field', $attr->attribute_type, false, $taxonomy, 'add' );
	}

	/**
	 * Create hook to fields to edit attribute term screen
	 *
	 * @param object $term
	 * @param string $taxonomy
	 */
	public function edit_attribute_fields( $term, $taxonomy ) {
		$attr = TA_WCVS()->get_tax_attribute( $taxonomy );
		if ( ! $attr || ! isset( $attr->attribute_type ) ) {
			return;
		}
		do_action( 'tawcvs_product_attribute_field', $attr->attribute_type, $term, $taxonomy, 'edit' );
	}

	/**
	 * Print HTML of custom fields on attribute term screens
	 *
	 * @param $type
	 * @param $term
	 * @param $taxonomy
	 * @param $form
	 */
	public function attribute_fields( $type, $term, $taxonomy, $form ) {
		// Return if this is a default attribute type
		if ( in_array( $type, array( 'select', 'text' ) ) ) {
			return;
		}
		if ( $term instanceof WP_Term ) {
			$term_id = $term->term_id;
		} else {
			$term_id = false;
		}
		$value = get_term_meta( $term_id, $type, true );
		$html  = '';

		ob_start();
		switch ( $type ) {
			case 'image':
				$image = $value ? wp_get_attachment_image_src( $value ) : '';
				$image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';
				?>
                <div class="tawcvs-term-image-thumbnail" style="float:left;margin-right:10px;">
                    <img src="<?php echo esc_url( $image ) ?>" width="60px" height="60px"/>
                </div>
                <div style="line-height:60px;">
                    <input type="hidden" class="tawcvs-term-image" name="image" value="<?php echo esc_attr( $value ) ?>"/>
                    <button type="button" class="tawcvs-upload-image-button button"><?php esc_html_e( 'Upload/Add image', 'wcvs' ); ?></button>
                    <button type="button" class="tawcvs-remove-image-button button <?php echo $value ? '' : 'hidden' ?>"><?php esc_html_e( 'Remove image', 'wcvs' ); ?></button>
                </div>
				<?php
				break;
			case 'radio':
				?>
				<input type="hidden" name="radio" />
				<?php
				break;
			default:
				?>
                <input type="text" id="term-<?php echo esc_attr( $type ) ?>" name="<?php echo esc_attr( $type ) ?>" value="<?php echo esc_attr( $value ) ?>"/>
				<?php
				break;
		}
		$html .= ob_get_clean();
		$html = apply_filters( 'tawcvs_tooltip_attributes', $html, $type, $term, $taxonomy, $form );

		if ( $type != 'radio' ) {
			echo sprintf(
				'<%s class="form-field">%s<label for="term-%s">%s</label>%s',
				'edit' == $form ? 'tr' : 'div',
				'edit' == $form ? '<th>' : '',
				esc_attr( $type ),
				TA_WCVS()->types[ $type ],
				'edit' == $form ? '</th><td>' : ''
			);

			echo $html;

			// Print the close tag of field container
			echo 'edit' == $form ? '</td></tr>' : '</div>';
		} else {
			echo $html;
		}
	}

	/**
	 * Save term meta
	 *
	 * @param int $term_id
	 * @param int $tt_id
	 */
	public function save_term_meta( $term_id, $tt_id ) {
		foreach ( TA_WCVS()->types as $type => $label ) {
			if ( isset( $_POST[ $type ] ) ) {
				update_term_meta( $term_id, $type, sanitize_text_field( $_POST[ $type ] ) );
				do_action( 'tawcvs_after_save_term_meta', $term_id, $type );
			}
		}
	}

	/**
	 * Add thumbnail column to column list
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_attribute_columns( array $columns ) {
		$new_columns          = array();
		if ( isset( $columns['cb']) ) {
			$new_columns['cb']    = $columns['cb'];
			$new_columns['thumb'] = '';
			unset( $columns['cb'] );
		}

		return array_merge( $new_columns, $columns );
	}

	/**
	 * Render thumbnail HTML depend on attribute type
	 *
	 * @param $columns
	 * @param $column
	 * @param $term_id
	 *
	 * @return mixed|void
	 */
	public function add_attribute_column_content( $columns, $column, $term_id ) {
		if ( 'thumb' !== $column ) {
			return $columns;
		}

		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? sanitize_text_field( $_REQUEST['taxonomy'] ) : '';
		if ( empty( $taxonomy ) ) {
			return $columns;
		}

		$attr = TA_WCVS()->get_tax_attribute( $taxonomy );
		if ( ! $attr || ! isset( $attr->attribute_type ) ) {
			return $columns;
		}

		$value = get_term_meta( $term_id, $attr->attribute_type, true );

		switch ( $attr->attribute_type ) {
			case 'color':
				$formatted_color_style = TA_WC_Variation_Swatches::generate_color_style( $term_id, $value );
				printf( '<div class="swatch-preview swatch-color" style="background:%s;"></div>', esc_attr( $formatted_color_style ) );
				break;

			case 'image':
				$image = $value ? wp_get_attachment_image_src( $value ) : '';
				$image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';
				printf( '<img class="swatch-preview swatch-image" src="%s" width="44px" height="44px">', esc_url( $image ) );
				break;

			case 'label':
				printf( '<div class="swatch-preview swatch-label">%s</div>', esc_html( $value ) );
				break;
		}
	}
}
