<?php

/**
 * Class TA_WC_Variation_Swatches_Admin
 */
class TA_WC_Variation_Swatches_Admin {
	/**
	 * The single instance of the class
	 *
	 * @var TA_WC_Variation_Swatches_Admin
	 */
	protected static $instance = null;

	private $generalSettings;

	/**
	 * Main instance
	 *
	 * @return TA_WC_Variation_Swatches_Admin
	 */
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'includes' ) );
		add_action( 'admin_init', array( $this, 'init_attribute_hooks' ) );
		add_action( 'admin_print_scripts', array( $this, 'enqueue_scripts' ) );

		// Restore attributes
		add_action( 'admin_notices', array( $this, 'restore_attributes_notice' ) );
		add_action( 'admin_init', array( $this, 'restore_attribute_types' ) );

		// Display attribute fields
		add_action( 'tawcvs_product_attribute_field', array( $this, 'attribute_fields' ), 10, 4 );

		add_filter( 'woosuite_core_module_settings_url', array(
			$this,
			'render_the_setting_url_in_core_plugin'
		), 10, 2 );

		include_once( dirname( __FILE__ ) . '/class-menu-page.php' );
		new VSWC_Settings_Page();

		$latest_option = get_option( 'woosuite_variation_swatches_option', array() );

		$this->generalSettings = isset( $latest_option['general'] ) ? $latest_option['general'] : array();
	}

	/**
	 * Rendering the setting url for this plugin in the dashboard page of Woosuite Core
	 *
	 * @param $url
	 * @param $module
	 *
	 * @return mixed|string|void
	 */
	function render_the_setting_url_in_core_plugin( $url, $module ) {
		if ( $module === WCVS_PLUGIN_NAME ) {
			$url = admin_url( 'admin.php?page=variation-swatches-settings' );
		}

		return $url;
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( dirname( __FILE__ ) . '/class-admin-product.php' );
		include_once( dirname( __FILE__ ) . '/class-menu-page.php' );
		include_once( dirname( __FILE__ ) . '/class-setting-fields-manager.php' );
		include_once( dirname( __FILE__ ) . '/class-setting-fields-renderer.php' );
		new VSWC_Setting_Fields_Renderer();
	}

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
	 * Load stylesheet and scripts in edit product attribute screen
	 */
	public function enqueue_scripts() {
		$screen   = get_current_screen();
		$dir_name = dirname( __FILE__ );

		if ( strpos( $screen->id, 'variation-swatches-addons' ) !== false ) {
			wp_enqueue_style( 'tawcvs-admin-addons', plugins_url( '/assets/css/admin-addons-page.css', $dir_name ), array() );
		}

		if ( strpos( $screen->id, 'edit-pa_' ) === false && strpos( $screen->id, 'product' ) === false ) {
			return;
		}

		// Don't let the below styles and css affect other woosuite plugin pages
		if ( strpos( $screen->id, 'woosuite_page_' ) !== false ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style( 'tawcvs-admin', plugins_url( '/assets/css/admin.css', $dir_name ), array( 'wp-color-picker' ), '20160615' );
		wp_enqueue_script( 'tawcvs-admin', plugins_url( '/assets/js/admin.js', $dir_name ), array(
			'jquery',
			'wp-color-picker',
			'wp-util'
		), '20170113', true );

		wp_localize_script(
			'tawcvs-admin',
			'tawcvs',
			array(
				'i18n'        => array(
					'mediaTitle'  => esc_html__( 'Choose an image', 'wcvs' ),
					'mediaButton' => esc_html__( 'Use image', 'wcvs' ),
				),
				'placeholder' => WC()->plugin_url() . '/assets/images/placeholder.png'
			)
		);

	}

	/**
	 * Display a notice of restoring attribute types
	 */
	public function restore_attributes_notice() {
		if ( get_transient( 'tawcvs_attribute_taxonomies' ) && ! get_option( 'tawcvs_restore_attributes_time' ) ) {
			?>
            <div class="notice-warning notice is-dismissible">
                <p>
					<?php
					esc_html_e( 'Found a backup of product attributes types. This backup was generated at', 'wcvs' );
					echo ' ' . date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), get_option( 'tawcvs_backup_attributes_time' ) ) . '.';
					?>
                </p>
                <p>
                    <a href="<?php echo esc_url( add_query_arg( array(
						'tawcvs_action' => 'restore_attributes_types',
						'tawcvs_nonce'  => wp_create_nonce( 'restore_attributes_types' )
					) ) ); ?>">
                        <strong><?php esc_html_e( 'Restore product attributes types', 'wcvs' ); ?></strong>
                    </a>
                    |
                    <a href="<?php echo esc_url( add_query_arg( array(
						'tawcvs_action' => 'dismiss_restore_notice',
						'tawcvs_nonce'  => wp_create_nonce( 'dismiss_restore_notice' )
					) ) ); ?>">
                        <strong><?php esc_html_e( 'Dismiss this notice', 'wcvs' ); ?></strong>
                    </a>
                </p>
            </div>
			<?php
		} elseif ( isset( $_GET['tawcvs_message'] ) && 'restored' == $_GET['tawcvs_message'] ) {
			?>
            <div class="notice-warning settings-error notice is-dismissible">
                <p><?php esc_html_e( 'All attributes types have been restored.', 'wcvs' ) ?></p>
            </div>
			<?php
		}
	}

	/**
	 * Restore attribute types
	 */
	public function restore_attribute_types() {
		if ( ! isset( $_GET['tawcvs_action'] ) || ! isset( $_GET['tawcvs_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['tawcvs_nonce'], $_GET['tawcvs_action'] ) ) {
			return;
		}

		if ( 'restore_attributes_types' == $_GET['tawcvs_action'] ) {
			global $wpdb;

			$attribute_taxnomies = get_transient( 'tawcvs_attribute_taxonomies' );

			foreach ( $attribute_taxnomies as $id => $attribute ) {
				$wpdb->update(
					$wpdb->prefix . 'woocommerce_attribute_taxonomies',
					array( 'attribute_type' => $attribute->attribute_type ),
					array( 'attribute_id' => $id ),
					array( '%s' ),
					array( '%d' )
				);
			}

			update_option( 'tawcvs_restore_attributes_time', time() );
			delete_transient( 'tawcvs_attribute_taxonomies' );
			delete_transient( 'wc_attribute_taxonomies' );

			$url = remove_query_arg( array( 'tawcvs_action', 'tawcvs_nonce' ) );
			$url = add_query_arg( array( 'tawcvs_message' => 'restored' ), $url );
		} elseif ( 'dismiss_restore_notice' == $_GET['tawcvs_action'] ) {
			update_option( 'tawcvs_restore_attributes_time', 'ignored' );
			$url = remove_query_arg( array( 'tawcvs_action', 'tawcvs_nonce' ) );
		}

		if ( isset( $url ) ) {
			wp_redirect( $url );
			exit;
		}
	}

	/**
	 * Create hook to add fields to add attribute term screen
	 *
	 * @param string $taxonomy
	 */
	public function add_attribute_fields( $taxonomy ) {
		$attr = TA_WCVS()->get_tax_attribute( $taxonomy );
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
		$value         = get_term_meta( $term_id, $type, true );

		// Print the open tag of field container
		printf(
			'<%s class="form-field">%s<label for="term-%s">%s</label>%s',
			'edit' == $form ? 'tr' : 'div',
			'edit' == $form ? '<th>' : '',
			esc_attr( $type ),
			TA_WCVS()->types[ $type ],
			'edit' == $form ? '</th><td>' : ''
		);

		switch ( $type ) {
			case 'image':
				$image = $value ? wp_get_attachment_image_src( $value ) : '';
				$image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';
				?>
                <div class="tawcvs-term-image-thumbnail" style="float:left;margin-right:10px;">
                    <img src="<?php echo esc_url( $image ) ?>" width="60px" height="60px"/>
                </div>
                <div style="line-height:60px;">
                    <input type="hidden" class="tawcvs-term-image" name="image"
                           value="<?php echo esc_attr( $value ) ?>"/>
                    <button type="button"
                            class="tawcvs-upload-image-button button"><?php esc_html_e( 'Upload/Add image', 'wcvs' ); ?></button>
                    <button type="button"
                            class="tawcvs-remove-image-button button <?php echo $value ? '' : 'hidden' ?>"><?php esc_html_e( 'Remove image', 'wcvs' ); ?></button>
                </div>
				<?php
				break;

			default:
				?>
                <input type="text" id="term-<?php echo esc_attr( $type ) ?>" name="<?php echo esc_attr( $type ) ?>"
                       value="<?php echo esc_attr( $value ) ?>"/>
				<?php
				break;
		}
		// Print the close tag of field container
		echo 'edit' == $form ? '</td></tr>' : '</div>';
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

				//Additional data for color
				if ( 'color' === $type && '1' === $this->generalSettings['enable-dual-color'] ) {
					array_map( function ( $meta_key ) use ( $term_id ) {
						update_term_meta( $term_id, $meta_key, sanitize_text_field( isset( $_POST[ $meta_key ] ) ? $_POST[ $meta_key ] : '' ) );
					}, array( 'is-dual-color', 'secondary-color' ) );
				}
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
		$new_columns['cb']    = $columns['cb'];
		$new_columns['thumb'] = '';
		unset( $columns['cb'] );

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

		$attr  = TA_WCVS()->get_tax_attribute( $_REQUEST['taxonomy'] );
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
