<!-- WooCommerce Plugin Reset -->
<?php include plugin_dir_path(__FILE__) . 'kz_siteMaster_header.php'; ?>

<div class="wrap">
    <h1>WooCommerce Plugin Reset</h1>
    <form id="kz-wc-plugin-reset-form">

        <input type="hidden" name="action" value="kz_siteMaster_wc_reset">
        
        <p>Type<strong>"reset"</strong> to confirm WooCommerce reset </p>
        <input type="text" name="kz_wc_reset_confirm" placeholder="Type reset to confirm" required>
        <p><button class="button button-primary">WooCommerce Reset</button></p>

    </form>
    <div id="kz-wc-reset-response"></div>
</div>
<?php include plugin_dir_path(__FILE__) . 'kz_siteMaster_footer.php'; ?>
<br><br><hr>
