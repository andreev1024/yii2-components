(function ( $ ) {

  var s = note.s,
    methods = {
      switchViews: function ( e ) {
        var sendNoteFormContainer = $( '#' + s.id.sendNoteFormContainer ),
          listContainer = $( '#' + s.id.listContainer ),
          hiddenClass = s.class.hidden;

        if ( sendNoteFormContainer.hasClass( s.class.hidden ) ) {
          methods.toggle( sendNoteFormContainer, listContainer );
        } else {
          methods.toggle( listContainer, sendNoteFormContainer );
        }
      },
      toggle: function ( showElement, hideElement, hiddenClass ) {
        hiddenClass = typeof hiddenClass !== 'undefined' ? hiddenClass : 'hidden';
        showElement.removeClass( hiddenClass );
        hideElement.addClass( hiddenClass );
      }
    };

  $.pjax.defaults.timeout = typeof note.pjaxTimeout !== 'undefined' ? note.pjaxTimeout : 5000;

  $( document ).on( 'click', '#' + s.id.noteBtn, function ( e ) {
    methods.switchViews( e );
  } );

  $( document ).on( 'click', '#' + s.id.sendNoteFormCloseBtn, function ( e ) {
    methods.switchViews( e );
  } );

  $( document ).on( 'click', '.' + s.class.deleteAction, function ( e ) {
    e.preventDefault();
    e.stopImmediatePropagation();

    if ( confirm( $( this ).data( 'customConfirm' ) ) ) {
      $.general.sendPost(
        {
          id: $( this ).data( 'noteId' )
        },
        $( this ).attr( 'href' ),
        function ( data ) {
          $.pjax.reload( { container: '#' + s.id.pjax } );
        },
        function ( data ) {
          'server error!';
        }
      );
    }
  } );

  $( document ).on( 'click', '.' + s.class.update + ',.' + s.class.cancel, function ( e ) {
    e.preventDefault();

    var parent = $( this ).closest( '.' + s.class.noteListItem ),
      editArea = $( '.' + s.class.noteListContentEditArea, parent ),
      viewArea = $( '.' + s.class.noteListContentViewArea, parent );

    if ( $( this ).hasClass( s.class.update ) ) {
      methods.toggle( editArea, viewArea );
    } else {
      methods.toggle( viewArea, editArea );
    }
  } );

  $( document ).on( 'submit', '.' + s.class.itemForm, function ( e ) {
    e.preventDefault();

    $.general.sendPost(
      $( this ).serialize(),
      $( this ).attr( 'action' ),
      function ( data ) {
        $.pjax.reload( { container: '#' + s.id.pjax } );
      },
      function ( data ) {
        'server error!';
      }
    );
  } );

})( window.jQuery );