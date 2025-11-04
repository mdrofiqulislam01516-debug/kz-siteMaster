<div class="wrap">
    
    <h1> Site Reset</h1>

    <!-- Site Reset -->

    <h2>Site Reset</h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'kz_reset_action', 'kz_reset_nonce' ); ?>
        <input type="hidden" name="action" value="kz_siteMaster_handle_reset">

        <p><label><input type="checkbox" name="reactivate_theme"> Reactivate current theme</label></p>
        <p><label><input type="checkbox" name="reactivate_plugins" checked> Reactivate KZ SiteMaster Plugin</label></p>
        <p><label><input type="checkbox" name="reactivate_this_plugin"> Reactivate all plugins</label></p>

        <p>Type <strong>reset</strong> to confirm site reset.</p>
        <input type="text" name="kz_reset_confirm" placeholder="Type reset to confirm" style="width:250px;">

        <p>
            <button class="button button-danger">Site Reset</button>
        </p>
    </form>
</div>