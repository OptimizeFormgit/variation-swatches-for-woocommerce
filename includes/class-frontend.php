<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_WC_Variation_Swatches_Frontend
 */
class TA_WC_Variation_Swatches_Frontend {
	use TAWCVS_Frontend_Variation_Data_Trait;
	use TAWCVS_Frontend_Swatch_Renderer_Trait;
	use TAWCVS_Frontend_Shop_Display_Trait;
	use TAWCVS_Frontend_Styles_Trait;
	use TAWCVS_Frontend_Settings_Trait;

	/**
	 * The single instance of the class
	 *
	 * @var TA_WC_Variation_Swatches_Frontend
	 */
	protected static $instance = null;

	private $generalSettings, $archiveSettings, $productDesign, $shopDesign, $toolTipDesign;

	/**
	 * Number of radio swatches rendered in this request, used to keep their ids unique
	 *
	 * @var int
	 */
	private $radio_count = 0;

	/**
	 * Main instance
	 *
	 * @return TA_WC_Variation_Swatches_Frontend
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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'woocommerce_before_variations_form', array( $this, 'get_available_variation' ) );

		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array(
			$this,
			'get_swatch_html'
		), 100, 2 );

		add_filter( 'tawcvs_swatch_html', array( $this, 'swatch_html' ), 5, 5 );

		$latest_option = TA_WCVS()->get_settings();

		$this->generalSettings = isset( $latest_option['general'] ) ? $latest_option['general'] : array();
		$this->archiveSettings = isset( $latest_option['archive'] ) ? $latest_option['archive'] : array();
		$this->productDesign = isset( $latest_option['design']['productDesign'] ) ? $latest_option['design']['productDesign'] : array();
		$this->shopDesign = isset( $latest_option['design']['shopDesign'] ) ? $latest_option['design']['shopDesign'] : array();
		$this->toolTipDesign = isset( $latest_option['design']['toolTipDesign'] ) ? $latest_option['design']['toolTipDesign'] : array();


		if ( isset( $this->archiveSettings['show-clear-link'] ) && ! $this->archiveSettings['show-clear-link'] ) {
			add_filter( 'woocommerce_reset_variations_link', array(
				$this,
				'tawcvs_show_clear_link_on_variations_on_shop_page'
			) );
		}

		if ( isset( $this->archiveSettings['show-swatch'] )
		     && $this->archiveSettings['show-swatch']
		     && ! TA_WC_Variation_Swatches::is_pro_addon_active() ) {
			add_filter( 'woocommerce_loop_add_to_cart_link', array(
				$this,
				'display_variations_on_shop_page_before_add_to_cart_btn'
			), 10, 2 );
		}
		add_action( 'wp_head', array( $this, 'apply_custom_design_styles' ) );

		add_filter( 'tawcvs_tax_attributes', array( $this, 'get_updated_attribute_type' ), 10 );
	}
}
