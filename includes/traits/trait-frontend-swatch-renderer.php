<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TAWCVS_Frontend_Swatch_Renderer_Trait
 *
 * Handles swatch HTML rendering for swatches on product pages.
 */
trait TAWCVS_Frontend_Swatch_Renderer_Trait {

	/**
	 * Filter function to add swatches bellow the default selector
	 *
	 * @param $html
	 * @param $args
	 *
	 * @return string
	 */
	public function get_swatch_html( $html, $args ) {
		global $woocommerce_loop;

		$attr = TA_WCVS()->get_tax_attribute( $args['attribute'] );

		// Return if this is normal attribute
		if ( empty( $attr ) || ! $args['product'] instanceof WC_Product_Variable ) {
			return $html;
		}

		$options = $args['options'];
		$product = $args['product'];
		$attribute_tax_name = $args['attribute'];
		$class = "variation-selector variation-select-{$attr->attribute_type}";
		$swatches = '';
		$is_product_page = is_product();
		$defined_limit = apply_filters( 'tawcvs_swatch_limit_number', 0 );
		$out_of_stock_state = apply_filters( 'tawcvs_out_of_stock_state', '' );

		//If this product has disabled the variation swatches
		if ( $this->is_disabled_variation_swatches( $product ) ) {
			return $html;
		}

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute_tax_name ) ) {
			$attributes = $product->get_variation_attributes();
			$options = $attributes[ $attribute_tax_name ];
		}


		if ( empty( $attr->attribute_type ) ) {
			return $html;
		}

		// Add new option for tooltip to $args variable.
		$args['tooltip'] = apply_filters( 'tawcvs_tooltip_enabled', $this->is_tooltip_enabled() );

		//Get the product variation detail for each attribute
		//If there are more than one attributes, the first one will be applied
		$collected_variations = array();

		if ( TA_WC_Variation_Swatches::is_pro_addon_active() && ! $this->is_use_attribute_image_only() ) {
			$collected_variations = TA_WC_Variation_Swatches::get_detailed_product_variations( $product, $attribute_tax_name );
		}

		if ( ! empty( $options ) && taxonomy_exists( $attribute_tax_name ) ) {
			// Get terms if this is a taxonomy - ordered. We need the names too.
			$terms = $this->get_product_variation_term( $product, $defined_limit, $attribute_tax_name, $options );

			foreach ( $terms as $term ) {

				//Check if we have the product variable for this attribute
				if ( isset( $collected_variations[ $term->slug ] ) ) {
					$args['variation_product'] = $collected_variations[ $term->slug ];
				} else {
					unset( $args['variation_product'] );
				}

				$swatches .= apply_filters( 'tawcvs_swatch_html', '', $term, $attr->attribute_type, $args, $product );

			}
			//If we are on shop/archived page (not product page), we will check the defined limit number of variations
			//the product still have more variations -> show the view more icon
			if ( ( ! $is_product_page || ( isset( $woocommerce_loop['name'] ) && $woocommerce_loop['name'] == 'related' ) )
			     && 0 < $defined_limit
			     && count( $options ) > $defined_limit ) {
				$swatches .= apply_filters( 'tawcvs_swatch_show_more_html', '', $product );
			}
		}

		if ( ! empty( $swatches ) ) {
			$class .= ' hidden';
			$swatches = '<div class="tawcvs-swatches oss-' . $out_of_stock_state . '" data-attribute_name="attribute_' . esc_attr( $attribute_tax_name ) . '">' . $swatches . '</div>';
			$html = '<div class="' . esc_attr( $class ) . '">' . $html . '</div>' . $swatches;
		}
		return $html;
	}

	/**
	 * Print HTML of a single swatch
	 *
	 * @param $html
	 * @param $term
	 * @param $type
	 * @param $args
	 *
	 * @return string
	 */
	public function swatch_html( $html, $term, $type, $args, $product ) {

		$selected = sanitize_title( $args['selected'] ) == $term->slug ? 'selected' : '';
		$name = esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $args['attribute'], $product ) );

        $tooltip_text = esc_html( apply_filters( 'woocommerce_variation_option_description_text', ! empty( $term->description ) ? $term->description : $term->name ) );
		$tooltip = $this->get_tooltip_html( '', $term, $tooltip_text, $args );
		$tooltip = apply_filters( 'tawcvs_tooltip_html', $tooltip, $term, $tooltip_text, $args );

		$swatchShape = esc_attr( isset( $this->generalSettings['swatch-shape'] ) ? $this->generalSettings['swatch-shape'] : 'circle' );

		$selectable_class = '';
		if ( $this->is_keep_disabled_variation_selectable() &&
		     $this->is_disabled_checking_availability() &&
		     isset( $args['variation_product']['woosuite_purchasable'] ) &&
		     $args['variation_product']['woosuite_purchasable'] === false ) {
			$selectable_class = ' woosuite-selectable ';
		}
		$class = ' swatch-type-' . esc_attr( $type );

        if( isset( $args['variation_product']['woosuite_purchasable'] ) &&
                $args['variation_product']['woosuite_purchasable'] === false ) {
            $class .= ' disabled blur-cross ';
        }

		switch ( $type ) {
			case 'color':
				$main_color = get_term_meta( $term->term_id, 'color', true );
				$formatted_color_style = TA_WC_Variation_Swatches::generate_color_style( $term->term_id, $main_color );

				$r = $g = $b = 0;
				if ( ! empty( $main_color ) && preg_match( '/^#[0-9a-fA-F]{6}$/', $main_color ) ) {
					list( $r, $g, $b ) = sscanf( $main_color, "#%02x%02x%02x" );
				}

				if ( $this->is_show_label_enabled() ) {
					$class .= ' swatch-label ';
					$text_shadow = 'text-shadow: -1px -1px 0 #555, 1px -1px 0 #555, -1px 1px 0 #555, 1px 1px 0 #555;';
					$color = 'white';
				} else {
					$class .= ' swatch-color ';
					$text_shadow = '';
					$color = "rgba($r,$g,$b,0.5)";
				}

				$html = sprintf(
					'<div class="swatch-item-wrapper"><div class="swatch '.$selectable_class.' swatch-shape-' . $swatchShape . $class . ' swatch-%s %s" style="background:%s;color:%s;' . $text_shadow . '" data-value="%s"><span class="text">%s</span></div>%s</div>',
					esc_attr( $term->slug ),
					$selected,
					esc_attr( $formatted_color_style ),
					$color,
					esc_attr( $term->slug ),
					$name,
					$tooltip
				);
				break;
			case 'image':
				// First, we check the default thumbnail of attribute variation
				$attach_id = get_term_meta( $term->term_id, 'image', true );
				if ( ! empty( $attach_id ) ) {
					$image_url = wp_get_attachment_image_url( $attach_id, 'large' );
				} else {
					//If we also do not have default thumbnail, we will use the placeholder image of WC
					$image_url = WC()->plugin_url() . '/assets/images/placeholder.png';
				}
				$image_url = apply_filters( 'tawcvs_product_swatch_image_url', $image_url, $args );

				$html = sprintf(
					'<div class="swatch-item-wrapper"><div class="swatch '.$selectable_class.' swatch-shape-' . $swatchShape . $class . ' swatch-image swatch-%s %s %s" data-value="%s" style="background-image:url(%s);background-size:cover;%s"></div>%s</div>',
					esc_attr( $term->slug ),
					$selected,
					apply_filters( 'tawcvs_swatch_image_ratio_class', 'swatch-ratio-disabled' ),
					esc_attr( $term->slug ),
					esc_url( $image_url ),
					( isset( $this->generalSettings['image-position'] ) ? 'background-position:' . esc_attr( $this->generalSettings['image-position'] ) . ';' : '' ), // background position
					$tooltip
				);
				break;
			case 'label':
				$label = get_term_meta( $term->term_id, 'label', true );
				$label = $label ?: $name;
				$html = sprintf(
					'<div class="swatch-item-wrapper"><div class="swatch '.$selectable_class.' swatch-shape-' . $swatchShape . $class . ' swatch-label swatch-%s %s" data-value="%s"><span class="text">%s</span></div>%s</div>',
					esc_attr( $term->slug ),
					$selected,
					esc_attr( $term->slug ),
					esc_html( $label ),
					$tooltip
				);
				break;
			case 'radio':
				$selected = ! empty( $selected ) ? 'checked' : '';
				// The shop page can show the same option for several products, so the id
				// that links the label to its radio must be unique on the page
				$this->radio_count++;
				$radio_id = 'tawcvs-radio-' . ( $product instanceof WC_Product ? $product->get_id() : 0 ) . '-' . $term->term_id . '-' . $this->radio_count;
				$html = sprintf(
					'<div class="swatch-item-wrapper swatch-radio ' . $selectable_class . $class .' "><input id="%s" class="swatch" data-value="%s" type="radio" name="%s" value="%s" %s /><label for="%s" class="text swatch-label" style="display:inline">%s</label>%s</div>',
					esc_attr( $radio_id ),
					esc_attr( $term->slug ),
					'attribute_' . $term->taxonomy,
					esc_attr( $term->slug ),
					$selected,
					esc_attr( $radio_id ),
					esc_html( $term->name ),
					$tooltip
				);
				break;
		}

		return apply_filters( 'tawcvs_swatch_item_html', $html, $term, $type, $selected, $name, $tooltip, $swatchShape );
	}

	/**
	 * @param $product
	 * @param $defined_limit
	 * @param $attribute_tax_name
	 * @param $options
	 *
	 * @return array
	 */
	public function get_product_variation_term( $product, $defined_limit, $attribute_tax_name, $options ) {
		global $woocommerce_loop;

		$variation_count = count( $product->get_children() );
		if ( $variation_count > 100 ) {
			$cache_key = 'tawcvs_terms_' . $product->get_id() . '_' . $attribute_tax_name;
			$terms = wp_cache_get( $cache_key );

			if ( false === $terms ) {
				$terms = wc_get_product_terms( $product->get_id(), $attribute_tax_name, array( 'fields' => 'all' ) );
				wp_cache_set( $cache_key, $terms, '', 3600 );
			}
		} else {
			$terms = wc_get_product_terms( $product->get_id(), $attribute_tax_name, array( 'fields' => 'all' ) );
		}

		$terms = array_filter( $terms, function ( $term ) use ( $options ) {
			return in_array( $term->slug, $options, true );
		} );

		if ( $defined_limit > 0 && ( ! is_product() || $woocommerce_loop['name'] == 'related' ) ) {
			$terms = array_slice( $terms, 0, $defined_limit );
		}

		return $terms;
	}

	/**
	 * Render the tooltip html if it is enabled
	 *
	 * @param $html
	 * @param $term
	 * @param $name
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public function get_tooltip_html( $html, $term, $name, $args ) {
		if ( ! empty( $args['tooltip'] ) ) {

			// The per-option tooltip settings (show/hide, custom text, image) can only be edited in the Pro add-on.
			// Without Pro the tooltip always shows the option name, whatever was saved while Pro was active.
			$is_pro_addon_activated = TA_WC_Variation_Swatches::is_pro_addon_active();
			$show_tooltip = $is_pro_addon_activated ? get_term_meta( $term->term_id, 'show-tooltip', true ) : '';
			$show_tooltip = $show_tooltip === '' ? 1 : $show_tooltip;
			$attribute_fee = '';
			// The attribute fee is a Pro feature, its value comes from the Pro add-on
			if ( $is_pro_addon_activated && is_callable( array( 'Woosuite_Variation_Swatches_Pro_Public', 'get_attribute_fee' ) ) ) {
				$attribute_fee = Woosuite_Variation_Swatches_Pro_Public::get_attribute_fee( $term->term_id, get_the_ID() );
			}
			if ( $show_tooltip == 2 ) {
				// image tooltip, or the text tooltip when the image no longer exists
				$image = get_term_meta( $term->term_id, 'tooltip-image', true );
				$image = wp_get_attachment_image_src( $image, 'thumbnail' );
				if ( $image ) {
					$html = '<span class="swatch__tooltip"><img src="' . esc_url( $image[0] ) . '" /></span>';
				} else {
					$show_tooltip = 1;
				}
			}
			if ( $show_tooltip == 1 ) {
				// get default tooltip label
				$label = $is_pro_addon_activated ? get_term_meta( $term->term_id, 'tooltip-text', true ) : '';
				$label = ! empty( $label ) ? wp_kses_post( $label ) : $name;
				$label = $attribute_fee ? $label . ' (+' . $attribute_fee . ')' : $label;
				$html  = '<span class="swatch__tooltip">' . $label . '</span>';
			}
		}

		return $html;
	}
}
