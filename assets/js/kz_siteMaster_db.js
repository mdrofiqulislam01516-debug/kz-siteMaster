jQuery(document).ready(function($){
    $( '#select-all' ).on( 'click', function(e){
        e.preventDefault();
        $( '#kz-siteMaster-db-reset-form input[type=checkbox]' ).prop( 'checked', true );
    });

    $( '#deselect-all' ).on( 'click', function(e) { 
        e.preventDefault();
        $( '#kz-siteMaster-db-reset-form input[type=checkbox]' ).prop( 'checked', false );
    });

    $( '#kz-siteMaster-db-reset-form' ).on( 'submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var responseBox = $( '#kz-db-reset-response' );

        var confirmVal = form.find('[name="kz_db_reset_confirm"]').val();

        if ( ! confirmVal || confirmVal.toLowerCase() !== 'reset' ) {
            responseBox.html( '<span style="color:red;">❌ You must type "reset" to confirm.</span>' );
            return;
        }

        if ( ! confirm( "Are you sure you want to reset selected tables?" ) ) return;

        responseBox.html( '<span style="color:blue;">Processing Selected Table reset...</span>' );

        var selectedTables = form.find( 'input[name="tables[]"]:checked' ).map( function() {
            return $(this).val();
        }).get();

        var reactivate = form.find( '[name="reactivate"]' ).is( ':checked' ) ? 1 : 0;

        $.ajax( {
            url    :  kzsiteMasterdb.ajax_url,
            method :  'POST',
            data   :   {
                action              : 'kz_siteMaster_db_table_reset',
                nonce               : kzsiteMasterdb.nonce,
                tables              : selectedTables,
                reactivate          : reactivate,
                kz_db_reset_confirm : confirmVal
            },
            success : function(res) {

                if (res.success) {
                    form[0].reset();
                    responseBox.html( '<span style="color:green;">' + res.data.message + '</span>' );

                    if (res.data.redirect_url) {
                        setTimeout(function() {
                            window.location.href = res.data.redirect_url;
                        }, 1000);
                    }
                } else {
                        responseBox.html( '<span style="color:red;">❌ ' + res.data.message + '</span>' );
                }
            },
            error: function() {
                    responseBox.html( '<span style="color:red;"> ❌ Something went wrong . Please try again. </span>' );
            }            
        });
    });
});