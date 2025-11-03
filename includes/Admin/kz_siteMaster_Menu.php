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

        ?>

        <!-- Site Reset -->

         <div class="wrap">
    <h1>Site Reset</h1>
    <p>Type <strong>reset</strong> to confirm full site reset.</p>
    
    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">

        <?php wp_nonce_field('kz_reset_action', 'kz_reset_nonce'); ?>
        
        <input type="hidden" name="action" value="kz_siteMaster_handle_reset">
        <input type="text" name="kz_siteMaster_reset_confirm" placeholder="Type reset here" required>
        <br><br>
        <label><input type="checkbox" name="reactivate_theme"> Reactivate current theme</label><br>
        <label><input type="checkbox" name="reactivate_plugins"> Reactivate all plugins</label><br>
        <label><input type="checkbox" name="reactivate_self"> Reactivate this plugin</label>
        <br><br>
        <button type="submit" class="button button-danger">Site Reset</button>
    </form>
</div>


       <!-- Full Site Reset  -->

        <div class="wrap">

            <h1>Full Site Reset</h1>

            <form method="post">

                <?php wp_nonce_field( 'kz_reset_action', 'kz_reset_nonce' ); ?>

                <p>Clicking the button below will delete all database tables and reinstall WordPress.</p>
                <input type="text" name="kz_siteMaster_fullsite_reset" placeholder="Type RESET to confirm" required>
                <button type="submit" class="kz_siteMaster_button button button-danger" name="kz_siteMaster_fullsite_reset">Full Site Reset</button>

            </form>

        </div>

        <?php 
    }

}