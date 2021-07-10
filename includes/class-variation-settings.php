<?php 


class VSWC_Settings {
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }

    public function admin_scripts() {
        wp_enqueue_script( 'tawcvs-admin', plugins_url( '/assets/js/admin.js', dirname( __FILE__ ) ), array('jquery'), null, true );
		wp_enqueue_style( 'tawcvs-admin', plugins_url( '/assets/css/admin.css', dirname( __FILE__ ) ), array( 'wp-color-picker' ), null );
    }

    public function admin_menu() {
        add_menu_page( 'Variation settings', 'Variation settings', 'manage_options', 'variation-settings', array($this, 'render') );
    }

    public function render() {
        ?> 
        <div>This is variation settings page</div>
        <?php
    }
}


?>