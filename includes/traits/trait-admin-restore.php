<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TAWCVS_Admin_Restore_Trait
 *
 * Handles attribute type backup restoration notices and actions.
 */
trait TAWCVS_Admin_Restore_Trait {

	/**
	 * Display a notice of restoring attribute types
	 */
	public function restore_attributes_notice() {
		if ( get_transient( 'tawcvs_attribute_taxonomies' ) && ! get_option( 'tawcvs_restore_attributes_time' ) ) {
			?>
            <div class="notice-warning notice is-dismissible">
                <p>
					<?php
					esc_html_e( 'Found a backup of product attributes types. This backup was generated at', 'wcvs' );
					echo ' ' . date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), get_option( 'tawcvs_backup_attributes_time' ) ) . '.';
					?>
                </p>
                <p>
                    <a href="<?php echo esc_url( add_query_arg( array(
						'tawcvs_action' => 'restore_attributes_types',
						'tawcvs_nonce'  => wp_create_nonce( 'restore_attributes_types' )
					) ) ); ?>">
                        <strong><?php esc_html_e( 'Restore product attributes types', 'wcvs' ); ?></strong>
                    </a>
                    |
                    <a href="<?php echo esc_url( add_query_arg( array(
						'tawcvs_action' => 'dismiss_restore_notice',
						'tawcvs_nonce'  => wp_create_nonce( 'dismiss_restore_notice' )
					) ) ); ?>">
                        <strong><?php esc_html_e( 'Dismiss this notice', 'wcvs' ); ?></strong>
                    </a>
                </p>
            </div>
			<?php
		} elseif ( isset( $_GET['tawcvs_message'] ) && 'restored' == $_GET['tawcvs_message'] ) {
			?>
            <div class="notice-warning settings-error notice is-dismissible">
                <p><?php esc_html_e( 'All attributes types have been restored.', 'wcvs' ) ?></p>
            </div>
			<?php
		}
	}

	/**
	 * Restore attribute types
	 */
	public function restore_attribute_types() {
		if ( ! isset( $_GET['tawcvs_action'] ) || ! isset( $_GET['tawcvs_nonce'] ) ) {
			return;
		}

		$action = sanitize_text_field( $_GET['tawcvs_action'] );
		$allowed_actions = array( 'restore_attributes_types', 'dismiss_restore_notice' );
		if ( ! in_array( $action, $allowed_actions, true ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['tawcvs_nonce'], $action ) ) {
			return;
		}

		if ( 'restore_attributes_types' == $action ) {
			global $wpdb;

			$attribute_taxnomies = get_transient( 'tawcvs_attribute_taxonomies' );

			if ( empty( $attribute_taxnomies ) || ! is_array( $attribute_taxnomies ) ) {
				return;
			}

			foreach ( $attribute_taxnomies as $id => $attribute ) {
				$wpdb->update(
					$wpdb->prefix . 'woocommerce_attribute_taxonomies',
					array( 'attribute_type' => $attribute->attribute_type ),
					array( 'attribute_id' => $id ),
					array( '%s' ),
					array( '%d' )
				);
			}

			update_option( 'tawcvs_restore_attributes_time', time() );
			delete_transient( 'tawcvs_attribute_taxonomies' );
			delete_transient( 'wc_attribute_taxonomies' );

			$url = remove_query_arg( array( 'tawcvs_action', 'tawcvs_nonce' ) );
			$url = add_query_arg( array( 'tawcvs_message' => 'restored' ), $url );
		} elseif ( 'dismiss_restore_notice' == $action ) {
			update_option( 'tawcvs_restore_attributes_time', 'ignored' );
			$url = remove_query_arg( array( 'tawcvs_action', 'tawcvs_nonce' ) );
		}

		if ( isset( $url ) ) {
			wp_redirect( $url );
			exit;
		}
	}
}
