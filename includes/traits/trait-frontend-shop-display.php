<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TAWCVS_Frontend_Shop_Display_Trait
 *
 * Handles variation display on shop/archive pages.
 */
trait TAWCVS_Frontend_Shop_Display_Trait {

	public function tawcvs_show_clear_link_on_variations_on_shop_page( $content ) {
		if ( ! is_product() ) {
			return '';
		}

		return $content;
	}

	/**
	 * Showing the variation section before the Add to cart button
	 *
	 * @param $html
	 * @param $product
	 *
	 * @return mixed|void
	 */
	public function display_variations_on_shop_page_before_add_to_cart_btn( $html, $product ) {
		if ( ! $product instanceof WC_Product ) {
			global $product;
		}

		if ( $product instanceof WC_Product_Variable ) {

			//Removing the Show option button
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );

			//Rendering
			$alignment = isset( $this->archiveSettings['swatch-alignment'] ) ? $this->archiveSettings['swatch-alignment'] : 'left';
			echo '<div class="swatch-align-' . esc_attr( $alignment ) . '">';
			do_action( 'woocommerce_variable_add_to_cart' );
			echo '</div>';

			// fix variation doesn't appear on list page
			add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );

			// The swatch form has its own add-to-cart button; returning $html would print a second one.
			return '';
		} else {
			return $html;
		}

	}
}
