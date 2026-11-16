<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TAWCVS_Frontend_Styles_Trait
 *
 * Handles custom inline CSS for swatch design settings.
 */
trait TAWCVS_Frontend_Styles_Trait {

	// A saved empty value prints nothing, so the browser drops the rule as it did before sanitising.
	private function sanitize_css_number( $value ) {
		return is_numeric( $value ) ? floatval( $value ) : '';
	}

	private function sanitize_css_unit( $value ) {
		return in_array( $value, array( 'px', 'rem', 'em', 'pt', '%' ), true ) ? $value : 'px';
	}

	public function apply_custom_design_styles() {
		if ( isset( $this->generalSettings['disable-plugin-stylesheet'] ) && ! $this->generalSettings['disable-plugin-stylesheet'] ) {
			$page = is_product() ? 'productDesign' : 'shopDesign';
			?>
            <style>
                .woocommerce div.product .cart.variations_form .tawcvs-swatches,
                .woocommerce:not(.archive) li.product .cart.variations_form .tawcvs-swatches,
                .woocommerce.single-product .cart.variations_form .tawcvs-swatches,
                .wc-product-table-wrapper .cart.variations_form .tawcvs-swatches,
                .woocommerce.archive .cart.variations_form .tawcvs-swatches {
                    margin-top: <?php echo $this->sanitize_css_number( isset($this->{$page}['wrm-top']) ? $this->{$page}['wrm-top'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['wrm-type']) ? $this->{$page}['wrm-type'] : 'px' ); ?>;
                    margin-right: <?php echo $this->sanitize_css_number( isset($this->{$page}['wrm-right']) ? $this->{$page}['wrm-right'] : '15' ) . $this->sanitize_css_unit( isset($this->{$page}['wrm-type']) ? $this->{$page}['wrm-type'] : 'px' ); ?>;
                    margin-bottom: <?php echo $this->sanitize_css_number( isset($this->{$page}['wrm-bottom']) ? $this->{$page}['wrm-bottom'] : '15' ) . $this->sanitize_css_unit( isset($this->{$page}['wrm-type']) ? $this->{$page}['wrm-type'] : 'px' ); ?>;
                    margin-left: <?php echo $this->sanitize_css_number( isset($this->{$page}['wrm-left']) ? $this->{$page}['wrm-left'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['wrm-type']) ? $this->{$page}['wrm-type'] : 'px' ); ?>;
                    padding-top: <?php echo $this->sanitize_css_number( isset($this->{$page}['wrp-top']) ? $this->{$page}['wrp-top'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['wrp-type']) ? $this->{$page}['wrp-type'] : 'px' ); ?>;
                    padding-right: <?php echo $this->sanitize_css_number( isset($this->{$page}['wrp-right']) ? $this->{$page}['wrp-right'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['wrp-type']) ? $this->{$page}['wrp-type'] : 'px' ); ?>;
                    padding-bottom: <?php echo $this->sanitize_css_number( isset($this->{$page}['wrp-bottom']) ? $this->{$page}['wrp-bottom'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['wrp-type']) ? $this->{$page}['wrp-type'] : 'px' ); ?>;
                    padding-left: <?php echo $this->sanitize_css_number( isset($this->{$page}['wrp-left']) ? $this->{$page}['wrp-left'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['wrp-type']) ? $this->{$page}['wrp-type'] : 'px' ); ?>;
                }

                .woocommerce div.product .cart.variations_form .tawcvs-swatches .swatch-item-wrapper,
                .woocommerce:not(.archive) li.product .cart.variations_form .tawcvs-swatches .swatch-item-wrapper,
                .woocommerce.single-product .cart.variations_form .tawcvs-swatches .swatch-item-wrapper,
                .wc-product-table-wrapper .cart.variations_form .tawcvs-swatches .swatch-item-wrapper,
                .woocommerce.archive .cart.variations_form .tawcvs-swatches .swatch-item-wrapper {
                <?php if(isset($this->{$page}['item-font']) && $this->{$page}['item-font']):?> font-size: <?php echo $this->sanitize_css_number( isset($this->{$page}['text-font-size']) ? $this->{$page}['text-font-size'] : '12' ) . $this->sanitize_css_unit( isset($this->{$page}['item-font-size-type']) ? $this->{$page}['item-font-size-type'] : 'px' ); ?>;
                <?php endif;?> margin-top: <?php echo $this->sanitize_css_number( isset($this->{$page}['mar-top']) ? $this->{$page}['mar-top'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['mar-type']) ? $this->{$page}['mar-type'] : 'px' ); ?> !important;
                    margin-right: <?php echo $this->sanitize_css_number( isset($this->{$page}['mar-right']) ? $this->{$page}['mar-right'] : '15' ) . $this->sanitize_css_unit( isset($this->{$page}['mar-type']) ? $this->{$page}['mar-type'] : 'px' ); ?> !important;
                    margin-bottom: <?php echo $this->sanitize_css_number( isset($this->{$page}['mar-bottom']) ? $this->{$page}['mar-bottom'] : '15' ) . $this->sanitize_css_unit( isset($this->{$page}['mar-type']) ? $this->{$page}['mar-type'] : 'px' ); ?> !important;
                    margin-left: <?php echo $this->sanitize_css_number( isset($this->{$page}['mar-left']) ? $this->{$page}['mar-left'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['mar-type']) ? $this->{$page}['mar-type'] : 'px' ); ?> !important;
                    padding-top: <?php echo $this->sanitize_css_number( isset($this->{$page}['pad-top']) ? $this->{$page}['pad-top'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['pad-type']) ? $this->{$page}['pad-type'] : 'px' ); ?> !important;
                    padding-right: <?php echo $this->sanitize_css_number( isset($this->{$page}['pad-right']) ? $this->{$page}['pad-right'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['pad-type']) ? $this->{$page}['pad-type'] : 'px' ); ?> !important;
                    padding-bottom: <?php echo $this->sanitize_css_number( isset($this->{$page}['pad-bottom']) ? $this->{$page}['pad-bottom'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['pad-type']) ? $this->{$page}['pad-type'] : 'px' ); ?> !important;
                    padding-left: <?php echo $this->sanitize_css_number( isset($this->{$page}['pad-left']) ? $this->{$page}['pad-left'] : '0' ) . $this->sanitize_css_unit( isset($this->{$page}['pad-type']) ? $this->{$page}['pad-type'] : 'px' ); ?> !important;
                }

                /* tooltip is a sibling of .swatch inside .swatch-item-wrapper */
                .woocommerce div.product .cart.variations_form .tawcvs-swatches .swatch-item-wrapper .swatch__tooltip,
                .woocommerce:not(.archive) li.product .cart.variations_form .tawcvs-swatches .swatch-item-wrapper .swatch__tooltip,
                .woocommerce.single-product .cart.variations_form .tawcvs-swatches .swatch-item-wrapper .swatch__tooltip,
                .wc-product-table-wrapper .cart.variations_form .tawcvs-swatches .swatch-item-wrapper .swatch__tooltip,
                .woocommerce.archive .cart.variations_form .tawcvs-swatches .swatch-item-wrapper .swatch__tooltip {
                <?php if(isset($this->toolTipDesign['item-font']) && $this->toolTipDesign['item-font']):?> font-size: <?php echo $this->sanitize_css_number( isset($this->toolTipDesign['text-font-size']) ? $this->toolTipDesign['text-font-size'] : '14' ) . $this->sanitize_css_unit( isset($this->toolTipDesign['item-font-size-type']) ? $this->toolTipDesign['item-font-size-type'] : 'px' ); ?>;
                <?php endif;?> width: <?php echo isset($this->toolTipDesign['width']) ? $this->sanitize_css_number( $this->toolTipDesign['width'] ) . 'px' : 'auto'; ?>;
                    max-width: <?php echo isset($this->toolTipDesign['max-width']) ? $this->sanitize_css_number( $this->toolTipDesign['max-width'] ) . 'px' : '100%'; ?>;
                    line-height: <?php
					if ( isset( $this->toolTipDesign['line-height'] ) && is_numeric( $this->toolTipDesign['line-height'] ) ) {
						echo floatval( $this->toolTipDesign['line-height'] ) . 'px';
					} else {
						echo 'unset'; // not saved or empty (the settings page default)
					}
					?>;
                }
            </style>
			<?php
		}
	}
}
