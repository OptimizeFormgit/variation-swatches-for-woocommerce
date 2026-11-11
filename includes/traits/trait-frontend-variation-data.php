<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TAWCVS_Frontend_Variation_Data_Trait
 *
 * Handles variation data retrieval and caching for the frontend.
 */
trait TAWCVS_Frontend_Variation_Data_Trait {

	/**
	 * Getting all available variations to a hidden elements
	 */
	public function get_available_variation() {
		global $product;

		if ( ! $product instanceof WC_Product_Variable ) {
			return '';
		}

		$variation_count = count( $product->get_children() );
		$large_product_threshold = apply_filters( 'tawcvs_large_product_threshold', 100, $product );
		$get_variations = $variation_count > apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

		$is_disabled_checking_availability = $this->is_disabled_checking_availability();
		$is_keep_disabled_variation_selectable = $this->is_keep_disabled_variation_selectable();

		if ( ! $is_disabled_checking_availability && ! $get_variations ) {
			return '';
		}

		if ( ! $is_disabled_checking_availability ) {
			$skip_out_of_stock = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' );
		} else {
			$skip_out_of_stock = $is_keep_disabled_variation_selectable;
		}

		// OPTIMIZED: For large products, use smart minimal data that WooCommerce can work with
		if ( $variation_count > $large_product_threshold ) {
			// One row per price context (tax location, role, user); clearing the generation key makes every row stale.
			$gen_key = 'tawcvs_smart_variations_gen_' . $product->get_id() . '_' . (int)$skip_out_of_stock;
			$gen     = get_transient( $gen_key );
			if ( false === $gen ) {
				$gen = uniqid( '', true );
				set_transient( $gen_key, $gen, 30 * MINUTE_IN_SECONDS );
				TA_WCVS()->forget_cleared_smart_variations( $product->get_id() );
			}
			// Generation lives in the value, not the key, so a new generation overwrites the same row.
			$cache_key = 'tawcvs_smart_variations_' . $product->get_id() . '_' . (int)$skip_out_of_stock . '_' . $this->get_variation_price_hash( $product );
			$cached    = get_transient( $cache_key );

			if ( is_array( $cached ) && isset( $cached['gen'], $cached['variations'] ) && $cached['gen'] === $gen ) {
				$variations = $cached['variations'];
			} else {
				$variations = $this->get_smart_variations_for_wc( $product, $skip_out_of_stock );
				set_transient( $cache_key, array( 'gen' => $gen, 'variations' => $variations ), 30 * MINUTE_IN_SECONDS );
			}
		} else {
			$variations = TA_WC_Variation_Swatches::get_available_variations( $product, $skip_out_of_stock, true );
		}

		$variations_json = wp_json_encode( $variations );
		$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

		if ( ! empty( $variations_attr ) ) {
			?>
            <div class="tawcvs-placeholder-element hidden tawcvs-available-product-variation"
                 data-product_variations="<?php echo $variations_attr; ?>">
            </div>
			<?php
		}
	}

	/**
	 * Cached variation data holds prices, which can differ per visitor (tax location,
	 * role, currency). Same inputs as WooCommerce's own variation price cache.
	 */
	private function get_variation_price_hash( $product ) {
		global $wp_filter;

		$price_hash = array( false );
		if ( wc_tax_enabled() ) {
			$price_hash = array(
				get_option( 'woocommerce_tax_display_shop', 'excl' ),
				WC_Tax::get_rates(),
				empty( WC()->customer ) ? false : WC()->customer->is_vat_exempt(),
			);
		}

		foreach ( array( 'woocommerce_variation_prices_price', 'woocommerce_variation_prices_regular_price', 'woocommerce_variation_prices_sale_price' ) as $filter_name ) {
			if ( ! empty( $wp_filter[ $filter_name ] ) ) {
				$price_hash[ $filter_name ] = array();
				foreach ( $wp_filter[ $filter_name ] as $callbacks ) {
					$price_hash[ $filter_name ][] = array_values( wp_list_pluck( $callbacks, 'function' ) );
				}
			}
		}

		return md5( wp_json_encode( apply_filters( 'woocommerce_get_variation_prices_hash', $price_hash, $product, true ) ) );
	}

	/**
	 * OPTIMIZED: Smart variations that provide exactly what WooCommerce needs
	 * This creates minimal but complete variation data that prevents disabled options
	 */
	private function get_smart_variations_for_wc( $product, $skip_out_of_stock = false ) {
		if ( ! $product instanceof WC_Product_Variable ) {
			return array();
		}

		$variation_ids = $product->get_children();
		if ( empty( $variation_ids ) ) {
			return array();
		}

		$smart_variations = array();

		// Prime caches for better performance
		if ( is_callable( '_prime_post_caches' ) ) {
			_prime_post_caches( $variation_ids );
		}

		// Get all variation objects at once for efficiency
		$variations_objects = array();
		foreach ( $variation_ids as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation && $variation->exists() ) {
				$variations_objects[ $variation_id ] = $variation;
			}
		}

		foreach ( $variations_objects as $variation_id => $variation ) {
			// Skip out of stock if needed
			if ( $skip_out_of_stock && ! $variation->is_in_stock() ) {
				continue;
			}

			// Skip invisible variations
			if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $product->get_id(), $variation )
			     && ! $variation->variation_is_visible() ) {
				continue;
			}

			// Create minimal but complete variation data that WooCommerce expects
			$variation_data = $this->create_minimal_variation_data( $variation, $product );

			if ( ! empty( $variation_data ) ) {
				$smart_variations[] = $variation_data;
			}
		}

		return array_values( array_filter( $smart_variations ) );
	}

	/**
	 * FIXED: Create complete variation data with all required WooCommerce fields
	 * This ensures WooCommerce JavaScript doesn't throw errors while optimizing performance
	 */
	private function create_minimal_variation_data( $variation, $parent_product ) {
		// Get basic variation info efficiently
		$variation_id = $variation->get_id();
		$attributes = $variation->get_variation_attributes();
		$is_purchasable = $variation->is_purchasable();
		$is_in_stock = $variation->is_in_stock();

		// Get price info (cached by WooCommerce)
		$price_html = $variation->get_price_html();
		$display_price = wc_get_price_to_display( $variation );
		$display_regular_price = wc_get_price_to_display( $variation, array( 'price' => $variation->get_regular_price() ) );

		// Get image info efficiently (only if different from parent)
		$image_data = $this->get_efficient_image_data( $variation, $parent_product );

		// Get stock info
		$stock_info = $this->get_efficient_stock_info( $variation );

		// Get dimensions efficiently (required by WooCommerce JS)
		$dimensions = $variation->get_dimensions( false );
		$dimensions_html = wc_format_dimensions( $dimensions );

		// Get weight efficiently (required by WooCommerce JS)
		$weight = $variation->get_weight();
		$weight_html = $weight ? wc_format_weight( $weight ) : '';

		// Get availability HTML efficiently
		$availability_html = wc_get_stock_html( $variation );

		// Create the complete variation data structure that WooCommerce expects.
		// Apply the standard woocommerce_available_variation filter so that other plugins
		// (e.g. woo-variation-gallery, product addons) can augment variation data on the
		// large-product "smart" path, matching WC core's signature in WC_Product_Variable::get_available_variation().
		return apply_filters(
			'woocommerce_available_variation',
			array(
				'attributes'                => $attributes,
				'availability_html'         => $availability_html,
				'backorders_allowed'        => $variation->backorders_allowed(),
				'dimensions'                => $dimensions,
				'dimensions_html'           => $dimensions_html,
				'display_price'             => $display_price,
				'display_regular_price'     => $display_regular_price,
				'image'                     => $image_data,
				'image_id'                  => $variation->get_image_id(),
				'is_downloadable'           => $variation->is_downloadable(),
				'is_in_stock'               => $is_in_stock,
				'is_purchasable'            => $is_purchasable,
				'is_sold_individually'      => $variation->is_sold_individually(),
				'is_virtual'                => $variation->is_virtual(),
				'max_qty'                   => $stock_info['max_qty'],
				'min_qty'                   => $variation->get_min_purchase_quantity(),
				'price_html'                => $price_html,
				'sku'                       => $variation->get_sku(),
				'variation_description'     => wc_format_content( $variation->get_description() ),
				'variation_id'              => $variation_id,
				'variation_is_active'       => $variation->variation_is_active(),
				'variation_is_visible'      => $variation->variation_is_visible(),
				'weight'                    => $weight,
				'weight_html'               => $weight_html,
			),
			$parent_product,
			$variation
		);
	}

	/**
	 * OPTIMIZED: Get image data efficiently
	 * Only loads image data if variation has a different image than parent
	 */
	private function get_efficient_image_data( $variation, $parent_product ) {
		$variation_image_id = $variation->get_image_id();
		$parent_image_id = $parent_product->get_image_id();

		// If variation uses same image as parent, return minimal data
		if ( ! $variation_image_id || $variation_image_id === $parent_image_id ) {
			return array(
				'title' => '',
				'caption' => '',
				'url' => '',
				'alt' => '',
				'src' => '',
				'srcset' => '',
				'sizes' => '',
				'full_src' => '',
				'full_src_w' => 0,
				'full_src_h' => 0,
				'gallery_thumbnail_src' => '',
				'gallery_thumbnail_src_w' => 0,
				'gallery_thumbnail_src_h' => 0,
				'thumb_src' => '',
				'thumb_src_w' => 0,
				'thumb_src_h' => 0,
			);
		}

		// Only get image data if variation has unique image
		$attachment = get_post( $variation_image_id );
		$single = wp_get_attachment_image_src( $variation_image_id, 'woocommerce_single' );
		$full = wp_get_attachment_image_src( $variation_image_id, 'full' );
		$thumbnail = wp_get_attachment_image_src( $variation_image_id, 'woocommerce_thumbnail' );

		return array(
			'title' => $attachment ? $attachment->post_title : '',
			'caption' => $attachment ? $attachment->post_excerpt : '',
			'url' => $attachment ? wp_get_attachment_url( $variation_image_id ) : '',
			'alt' => get_post_meta( $variation_image_id, '_wp_attachment_image_alt', true ),
			'src' => $single[0] ?? '',
			'srcset' => wp_get_attachment_image_srcset( $variation_image_id, 'woocommerce_single' ),
			'sizes' => wp_get_attachment_image_sizes( $variation_image_id, 'woocommerce_single' ),
			'full_src' => $full[0] ?? '',
			'full_src_w' => $full[1] ?? 0,
			'full_src_h' => $full[2] ?? 0,
			'gallery_thumbnail_src' => $thumbnail[0] ?? '',
			'gallery_thumbnail_src_w' => $thumbnail[1] ?? 0,
			'gallery_thumbnail_src_h' => $thumbnail[2] ?? 0,
			'thumb_src' => $thumbnail[0] ?? '',
			'thumb_src_w' => $thumbnail[1] ?? 0,
			'thumb_src_h' => $thumbnail[2] ?? 0,
		);
	}

	/**
	 * OPTIMIZED: Get stock info efficiently
	 */
	private function get_efficient_stock_info( $variation ) {
		if ( $variation->managing_stock() ) {
			$stock_quantity = $variation->get_stock_quantity();
			$max_qty = $stock_quantity > 0 ? $stock_quantity : '';
		} else {
			$max_qty = '';
		}

		return array(
			'max_qty' => $max_qty,
		);
	}
}
