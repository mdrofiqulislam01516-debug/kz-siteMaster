jQuery(document).ready(function($) {
    $( '#kz-wc-plugin-reset-form' ).on( 'submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var responseBox = $( '#kz-wc-reset-response' );
        var confirmVal = form.find( '[name="kz_wc_reset_confirm"]' ).val();

        if ( ! confirmVal || confirmVal.toLowerCase() !== 'reset' ) {
            responseBox.html( '<span style="color:red;">❌ You must type "reset" to confirm.</span>' );
            return;
        }

        if ( ! confirm( "Are you sure you want to reset WooCommerce Plugin? " ) ) return;

        responseBox.html( '<span style="color:blue;">Processing WooCommerce plugin reset .....</span>' );

        $.ajax( {
            url    : kzsiteMasterwc.ajax_url,
            method : 'POST',
            data   : {
                action              : 'kz_siteMaster_wc_reset',
                nonce               : kzsiteMasterwc.nonce,
                kz_wc_reset_confirm : confirmVal
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
                    responseBox.html( '<span style="color:red;">❌' + res.data.message + '</span>' );
                }
            },
            error: function() {
                responseBox.html( '<span style="color:red;">❌ Something went wrong.. Please try again.</span>' );
            }
        });
    });
});