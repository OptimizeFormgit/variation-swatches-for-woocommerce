<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TAWCVS_Admin_Ajax_Trait
 *
 * Handles AJAX callbacks for attribute type updates and settings.
 */
trait TAWCVS_Admin_Ajax_Trait {

	/**
	 * The Ajax callback to update the plugin Attribute type setting.
     * It also can be called from the Ajax callback to save attribute type
	 */
	public function update_attribute_type_setting() {
		if ( ! wp_verify_nonce( $_POST['__nonce'], 'tawcvs_admin_settings') || ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => 'Failed to update', 'success' => false ), 200 );
		}

        //Get the latest plugin option
		$latest_option = $this->get_latest_plugin_option();

        //Check if we need to return an json response or not
		$send_response = (bool) ( isset( $_POST['sendResponse'] ) ? sanitize_text_field( $_POST['sendResponse'] ) : 0 );

		// Only the flat "general" section is posted here (admin.js and Pro); nested sections would be flattened.
		$main_setting_arr = array();
		if ( isset( $_POST['general'] ) && is_array( $_POST['general'] ) ) {
			$main_setting_arr['general'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['general'] ) );
		}

		if ( empty( $main_setting_arr ) ) {
			if ( $send_response ) {
				wp_send_json_error( array( 'message' => 'No valid settings data', 'success' => false ), 200 );
			}
			return;
		}

        //Update the plugin setting and return the response if needed
		if ( update_option( TA_WC_Variation_Swatches::OPTION_NAME, array_replace_recursive( $latest_option, $main_setting_arr ) ) ) {
			if ( $send_response ) {
				wp_send_json_success( array( 'message' => 'Updated plugin settings', 'success' => true ), 200 );
			}
		} elseif ( $send_response ) {
			wp_send_json_error( array( 'message' => 'Failed to update', 'success' => false ), 200 );
		}
	}

	/**
	 * Ajax callback to update the Product Attribute type
	 */
	public function update_product_attr_type() {
		if ( ! wp_verify_nonce( $_POST['__nonce'], 'tawcvs_admin_settings') || ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => 'Failed to update', 'success' => false ), 200 );
		}

		global $wpdb;

		$attribute_name = isset( $_POST['attribute'] ) ? sanitize_text_field( $_POST['attribute'] ) : '';
		$type_to_update = isset( $_POST['typeToUpdate'] ) ? sanitize_text_field( $_POST['typeToUpdate'] ) : '';

		if ( empty( $attribute_name ) || empty( $type_to_update ) ) {
			wp_send_json_error( array( 'message' => 'Missing data', 'success' => false ), 200 );
		}

		//Setting the Product attribute to specified type
		$result = $wpdb->update(
			$wpdb->prefix . 'woocommerce_attribute_taxonomies',
			array( 'attribute_type' => $type_to_update ),
			array( 'attribute_name' => $attribute_name ),
			array( '%s' ),
			array( '%s' )
		);

		$this->update_attribute_type_setting();

		$this->remove_wc_attributes_cache();

		//Save the setting
		if ( false !== $result ) {
			wp_send_json_success( array( 'message' => 'Done with update', 'success' => true ), 200 );
		}

		wp_send_json_error( array( 'message' => 'Unexpected error', 'success' => false ), 200 );

	}
}
