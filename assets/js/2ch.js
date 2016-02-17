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
      $(document).trigger( 'message.nichan', [message, status, $form] );
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

  /**
   * Check cookie and show if exists.
   */
  $(document).ready(function(){
    // If form exists, check cookie
    var $recaptcha = $('#nichan-recaptcha');
    if( $recaptcha.length ){
      var id = $recaptcha.attr('data-post-id');
      var cookie = Cookies.get('nichan_posted');
      if( cookie ){
        var post_ids = cookie.split('-');
        if( -1 < post_ids.indexOf(id) ){
          // Cookie exists!
          message($recaptcha.parents('form'), NichanVars.message, 'success');
          var newCookie = [];
          for( var i = 0, l = post_ids.length; i < l; i++ ){
            if( id != post_ids[i] ){
              newCookie.push(post_ids[i])
            }
          }
          if( newCookie.length ){
            // Refresh cookie.
            Cookies.set('nichan_posted', newCookie.join('-'), {
              path: '/',
              expires: 1
            });
          }else{
            // Remove cookie.
            Cookies.remove('nichan_posted', {
              path: '/'
            });
          }
        }
      }
    }

    /**
     * Thread toggler
     */
    $(document).ready(function(){
      $('.nichan-thread__button').click(function(e){
        e.preventDefault();
        $(this).parent('div').remove();
      });
    });
  });

})(jQuery);
