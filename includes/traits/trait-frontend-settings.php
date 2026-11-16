<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TAWCVS_Frontend_Settings_Trait
 *
 * Handles frontend script enqueuing and settings-related checks.
 */
trait TAWCVS_Frontend_Settings_Trait {

	/**
	 * Enqueue scripts and stylesheets
	 */
	public function enqueue_scripts() {
		if ( ! isset( $this->generalSettings['disable-plugin-stylesheet'] ) || ! $this->generalSettings['disable-plugin-stylesheet'] ) {
			wp_enqueue_style( 'tawcvs-frontend', plugins_url( 'assets/css/frontend.css', TAWC_VS_PLUGIN_FILE ), array(), WCVS_PLUGIN_VERSION );
		}
		if ( is_shop() || is_product_category() || is_product_tag() ) {
			wp_enqueue_style( 'tawcvs-frontend-for-listing-pages', plugins_url( 'assets/css/frontend-list-products.css', TAWC_VS_PLUGIN_FILE ) );
		}
		wp_enqueue_script( 'tawcvs-frontend', plugins_url( 'assets/js/frontend.js', TAWC_VS_PLUGIN_FILE ), array( 'jquery' ), WCVS_PLUGIN_VERSION, true );

		// Only add threshold for debugging
		wp_localize_script( 'tawcvs-frontend', 'tawcvs_config', array(
			'large_product_threshold' => apply_filters( 'tawcvs_large_product_threshold', 100 )
		) );
    }

	public function get_updated_attribute_type( $attr ) {
		if ( empty( $attr ) ) {
			return '';
		}
		$supported_swatch_types = TA_WCVS()->types;
		$dropdown_to_label_setting = isset( $this->generalSettings['dropdown-to-label'] ) && $this->generalSettings['dropdown-to-label'];

		// If the type isn't supported, and we turned on the setting to convert dropdown to label/image
		// then we forced that type to the corresponding type
		if ( ! array_key_exists( $attr->attribute_type, $supported_swatch_types ) ) {

			if ( $dropdown_to_label_setting
			     && isset( $this->generalSettings[ 'dropdown-to-label-attribute-' . $attr->attribute_name ] )
			     && $this->generalSettings[ 'dropdown-to-label-attribute-' . $attr->attribute_name ] ) {

				$attr->attribute_type = 'label';

			} else {
				//Default attribute type should be select
				$attr->attribute_type = 'select';

			}

		}
		$attr->attribute_type = apply_filters( 'tawcvs_attribute_type', $attr->attribute_type, $attr, $supported_swatch_types );

		return $attr;
	}

	private function is_use_attribute_image_only() {
		return wc_string_to_bool( isset( $this->generalSettings['attribute-image-only'] ) ? $this->generalSettings['attribute-image-only'] : 0 );
	}

    private function is_disabled_checking_availability() {
		return wc_string_to_bool( isset( $this->generalSettings['disable-checking-availability'] ) ? $this->generalSettings['disable-checking-availability'] : 0 );
	}

    private function is_keep_disabled_variation_selectable() {
		return wc_string_to_bool( isset( $this->generalSettings['keep-showing-attribute-state'] ) ? $this->generalSettings['keep-showing-attribute-state'] : 0 );
	}

	private function is_disabled_variation_swatches( $product ) {
		if ( ! $product instanceof WC_Product_Variable ) {
			return false;
		}

		return 'yes' === get_post_meta( $product->get_id(), 'variation_swatches_disabled', true );
	}

	/**
	 * Return the boolean to check if tooltip is enabled in Archive/Shop/Product pages
	 *
	 * @return bool
	 */
	public function is_tooltip_enabled() {
		if ( ! is_product() ) {
			return false;
		}

		return wc_string_to_bool( isset( $this->generalSettings['enable-tooltip'] ) ? $this->generalSettings['enable-tooltip'] : 0 );
	}
	/**
	 *
	 * Return the boolean to check if show label on color swatches is enabled in General Settings page
	 *
	 * @return bool
	 */
	public function is_show_label_enabled() {
		if ( isset( $this->generalSettings['show-label-on-color-swatches'] ) && $this->generalSettings['show-label-on-color-swatches'] == 1 ) {
			return true;
		} else {
			return false;
		}
	}
}
