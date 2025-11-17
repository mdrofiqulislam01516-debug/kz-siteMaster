<!-- Database Reset -->
    
<?php

global $wpdb;
    $wp_core_tables = [
        'commentmeta',
        'comments',
        'links',
        'options',
        'postmeta',
        'posts',
        'termmeta',
        'terms',
        'term_relationships',
        'term_taxonomy',
        'usermeta',
        'users'
    ];
    $prefixe_tables = [];
        foreach ( $wp_core_tables as $table ) {
            $prefixe_tables[] = $wpdb->prefix . $table;
    }
?>
<div class="wrap">
  <h1>Database Reset</h1><br>
  <p><b>Select The Database Tables You Would Like To Reset</b></p>

        <button id="select-all">Select All</button>
        <button id="deselect-all">Deselect All</button>

        <form id="kz-siteMaster-db-reset-form">
          <input type="hidden" name="action" value="kz_siteMaster_db_table_reset">

            <ul>
            <?php foreach( $prefixe_tables as $table ): ?>
                <li>
                    <label>
                        <input type="checkbox" name="tables[]" value="<?php echo esc_attr( $table ); ?>">
                        <?php echo esc_html( $table ); ?>
                    </label>
                </li>
            <?php endforeach; ?>
            </ul>

            <br>
            <label>
                <input type="checkbox" name="reactivate" value="1" checked>
                Reactivate theme & plugins
            </label>

            <label>
            <p>Type <strong>"reset"</strong> to confirm Database Tables</p>
            <input type="text" name="kz_db_reset_confirm" placeholder="Type reset to confirm" style="width:250px;" required>
            </label>

              <br><br>
            <button type="submit" class="button button-primary">Reset Tables</button>
        </form>
        
        <div id="kz-db-reset-response"></div>
</div>

 <br><br><hr>
