<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main plugin class
 */
final class TA_WC_Variation_Swatches {
	/**
	 * Centralized option name for plugin settings.
	 */
	const OPTION_NAME = 'woosuite_variation_swatches_option';

	/**
	 * The single instance of the class
	 *
	 * @var TA_WC_Variation_Swatches
	 */
	protected static $instance = null;

	/**
	 * Extra attribute types
	 *
	 * @var array
	 */
	public $types = array();

	/**
	 * Products whose large-product cache was already cleared in this request.
	 *
	 * @var array
	 */
	private $cleared_smart_variations = array();

	/**
	 * Main instance
	 *
	 * @return TA_WC_Variation_Swatches
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
		// The constructor runs on plugins_loaded, which is too early to translate: WordPress 6.7+ logs a
		// "_load_textdomain_just_in_time was called incorrectly" notice on every request. The labels are
		// translated on init by translate_types().
		$this->types = array(
			'color' => 'Color',
			'image' => 'Image',
			'label' => 'Label',
			'radio' => 'Radio button',
		);

		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		$current_dir = dirname( __FILE__ );
		$traits_dir  = $current_dir . '/traits';

		// Load frontend traits first (required before class definition)
		require_once $traits_dir . '/trait-frontend-variation-data.php';
		require_once $traits_dir . '/trait-frontend-swatch-renderer.php';
		require_once $traits_dir . '/trait-frontend-shop-display.php';
		require_once $traits_dir . '/trait-frontend-styles.php';
		require_once $traits_dir . '/trait-frontend-settings.php';

		require_once $current_dir . '/class-upgrader.php';
		require_once $current_dir . '/class-frontend.php';

		if ( is_admin() ) {
			// Load admin traits before class definition
			require_once $traits_dir . '/trait-admin-attribute-fields.php';
			require_once $traits_dir . '/trait-admin-ajax.php';
			require_once $traits_dir . '/trait-admin-restore.php';
			require_once $traits_dir . '/trait-admin-product-tab.php';
			require_once $traits_dir . '/trait-admin-settings-sync.php';

			require_once $current_dir . '/class-admin.php';
		}
	}

	/**
	 * Initialize hooks
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'translate_types' ) );

		add_filter( 'product_attributes_type_selector', array( $this, 'add_attribute_types' ) );

		if ( is_admin() ) {
			add_action( 'init', array( 'TA_WC_Variation_Swatches_Admin', 'instance' ) );
		}

		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			add_action( 'init', array( 'TA_WC_Variation_Swatches_Frontend', 'instance' ) );
		}

		// Clear cache when product is saved
		add_action( 'woocommerce_process_product_meta', array( $this, 'clear_product_cache' ) );
		add_action( 'woocommerce_save_product_variation', array( $this, 'clear_product_cache' ) );

		// Large products cache stock in a transient: refresh it when an order, refund or edit changes stock.
		foreach ( array( 'woocommerce_variation_set_stock', 'woocommerce_variation_set_stock_status', 'woocommerce_product_set_stock', 'woocommerce_product_set_stock_status' ) as $stock_hook ) {
			add_action( $stock_hook, array( $this, 'clear_stock_cache' ) );
		}
		// WooCommerce clears its own price caches here on every CRUD/REST save, bulk edit and scheduled sale.
		add_action( 'woocommerce_delete_product_transients', array( $this, 'clear_smart_variations_cache' ) );
	}

	/**
	 * Drop every price context of the large-product cache by deleting its generation key.
	 *
	 * @param int $product_id Product ID; only variable products (post type product) have this cache.
	 */
	public function clear_smart_variations_cache( $product_id ) {
		// Once per product until the cache is rebuilt, so a 1,000-variation import clears once.
		if ( isset( $this->cleared_smart_variations[ $product_id ] ) || 'product' !== get_post_type( $product_id ) ) {
			return;
		}
		$this->cleared_smart_variations[ $product_id ] = true;
		delete_transient( 'tawcvs_smart_variations_gen_' . $product_id . '_0' );
		delete_transient( 'tawcvs_smart_variations_gen_' . $product_id . '_1' );
	}

	/**
	 * A new cache generation was built: the next change to this product must clear it again.
	 *
	 * @param int $product_id Variable product ID.
	 */
	public function forget_cleared_smart_variations( $product_id ) {
		unset( $this->cleared_smart_variations[ $product_id ] );
	}

	/**
	 * @param WC_Product|int $product Product object (set_stock hooks) or ID (set_stock_status hooks).
	 */
	public function clear_stock_cache( $product ) {
		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product( $product );
		}
		if ( ! $product ) {
			return;
		}
		// Only variable products have this cache; the parent's stock counts for variations that inherit it.
		if ( $product->is_type( 'variation' ) ) {
			$product_id = $product->get_parent_id();
		} elseif ( $product->is_type( 'variable' ) ) {
			$product_id = $product->get_id();
		} else {
			return;
		}
		$this->clear_smart_variations_cache( $product_id );
	}

	/**
	 * OPTIMIZED: Enhanced cache clearing for smart variations
	 */
	public function clear_product_cache( $product_id ) {
		$product = wc_get_product( $product_id );
		// woocommerce_save_product_variation passes the variation ID; the caches belong to the parent.
		if ( $product && $product->is_type( 'variation' ) ) {
			$product_id = $product->get_parent_id();
			$product    = wc_get_product( $product_id );
		}

		$this->clear_smart_variations_cache( $product_id );
		delete_transient( 'tawcvs_light_variations_' . $product_id . '_0' );
		delete_transient( 'tawcvs_light_variations_' . $product_id . '_1' );
		if ( $product ) {
			// Same key as get_product_variation_term(): one entry per attribute taxonomy.
			foreach ( array_keys( $product->get_attributes() ) as $taxonomy ) {
				wp_cache_delete( 'tawcvs_terms_' . $product_id . '_' . $taxonomy );
			}
		}
	}

	/**
	 * Load plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wcvs', false, dirname( plugin_basename( TAWC_VS_PLUGIN_FILE ) ) . '/languages/' );
	}

	/**
	 * Translate the attribute type labels set in the constructor, once the text domain is loaded
	 */
	public function translate_types() {
		$labels = array(
			'color' => esc_html__( 'Color', 'wcvs' ),
			'image' => esc_html__( 'Image', 'wcvs' ),
			'label' => esc_html__( 'Label', 'wcvs' ),
			'radio' => esc_html__( 'Radio button', 'wcvs' ),
		);

		foreach ( $labels as $type => $label ) {
			if ( isset( $this->types[ $type ] ) ) {
				$this->types[ $type ] = $label;
			}
		}
	}

	/**
	 * Add extra attribute types
	 * Add color, image and label type
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	public function add_attribute_types( array $types ) {
		return array_merge( $types, $this->types );
	}

	/**
	 * Get the plugin settings. The option is autoloaded, so WordPress already keeps it in memory.
	 *
	 * @return array
	 */
	public function get_settings() {
		$option = get_option( self::OPTION_NAME, array() );
		return is_array( $option ) ? $option : array();
	}

	/**
	 * Get attribute's properties
	 *
	 * @param string $taxonomy
	 *
	 * @return object
	 */
	public function get_tax_attribute( $taxonomy ) {
		global $wpdb;

		if ( strpos( $taxonomy, 'pa_' ) === 0 ) {
			$attr = substr( $taxonomy, 3 );
		} else {
			$attr = $taxonomy;
		}

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = %s",
				$attr
			)
		);

		return apply_filters( 'tawcvs_tax_attributes', $result );
	}

	/**
	 * Instance of admin
	 *
	 * @return object
	 */
	public function admin() {
		return TA_WC_Variation_Swatches_Admin::instance();
	}

	/**
	 * Instance of frontend
	 *
	 * @return object
	 */
	public function frontend() {
		return TA_WC_Variation_Swatches_Frontend::instance();
	}

	/**
	 * Function to generate the formatted style for the dual color feature
	 *
	 * @param int $term_id
	 * @param string $main_color
	 *
	 * @return string
	 */
	public static function generate_color_style( $term_id, $main_color ) {

		$color_style = apply_filters( 'tawcvs_color_style', $main_color, $term_id, $main_color );

		return esc_attr( $color_style );
	}

	/**
	 * Include a specified template file
	 *
	 * @param $file_path
	 */
	public static function get_template( $file_path ) {
		$template = WCVS_PLUGIN_DIR . 'templates/' . $file_path;
		if ( file_exists( $template ) ) {
			include $template;
		}
	}

	public static function get_product_attributes_as_checkbox(
		$section_id,
		$tab_id,
		$field_name,
		$show_configure_link = false,
		$type_to_update = ''
	) {
		ob_start();
		$current_options = self::instance()->get_settings();
		if ( ! empty( $tab_id ) ) {
			$field_name_prefix = $section_id . '[' . $tab_id . ']';
		} else {
			$field_name_prefix = $section_id;
		}
		foreach ( wc_get_attribute_taxonomies() as $att ) {
			if ( ! empty( $tab_id ) ) {
				$field_value = isset( $current_options[ $section_id ][ $tab_id ][ $field_name . '-' . $att->attribute_name ] ) ? $current_options[ $section_id ][ $tab_id ][ $field_name . '-' . $att->attribute_name ] : '';
			} else {
				$field_value = isset( $current_options[ $section_id ][ $field_name . '-' . $att->attribute_name ] ) ? $current_options[ $section_id ][ $field_name . '-' . $att->attribute_name ] : '';
			}
			$field_id = $field_name . '-' . $att->attribute_name;
			$field_name_modified = $field_name_prefix . '[' . $field_name . '-' . $att->attribute_name . ']';

			$tax_slug = esc_attr( wc_attribute_taxonomy_name( $att->attribute_name ) );
			?>
            <label class="variation-checkbox-container" for="<?php echo $field_id; ?>">
				<?php echo $att->attribute_label; ?>
                <input type="hidden" name="<?php echo $field_name_modified; ?>" value="0">
                <input id="<?php echo $field_id; ?>"
                       type="checkbox"
                       name="<?php echo $field_name_modified; ?>"
                       value="1"
                       data-slug="<?php echo $att->attribute_name; ?>"
					<?php echo $type_to_update ? 'data-type="' . esc_attr( $type_to_update ) . '"' : '';; ?>
					<?php checked( '1', $field_value ); ?>/>
                <span class="checkmark"></span>
				<?php if ( $show_configure_link ): ?>
					<?php $configure_link = 'edit-tags.php?taxonomy=' . $tax_slug . '&amp;post_type=product'; ?>
                    <a class="configure-items-link <?php echo 1 == $field_value ? '' : 'hidden'; ?>"
                       href="<?php echo $configure_link; ?>">
						<?php _e( 'Configure items', 'wcvs' ); ?>
                    </a>
				<?php endif; ?>
            </label>
			<?php
		}

		return ob_get_clean();
	}

	public static function get_detailed_product_variations( $product, $attribute_tax_name ) {
		if ( ! $product instanceof WC_Product_Variable ) {
			return array();
		}
		$collected_variations = array();
		$variations = self::get_available_variations( $product );

		if ( ! empty( $variations ) ) {
			foreach ( $variations as $variation ) {
				if ( ! isset( $variation['attributes'][ 'attribute_' . $attribute_tax_name ] ) ) {
					continue;
				}
				$attribute_item_obj_slug = $variation['attributes'][ 'attribute_' . $attribute_tax_name ];

				// Keep the first variation as the representative for display (image/price),
				// but a term is only "unpurchasable" when EVERY combination containing it is
				// unpurchasable. Aggregate purchasability across all variations sharing the
				// term so a single out-of-stock combination no longer disables the whole
				// attribute option on page load.
				if ( ! isset( $collected_variations[ $attribute_item_obj_slug ] ) ) {
					$collected_variations[ $attribute_item_obj_slug ] = $variation;
					$collected_variations[ $attribute_item_obj_slug ]['woosuite_purchasable'] = false;
				}

				if ( ! empty( $variation['woosuite_purchasable'] ) ) {
					$collected_variations[ $attribute_item_obj_slug ]['woosuite_purchasable'] = true;
				}
			}
		}

		return $collected_variations;
	}

	/**
	 * Get an array of available variations for the current product.
	 *
	 * @param $product
	 * @param $skip_out_of_stock
	 * @param $get_full_attribute_data
	 *
	 * @return array[]|WC_Product_Variation[]
	 */
	public static function get_available_variations( $product, $skip_out_of_stock = false, $get_full_attribute_data = false ) {
		if ( ! $product instanceof WC_Product_Variable ) {
			return array();
		}
		$variation_ids = $product->get_children();
		if ( empty( $variation_ids ) ) {
			return array();
		}
		$available_variations = array();

		if ( is_callable( '_prime_post_caches' ) ) {
			_prime_post_caches( $variation_ids );
		}

		foreach ( $variation_ids as $variation_id ) {

			$variation = wc_get_product( $variation_id );

			// Hide out of stock variations if 'Hide out of stock items from the catalog' is checked.
			if ( ! $variation || ! $variation->exists() || ( $skip_out_of_stock && ! $variation->is_in_stock() ) ) {
				continue;
			}

			// Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
			if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $product->get_id(),
					$variation ) && ! $variation->variation_is_visible() ) {
				continue;
			}
			if ( $get_full_attribute_data ) {
				$available_variations[] = array_merge(
					$product->get_available_variation( $variation_id ),
					array(
						'woosuite_purchasable' => $variation->is_purchasable() && $variation->is_in_stock()
					)
				);
			} else {
				$available_variations[] = array(
					'attributes'           => $variation->get_variation_attributes(),
					'variation_id'         => $variation_id,
					'woosuite_purchasable' => $variation->is_purchasable() && $variation->is_in_stock()
				);
			}
		}

		return array_values( array_filter( $available_variations ) );
	}

	/**
	 * Detect if we have the Woosuite Core plugin activated
	 *
	 * @return bool
	 */
	public static function is_woo_core_active() {
		return class_exists( 'Woosuite_Core' );
	}

	/**
	 * Detect if we have the Woosuite Core plugin activated
	 *
	 * @return bool
	 */
	public static function is_pro_addon_active() {
		return class_exists( 'Woosuite_Variation_Swatches_Pro' );
	}

	public static function is_in_plugin_settings_page() {
		return is_admin() && isset( $_GET['page'] ) && $_GET['page'] === 'variation-swatches-settings';
	}
}

if ( ! function_exists( 'TA_WCVS' ) ) {
	/**
	 * Main instance of plugin
	 *
	 * @return TA_WC_Variation_Swatches
	 */
	function TA_WCVS() {
		return TA_WC_Variation_Swatches::instance();
	}
}