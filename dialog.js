function updateTips( t ) {
  var tips = $( ".validateTips" );
  tips
    .text( t )
    .addClass( "ui-state-highlight" );
  setTimeout(function() {
    tips.removeClass( "ui-state-highlight", 1500 );
  }, 500 );
}

function checkLength( o, n, min, max ) {
  if ( o.val().length > max || o.val().length < min ) {
    o.addClass( "ui-state-error" );
    updateTips( "Długość " + n + " musi być pomiędzy " +
      min + " a " + max + "." );
    return false;
  } else {
    return true;
  }
}

function checkRegexp( o, regexp, n ) {
  if ( !( regexp.test( o.val() ) ) ) {
    o.addClass( "ui-state-error" );
    updateTips( n );
    return false;
  } else {
    return true;
  }
}

function blad( o, war, tekst ) {
  if ( !war ) {
    o.addClass( "ui-state-error" );
    updateTips( tekst );
    return false;
  } else {
    return true;
  }
}

var dialog_pass = {
  html: function(id) { 
    return '<div id="'+id+'" title="Zmień hasło">'
			+'<p class="validateTips"></p>'
			+'<form><fieldset>'
				+'<label for="password">Stare hasło:</label><br/>'
				+'<input type="password" name="old_pass" value="" class="old_pass text ui-widget-content ui-corner-all" /><br/>'
				+'<label for="password">Nowe hasło:</label><br/>'
				+'<input type="password" name="new_pass" value="" class="new_pass text ui-widget-content ui-corner-all" /><br/>'
				+'<label for="password">Powtórz nowe hasło:</label><br/>'
				+'<input type="password" name="new_pass_2" value="" class="new_pass_2 text ui-widget-content ui-corner-all" />'
			+'</fieldset></form>'
		+'</div>';
  },
  obj: function(id, myuser_nr, myuser_md5) { 
    return {
      autoOpen: false,
      height: 400,
      width: 350,
      modal: true,
      buttons: {
        "Zmień hasło": function() {
          var bValid = true;
          var $old_pass = $( '#'+id+" .old_pass" ),
            $new_pass = $( '#'+id+" .new_pass" ),
            $new_pass_2 = $( '#'+id+" .new_pass_2" );
          $( '#'+id+" input" ).removeClass( "ui-state-error" );
          bValid = bValid && blad($old_pass, (hex_md5($old_pass.val()) == myuser_md5),"Błędne hasło.");
          bValid = bValid && checkLength( $new_pass, "hasła", 3, 26 );
          bValid = bValid && blad($new_pass_2, ($new_pass.val() == $new_pass_2.val()),"Hasła muszą być takie same.");
          if ( bValid ) {
            var obj = {"myusername":myuser_nr,"mypassword":$old_pass.val(),"mynewpass":$new_pass.val()};
            var ths = $( this );
            $.ajax({
              url: 'checklogin.php',
              type: 'POST',
              data: obj,
              timeout: 1000
            }).success(function(obj){
              if (obj == "OK"){
                alert('Hasło zostało zmienione.');
                ths.dialog( "close" );
                window.open("logout.php?reload","_self");
              } else
                alert(obj);
            }).fail( function() {
              alert('Błąd zmiany hasła');
            });
          }
        },
        "Anuluj": function() {
          $( this ).dialog( "close" );
        }
      },
      close: function() {
        $( '#'+id+" input" ).val( "" ).removeClass( "ui-state-error" );
      }
    }
  }
}

var dialog_l4 = {
  html: function(id) { 
    return '<div id="'+id+'" title="Urlop / L4">'
			+'<p class="validateTips"></p>'
			+'<form><fieldset>'
				+'<label for="od">Od:</label><br/>'
        +'<input type="text" class="data_od datetimepicker od " name="od" /><br/>'
				+'<label for="do">Nowe hasło:</label><br/>'
				+'<input type="text" class="data_do datetimepicker do " name="do" /><br/>'
				+'<label for="urlop_zad">Czynność:</label><br/>'
				+'<select name="urlop_zad"><option value="null"></option><option value="504">Urlop wypoczynkowy</option><option value="507">L4</option></select>'
			+'</fieldset></form>'
		+'</div>';
  },
  obj: {}
}