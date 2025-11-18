jQuery( document ).ready(function ( $ ) {

    function kz_siteMaster_Reset_Form( kz_siteMaster ) {
        
        $( kz_siteMaster.form ).off( "submit" ).on( "submit", function (e) {
            e.preventDefault();

            var form = $( this );
            var responseBox = $( kz_siteMaster.responseBox );
            var confirmVal = form.find( kz_siteMaster.confirmField ).val();

            if ( ! confirmVal || confirmVal.toLowerCase() !== "reset" ) {
                responseBox.html( '<span style="color:red;">❌ You must type "reset" to confirm.</span>' );
                return;
            }

            if ( ! confirm( kz_siteMaster.confirmText ) ) return;

            responseBox.html( '<span style="color:blue;">Processing... Please wait...</span>' );

            var data = {
                action                      : kz_siteMaster.action,
                nonce                       : kz_siteMaster.nonce,
                [kz_siteMaster.confirmName] : confirmVal
            };

            if ( typeof kz_siteMaster.extraFields === "function" ) {
                data = Object.assign( data, kz_siteMaster.extraFields( form ) );
            }

            $.ajax( {
                url     : kz_siteMaster.ajaxUrl,
                method  : "POST",
                data    : data,
                success : function ( res ) {
                    if ( res.success ) {
                        form[0].reset();
                        responseBox.html( '<span style="color:green;">' + res.data.message + '</span>' );
                        if ( res.data.redirect_url ) {
                            setTimeout(() => {
                                window.location.href = res.data.redirect_url;
                            }, kz_siteMaster.redirectdelay );
                        }
                    } else {
                        responseBox.html( '<span style="color:red;">❌ ' + res.data.message + '</span>' );
                    }
                },
                error: function () {
                    responseBox.html( '<span style="color:red;">❌ Something went wrong. Please try again.</span>' );
                }
            });
        });
    }

     /**
      * Site Reset
      */

    kz_siteMaster_Reset_Form( {
        form            : "#kz-site-reset-form",
        responseBox     : "#kz-reset-response",
        confirmField    : "[name='kz_reset_confirm']",
        confirmName     : "kz_reset_confirm",
        confirmText     : "Are you sure you want to reset the entire site?",
        action          : "kz_siteMaster_handle_reset",
        ajaxUrl         : kzsiteMaster.ajax_url,
        nonce           : kzsiteMaster.nonce,
        redirectdelay   :1000,
        extraFields     : function ( form ) {
            return {
                reactivate_theme        : form.find( "[name='reactivate_theme']" ).is( ":checked" ) ? 1 : 0,
                reactivate_this_plugin  : form.find( "[name='reactivate_this_plugin']" ).is( ":checked" ) ? 1 : 0
            };
        }
    });

    /**
     * Database Reset
     */

    kz_siteMaster_Reset_Form({
        form            : "#kz-siteMaster-db-reset-form",
        responseBox     : "#kz-db-reset-response",
        confirmField    : "[ name='kz_db_reset_confirm' ]",
        confirmName     : "kz_db_reset_confirm",
        confirmText     : "Are you sure you want to reset selected database tables?",
        action          : "kz_siteMaster_db_table_reset",
        ajaxUrl         : kzsiteMasterdb.ajax_url,
        nonce           : kzsiteMasterdb.nonce,
        redirectdelay   :1000,
        extraFields     : function ( form ) {
            return {
                tables: form.find( 'input[ name="tables[]" ]:checked' )
                    .map(function () { return $( this ).val(); }).get(),
                reactivate: form.find( "[ name='reactivate' ]" ).is( ":checked" ) ? 1 : 0
            };
        }
    });

    /**
     * WooCommerce Reset
     */
    kz_siteMaster_Reset_Form( {
        form            : "#kz-wc-plugin-reset-form",
        responseBox     : "#kz-wc-reset-response",
        confirmField    : "[name='kz_wc_reset_confirm']",
        confirmName     : "kz_wc_reset_confirm",
        confirmText     : "Are you sure you want to reset WooCommerce Plugin?",
        action          : "kz_siteMaster_wc_reset",
        ajaxUrl         : kzsiteMasterwc.ajax_url,
        nonce           : kzsiteMasterwc.nonce,
        redirectdelay   :1500
    });

    /**
     * Select All / Deselect All
     */
    $( "#select-all" ).off( "click" ).on( "click", function (e) {
        e.preventDefault();
        $( "#kz-siteMaster-db-reset-form input[ type='checkbox' ]" ).prop( "checked", true );
    });

    $( "#deselect-all" ).off( "click" ).on( "click", function (e) {
        e.preventDefault();
        $( "#kz-siteMaster-db-reset-form input[ type='checkbox' ]" ).prop( "checked", false );
    });

});
