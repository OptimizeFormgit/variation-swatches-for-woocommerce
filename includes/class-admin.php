<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_WC_Variation_Swatches_Admin
 */
class TA_WC_Variation_Swatches_Admin {
	use TAWCVS_Admin_Attribute_Fields_Trait;
	use TAWCVS_Admin_Ajax_Trait;
	use TAWCVS_Admin_Restore_Trait;
	use TAWCVS_Admin_Product_Tab_Trait;
	use TAWCVS_Admin_Settings_Sync_Trait;

	/**
	 * The single instance of the class
	 *
	 * @var TA_WC_Variation_Swatches_Admin
	 */
	protected static $instance = null;

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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), PHP_INT_MAX );

		add_action( 'woocommerce_product_data_tabs', array( $this, 'add_custom_swatch_variation_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_tab_variation_swatches_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_custom_product_data' ) );

		// Restore attributes
		add_action( 'admin_notices', array( $this, 'restore_attributes_notice' ) );
		add_action( 'admin_init', array( $this, 'restore_attribute_types' ) );

		// Display attribute fields
		add_action( 'tawcvs_product_attribute_field', array( $this, 'attribute_fields' ), 10, 4 );

		add_action( 'wp_ajax_update_product_attr_type', array( $this, 'update_product_attr_type' ) );
		add_action( 'wp_ajax_update_attribute_type_setting', array( $this, 'update_attribute_type_setting' ) );


		add_action( 'woocommerce_attribute_added', array( $this, 'update_plugin_setting_on_added' ), 10, 2 );
		add_action( 'woocommerce_attribute_updated', array( $this, 'update_plugin_setting_on_updated' ), 10, 3 );

		add_filter( 'woosuite_core_module_settings_url', array(
			$this,
			'render_the_setting_url_in_core_plugin'
		), 10, 2 );

		include_once( dirname( __FILE__ ) . '/class-menu-page.php' );
		new VSWC_Settings_Page();
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
	 * Load stylesheet and scripts in edit product attribute screen
	 */
	public function enqueue_scripts() {
		global $wp_styles;
		$screen   = get_current_screen();
		$dir_name = dirname( __FILE__ );

		wp_enqueue_style( 'tawcvs-admin-common-css', plugins_url( '/assets/css/common.css', $dir_name ), null, WCVS_PLUGIN_VERSION );
		wp_enqueue_script( 'tawcvs-admin-common-js', plugins_url( '/assets/js/common.js', $dir_name ), null, WCVS_PLUGIN_VERSION );

		wp_localize_script(
			'tawcvs-admin-common-js',
			'tawcvs',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'i18n'        => array(
					'mediaTitle'  => esc_html__( 'Choose an image', 'wcvs' ),
					'mediaButton' => esc_html__( 'Use image', 'wcvs' ),
				),
				'placeholder' => WC()->plugin_url() . '/assets/images/placeholder.png',
			)
		);

		// Don't enqueue unless we are in the edit product attribute or plugin setting page
		if ( strpos( $screen->id, 'edit-pa_' ) === false &&
		     strpos( $screen->id, 'product' ) === false &&
		     TA_WC_Variation_Swatches::is_in_plugin_settings_page() === false ) {
			return;
		}

		if ( TA_WC_Variation_Swatches::is_in_plugin_settings_page() ) {
			wp_dequeue_style( 'woocommerce_admin_styles' );
			wp_deregister_script( 'woocommerce_admin_styles' );
			unset( $wp_styles->registered['woocommerce_admin_styles'] );
		}

		wp_enqueue_media();

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style( 'tawcvs-admin', plugins_url( '/assets/css/admin.css', $dir_name ), array( 'wp-color-picker' ), WCVS_PLUGIN_VERSION );
		// admin.js reads the `tawcvs` object printed once for common.js; a second copy would replace it.
		wp_enqueue_script( 'tawcvs-admin', plugins_url( '/assets/js/admin.js', $dir_name ), array(
			'jquery',
			'wp-color-picker',
			'wp-util',
			'tawcvs-admin-common-js'
		), WCVS_PLUGIN_VERSION, true );

	}

}
