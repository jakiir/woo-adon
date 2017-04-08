jQuery( document ).ready(function() {
    jQuery('.export_romit').click(function(event) {
        event.preventDefault();
        type = jQuery(this).attr('rel');
        var data = {
            action: 'get_transfers_romit',
            file_type: type
        };
        
        jQuery.post( ajaxurl, data, function(response) {
            result = JSON.parse(response);
            console.log(result);
            
            if(result.answ.type=='url_to_get_code')
            {
                location.href = result.answ.body;
            }
            
            if(result.answ.type=='output_file')
            {
                if(result.answ.body.error==false)
                {
                    location.href = result.answ.body.file_name;
                }
                else
                {
                    swal(
                      'Error!',
                      'You select a wrong file format, or this file format is not supported!',
                      'error'
                    )
                }
            }
            
        });
        
    });
});