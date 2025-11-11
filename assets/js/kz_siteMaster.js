jQuery(document).ready(function ($) {
    $('#kz-site-reset-form').on('submit', function (e) {
        e.preventDefault();

        if ( ! confirm( "Are you sure you want to reset the site?" ) ) return;

        const formData = $(this).serialize();   

        $('#kz-reset-response').html('<p style="color:blue;">Processing... Please wait...</p>');

        $.post(kzsiteMaster.ajax_url, formData, function (response) {
            if (response.success) {
                $('#kz-reset-response').html('<p style="color:green;"> ' + response.data.message + '</p>');
                // setTimeout(function(){
                //     if(response.data.redirect_url){
                //         window.location.href = response.data.redirect_url;
                //     }
                // }, 1000);
            } else {
                $('#kz-reset-response').html('<p style="color:red;">❌ ' + response.data.message + '</p>');
            }
         }).fail(function () {
            $('#kz-reset-response').html('<p style="color:red;">❌ AJAX request failed.</p>');
        });
    });
});
