<?php


class VSWC_Settings_Page {
	private $option_name = 'woosuite_variation_swatches_option';
	/**
	 * @var VSWC_Upgrader
	 */
	private $upgrader_obj;

	public function __construct() {
		$this->upgrader_obj = new VSWC_Upgrader();

		add_action( 'admin_menu', array( $this, 'handle_save_actions' ),5);
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_ajax_tawcvs_save_settings', array( $this, 'tawcvs_save_settings' ) );
	}

	public function admin_scripts() {
		if ( TA_WC_Variation_Swatches::is_in_plugin_settings_page() ) {

			do_action( 'woosuite_core_admin_page_scripts' );

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			wp_enqueue_script( 'tawcvs-admin', plugins_url( '/assets/js/admin.js', dirname( __FILE__ ) ), array( 'jquery' ), WCVS_PLUGIN_VERSION, true );
			wp_enqueue_style( 'tawcvs-admin', plugins_url( '/assets/css/admin.css', dirname( __FILE__ ) ), array( 'wp-color-picker' ), WCVS_PLUGIN_VERSION );
		}
	}

	public function admin_menu() {
		if ( TA_WC_Variation_Swatches::is_woo_core_active() ) {
			add_submenu_page(
				'woosuite-core',
				__( 'Variation Swatches', 'wcvs' ),
				__( 'Variation Swatches', 'wcvs' ),
				'manage_options',
				'variation-swatches-settings',
				array( $this, 'render' )
			);
		} else {
			add_menu_page(
				__( 'Variation Swatches', 'wcvs' ),
				__( 'Variation Swatches', 'wcvs' ),
				'manage_options',
				'variation-swatches-settings',
				array( $this, 'render' ),
				'dashicons-ellipsis',
				12 );

			add_submenu_page(
				'variation-swatches-settings',
				__( 'Settings', 'wcvs' ),
				__( 'Settings', 'wcvs' ),
				'manage_options',
				'variation-swatches-settings',
				array( $this, 'render' )
			);
			add_submenu_page(
				'variation-swatches-settings',
				__( 'Woosuite Addons', 'wcvs' ),
				__( 'Addons', 'wcvs' ),
				'manage_options',
				'variation-swatches-addons',
				array( $this, 'render_addons' )
			);
		}

	}

	public function render() {
		TA_WC_Variation_Swatches::get_template( 'admin/setting-panel.php' );
		TA_WC_Variation_Swatches::get_template( 'admin/pro-feature-popup.php' );
		if ( $this->upgrader_obj->is_welcome_popup_should_be_shown() ) {
			TA_WC_Variation_Swatches::get_template( 'admin/welcome-popup-version-2_0_0.php' );
		}
	}

	public function render_addons() {
		TA_WC_Variation_Swatches::get_template( 'admin/addons-pages.php' );
	}

	public function tawcvs_save_settings() {
		unset( $_POST['action'] );
		$this->save_post_data_to_db();
		wp_send_json_success( [ 'msg' => 'saved' ], 200 );
	}

	/**
	 * Save form in case the core plugin is activated
	 *
	 * @return void
	 */
	public function handle_save_actions() {
		if ( isset( $_POST['woosuite_saving_variation_settings'] ) ) {
			unset( $_POST['woosuite_saving_variation_settings'] );
			$this->save_post_data_to_db();
			$_POST['woosuite_saved_variation_settings'] = true;
		}
	}

	/**
	 * Helper function to save _POST data to db
	 */
	private function save_post_data_to_db() {
		update_option( $this->option_name, $this->sanitize_post_data( $_POST ) );
	}

	private function sanitize_post_data( $post_data ) {
		foreach ( $post_data as $section_id => $items ) {
			foreach ( $items as $field_name => $field_value ) {
				if ( is_array( $field_value ) ) {
					$post_data[ $section_id ] = $this->sanitize_post_data( $items );
				} else {
					$post_data[ $section_id ][ $field_name ] = sanitize_text_field( $field_value );
				}
			}
		}

		return $post_data;
	}
}


?>