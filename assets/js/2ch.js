/**
 * Description
 */

/*global NichanVars: false*/

(function ($) {
  'use strict';

  /**
   * Message function
   *
   * @param {jQuery} $form
   * @param {String} message
   * @param {String} status
   */
  function message($form, message, status){
    if( ! status ){
      status = 'success';
    }
    if ( NichanVars.callback ) {
      $form.trigger('message.nichan', [message, status]);
    } else {
      window.alert( message );
    }
  }

  /**
   * Observe form
   */
  $(document).on( 'submit', '.nichan-thread', function(e){
    var $form = $(this);
    e.preventDefault();
    $form.addClass( 'loading' );
    $(this).ajaxSubmit( {
      type: 'POST',
      beforeSend: function(xhr){
        xhr.setRequestHeader( 'X-WP-Nonce', NichanVars.nonce );
      },
      success: function(response){
        message( $form, response.message, 'success' );
        setTimeout(function(){
          window.location.href = response.permalink;
        }, 3000);
      },
      error: function(xhr, err, status){
        if( xhr.responseJSON ){
          message( $form, xhr.responseJSON.message, 'error' );
        }else{
          message( $form, status, 'error' );
        }
      },
      complete: function(){
        $form.removeClass( 'loading' );
      }
    } );
  } );

})(jQuery);
