<?php
namespace kodezen\siteMaster\Admin;

class kz_siteMaster_Menu {
    
    function __construct() {

        add_action( 'admin_menu', [ $this, 'kz_siteMaster_register_admin_menu' ] );
    }

    public function kz_siteMaster_register_admin_menu() {

        add_menu_page(

            __( 'kz siteMaster', 'kz_siteMaster' ), 
            __( 'KZ siteMaster', 'kz_siteMaster' ),
            'manage_options',
            'kz_siteMaster',
            [ $this, 'kz_siteMaster_render_plugin_page' ],
            'dashicons-update'

        );
    }

    public function kz_siteMaster_render_plugin_page() {
          
        include plugin_dir_path(__FILE__) . 'kz_reset_form.php'; 
        
    }

}




