/**
 * An admin screen helper.
 *
 * @package 2ch
 */

/* global NichanAdmin: false */

jQuery(document).ready(function ($) {

  'use strict';

  var $form = $('#form-2ch-setting');

  function message( msg, error ){
    var className = error ? 'error' : 'updated';
    var $msg = $('<div class="' + className + '"><p></p></div>');
    $msg.find('p').text(msg);
    $form.before($msg);
    setTimeout(function(){
      $msg.remove();
    }, error ? 5000 : 3000)
  }

  $form.submit(function(e){
    e.preventDefault();
    $form.ajaxSubmit({
      dataType: 'json',
      beforeSubmit: function(){
        $form.addClass('loading');
      },
      success: function(response){
        message(response.message);
      },
      error: function(xhr){
        $.each(xhr.responseJSON.messages, function(i, msg){
          message(msg, true);
        });
      },
      complete: function(){
        $form.removeClass('loading');
      }
    });
  });

  // Auto complete
  $( '#setting2ch-user' ).autocomplete({
      source: NichanAdmin.endpoint + '?action=' + NichanAdmin.actionSearch,
      focus: function( event, ui ) {
        $( '#setting2ch-user' ).val( ui.item.label );
        return false;
      },
      select: function( event, ui ) {
        $( '#setting2ch-user' ).val( ui.item.label );
        $( '#nichan-post-as' ).val( ui.item.value );
        $( '#setting2ch-user').prev('.avatar').replaceWith(ui.item.image);

        return false;
      }
    })
    .autocomplete( 'instance' )._renderItem = function( ul, item ) {
    return $( '<li>' )
      .append( '<a>' + item.label +  '</a>' )
      .appendTo( ul );
  };



});
