jQuery(document).ready(function($) {
    $( '#kz-site-reset-form' ).on( 'submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var responseBox = $( '#kz-reset-response' );

        var confirmVal = form.find( '[name="kz_reset_confirm"]' ).val();
        if ( ! confirmVal || confirmVal.toLowerCase() !== 'reset' ) {
            responseBox.html( '<span style="color:red;">❌ You must type "reset" to confirm.</span>' );
            return;
        }

        if ( ! confirm( "Are you sure you want to reset the site?" ) ) return;

        responseBox.html( '<span style="color:blue;">Processing site reset...</span>' );

        $.ajax( {
            url    : kzsiteMaster.ajax_url,
            method : 'POST',
            data   : {
                action                 : 'kz_siteMaster_handle_reset',
                nonce                  : kzsiteMaster.nonce,
                reactivate_theme       : form.find( '[name="reactivate_theme"]' ).is( ':checked' ) ? 1 : 0,   
                reactivate_this_plugin : form.find( '[name="reactivate_this_plugin"]' ).is( ':checked' ) ? 1 : 0,
                kz_reset_confirm       : confirmVal
            },
            success: function(res) {
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
                responseBox.html( '<span style="color:red;">❌ Something went wrong. Please try again.</span>' );
            }
        });
    });
});
