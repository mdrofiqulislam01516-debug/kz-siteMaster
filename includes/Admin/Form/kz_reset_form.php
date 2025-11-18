<!-- Site Reset -->
<?php include plugin_dir_path(__FILE__) . 'kz_siteMaster_header.php'; ?>
<div class="wrap">
    <h1>Site Reset</h1>
    <form id="kz-site-reset-form">
        <input type="hidden" name="action" value="kz_siteMaster_handle_reset">

        <p><label><input type="checkbox" class="kz-active-theme" name="reactivate_theme"> Reactivate current theme</label></p>
        
        <p><label><input type="checkbox" name="reactivate_this_plugin" checked> Reactivate KZ_siteMaster plugin</label></p>

        <p>Type <strong>"reset"</strong> to confirm site reset.</p>
        <input type="text" name="kz_reset_confirm" placeholder="Type reset to confirm" style="width:250px;" required>
        <p><button class="button button-primary">Reset Site</button></p>
    </form>
    <div id="kz-reset-response"></div>
</div>
<?php include plugin_dir_path(__FILE__) . 'kz_siteMaster_footer.php'; ?>
<hr>