<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TAWCVS_Admin_Product_Tab_Trait
 *
 * Handles the Variation Swatches product data tab in WooCommerce.
 */
trait TAWCVS_Admin_Product_Tab_Trait {

	/**
	 * Add the custom product tab for Variation swatches
	 *
	 * @param $product_data_tabs
	 *
	 * @return mixed
	 */
	public function add_custom_swatch_variation_tab( $product_data_tabs ) {
		$product_data_tabs['variation-swatches'] = array(
			'label'    => __( 'Variation Swatches', 'wcvs' ),
			'target'   => 'variation_swatches_options',
			'class'    => array( 'show_if_variable' ),
			'priority' => 61,
		);

		return $product_data_tabs;
	}

	/**
	 * Rendering the product data panel for Swatch Variation settings
	 */
	public function product_tab_variation_swatches_panel() { ?>
        <div id="variation_swatches_options" class="panel woocommerce_options_panel">
            <div class="options_group">
				<?php
				woocommerce_wp_checkbox( array(
					'id'            => 'variation_swatches_disabled',
					'wrapper_class' => 'show_if_variable',
					'label'         => __( 'Disable Swatches', 'wcvs' ),
					'default'       => '0',
					'desc_tip'      => false,
					'description'   => __( 'Show the default dropdown selection instead of swatches settings', 'wcvs' )
				) );

				if ( ! TA_WC_Variation_Swatches::is_pro_addon_active() ) {
					woocommerce_wp_select(
						array(
							'wrapper_class'     => 'wcvs-pro-feature',
							'id'                => 'single_variation_preview',
							'label'             => __( 'Single variation preview attributes', 'wcvs' ),
							'options'           => array(
								'' => __( ' - Choose Attribute - ', 'wcvs' )
							),
							'value'             => '',
							'custom_attributes' => array(
								'disabled' => 'disabled'
							)
						)
					);
					woocommerce_wp_select(
						array(
							'wrapper_class'     => 'wcvs-pro-feature',
							'id'                => 'override_single_attribute',
							'label'             => __( 'Single attribute for using in catalog pages', 'wcvs' ),
							'options'           => array(
								'' => __( ' - Choose Attribute - ', 'wcvs' )
							),
							'value'             => '',
							'custom_attributes' => array(
								'disabled' => 'disabled'
							)
						)
					);
					TA_WC_Variation_Swatches::get_template( 'admin/pro-feature-popup.php' );
				}

				do_action( 'variation_swatches_product_panel' );
				?>
            </div>
        </div>
		<?php
	}

	/**
	 * Saving custom field of product data tab
	 *
	 * @param $post_id
	 */
	public function save_custom_product_data( $post_id ) {
		$variation_swatches_disabled = isset( $_POST['variation_swatches_disabled'] ) ? sanitize_text_field( wp_unslash( $_POST['variation_swatches_disabled'] ) ) : 'no';
		update_post_meta( $post_id, 'variation_swatches_disabled', $variation_swatches_disabled );
	}
}
