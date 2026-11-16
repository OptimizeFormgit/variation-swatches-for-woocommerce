<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TAWCVS_Admin_Settings_Sync_Trait
 *
 * Handles syncing attribute type settings with plugin options.
 */
trait TAWCVS_Admin_Settings_Sync_Trait {

	/**
	 * @param $id
	 * @param $data
	 */
	public function update_plugin_setting_on_added( $id, $data ) {

		$this->sync_attribute_setting_to_plugin_settings( $data );
	}

	/**
	 * @param $id
	 * @param $data
	 * @param $old_slug
	 */
	public function update_plugin_setting_on_updated( $id, $data, $old_slug ) {

		$latest_option = $this->get_latest_plugin_option();

		//Remove the old slug from our plugin setting
		if ( $data['attribute_name'] !== $old_slug && isset( $latest_option['general'] ) ) {
			unset( $latest_option['general'][ 'color-swatches-attribute-' . $old_slug ] );
			unset( $latest_option['general'][ 'image-swatches-attribute-' . $old_slug ] );
		}

		$this->sync_attribute_setting_to_plugin_settings( $data, $latest_option );
	}

	/**
	 * Function to sync the attribute type setting with the plugin setting
	 *
	 * @param $data
	 * @param array|bool $latest_option
	 */
	private function sync_attribute_setting_to_plugin_settings( $data, $latest_option = false ) {
		if ( ! $latest_option ) {
			$latest_option = $this->get_latest_plugin_option();
		}

		$generalSettings = isset( $latest_option['general'] ) ? $latest_option['general'] : array();

		//Set new value
		switch ( $data['attribute_type'] ) {
			case 'image':
				$generalSettings[ 'image-swatches-attribute-' . $data['attribute_name'] ] = '1';
				$generalSettings[ 'color-swatches-attribute-' . $data['attribute_name'] ] = '0';
				$generalSettings[ 'radio-swatches-attribute-' . $data['attribute_name'] ] = '0';
				$generalSettings['enable-image-swatches']                                 = '1';
				break;
			case 'color':
				$generalSettings[ 'image-swatches-attribute-' . $data['attribute_name'] ] = '0';
				$generalSettings[ 'color-swatches-attribute-' . $data['attribute_name'] ] = '1';
				$generalSettings[ 'radio-swatches-attribute-' . $data['attribute_name'] ] = '0';
				$generalSettings['enable-color-swatches']                                 = '1';
				break;
			case 'radio':
				$generalSettings[ 'image-swatches-attribute-' . $data['attribute_name'] ] = '0';
				$generalSettings[ 'color-swatches-attribute-' . $data['attribute_name'] ] = '0';
				$generalSettings[ 'radio-swatches-attribute-' . $data['attribute_name'] ] = '1';
				$generalSettings['enable-radio-swatches']                                 = '1';
				break;
		}

		$latest_option['general'] = $generalSettings;

		update_option( TA_WC_Variation_Swatches::OPTION_NAME, $latest_option );

		$this->remove_wc_attributes_cache();
	}

	/**
	 * Reset the WC cache to get the latest value
	 */
	private function remove_wc_attributes_cache() {
		delete_transient( 'wc_attribute_taxonomies' );
	}

	/**
	 * Get the latest plugin option settings
	 */
	private function get_latest_plugin_option() {
		return TA_WCVS()->get_settings();
	}
}
