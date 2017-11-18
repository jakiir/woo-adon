/*
jQuery( document ).ready(function() {
    jQuery( ".popup_open_edebitdirect" ).click(function() {
        iframe = jQuery(this).attr('frame_url');
        swal({
        title: 'Payment via edebitdirect',
        html: '<div class="edebitdirect_iframe_wrap"><iframe src="'+decodeURIComponent(iframe)+'" style="height:500px;"></div>',
        showConfirmButton : false,
        showCloseButton:true,
        customClass : 'edebitdirect_checkout_popup'
        })
    });
});
*/
jQuery( function( $ ) {
    'use strict';
    $(document).on('click', '#place_order', function() {
        var edebitdirect_check = $( '#payment_method_edebitdirect' ).is( ':checked' );
        if(edebitdirect_check){
            $('#check_number,#routing_number,#account_number').css('border','1px solid #bbb');
            if($('#check_number').val() == ''){
                alert('Check number is required!');
                $('#check_number').focus().css('border','1px solid red');
                return false;
            }
            /*if($('#check_number').val().length < 4){
                alert('Check number is minimum 3 digit!');
                $('#check_number').focus().css('border','1px solid red');
                return false;
            }*/
            if($('#routing_number').val() == ''){
                alert('Routing number is required!');
                $('#routing_number').focus().css('border','1px solid red');
                return false;
            }
            /*if($('#routing_number').val().length < 3 || $('#routing_number').val().length > 9){
                alert('Routing number is minimum 3 digit and maximum 9 digit!');
                $('#routing_number').focus().css('border','1px solid red');
                return false;
            }*/
            if($('#account_number').val() == ''){
                alert('Account number is required!');
                $('#account_number').focus().css('border','1px solid red');
                return false;
            }
            /*if($('#account_number').val().length < 3 || $('#account_number').val().length > 9){
                alert('Account number is minimum 3 digit and maximum 9 digit!');
                $('#account_number').focus().css('border','1px solid red');
                return false;
            }*/
            //$( document.body ).on( 'checkout_error', 'fasdfas' );
        }
        
        
        return true;
    });

    $(document).on('keypress', 'input.number', function(event) {
        // Backspace, tab, enter, end, home, left, right
          // We don't support the del key in Opera because del == . == 46.
          var controlKeys = [8, 9, 13, 35, 36, 37, 39];
          // IE doesn't support indexOf
          var isControlKey = controlKeys.join(",").match(new RegExp(event.which));
          // Some browsers just don't raise events for control keys. Easy.
          // e.g. Safari backspace.
          if (!event.which || // Control keys in most browsers. e.g. Firefox tab is 0
              (48 <= event.which && event.which <= 57) || // Always 1 through 9
              (48 == event.which && $(this).attr("value")) || // No 0 first digit
              isControlKey) { // Opera assigns values for control keys.
            return;
          } else {
            event.preventDefault();
          }

      if (/\D/g.test(this.value))
      {
        // Filter non-digits from input value.
        this.value = this.value.replace(/\D/g, '');
      }
    });

});