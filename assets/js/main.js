console.log("Main!");
// Menu Lateral
$(document).ready(function() {
  $("#menu").mmenu({
    navbar: false,
    navbars: {
      height:4,
      content: [
        '<div class="user-card"><div class="user-photo"><img src="http://lorempixel.com/100/100/people/" /></div><div class="user-info"><h2>Fulano Silva</h2><p>fulano.silva@infnet.edu.br</p></div></div>'
      ]
    }
  });

  // Altera o tipo de visualização
  $(".ui-menu button").click(function(){
    var rel = $(this).attr('rel');
    switch (rel) {
      case "linha":
        $(".dashboard .area").removeClass("coluna");
        break;
      case "coluna":
        $(".dashboard .area").addClass("coluna");
        break;
      case "grid":
        $(".dashboard .area").removeClass("lista");
        break;
      case "lista":
        $(".dashboard .area").addClass("lista");
        break;
      default:
        console.log("Nada");
    }
  });

});
