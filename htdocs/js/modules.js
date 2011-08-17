
/* --- INITIALIZATION --------------------------------------------------------- */

$(document).ready(function() {

	// create Foomo
	window.foomo = new Foomo;

	// create ToggleBox
	window.toggleBox = new Foomo.ToggleBox;

	// create TabBox
	window.tabBox = new Foomo.TabBox;

	// create open Overlay
	window.openOverlay = new Foomo.OpenOverlay;

	// create close Overlay
	window.closeOverlay = new Foomo.CloseOverlay;

	// create close Overlay
	window.backButton = new Foomo.BackButton;
});





/* --- Foomo --------------------------------------------------------- */

Foomo = function(){}





/* --- Toggle Box --------------------------------------------------------- */

Foomo.ToggleBox = function() {

	var a = this;
	$(".toggleBox div.toogleButton").live('click', function(event) {
		a.clickHandler(event);
	});
	// @todo: find better selector eg refactor template
	$(".toggleBox div.toogleButton a, .toggleBox div.toogleButton .toggleOpenContent form").live('click', function(event) {
		a.openContentClickHandler(event);
	});

}

Foomo.ToggleBox.prototype = {

	clickHandler: function(event) {
		var target = $(event.currentTarget);

		target.parent('.toggleBox').find('.toggleContent:first').toggle();

		if(target.parent('.toggleBox').find('.toggleContent:first').is(':hidden')){
			target.find('.toggleOpenIcon').text('+');
		} else {
			target.find('.toggleOpenIcon').text('-');
		}
	},

	openContentClickHandler: function(event) {
		event.stopPropagation();
	}
}





/* --- Toggle Box --------------------------------------------------------- */

Foomo.TabBox = function() {

	var a = this;
	$(".tabBox div.tabNavi li").live('click', function(event) {
		a.clickHandler(event);
	});

}

Foomo.TabBox.prototype = {

	clickHandler: function(event) {

		$(event.currentTarget).parents('.tabNavi:first').find('li').removeClass('selected');
		$(event.currentTarget).addClass('selected');


		$(event.currentTarget).parents('.tabBox:first').find('.tabContentBox:first > .tabContent').hide();
		$(event.currentTarget).parents('.tabBox:first').find('.tabContentBox:first > .tabContent-'+( $(event.currentTarget).index()+1 )).show();
	}
}





/* --- Overlay --------------------------------------------------------- */

Foomo.CloseOverlay = function() {

	var a = this;
	$("#overlay .closeOverlay").live('click', function(event) {
		a.clickHandler(event);
	});

}

Foomo.CloseOverlay.prototype = {

	clickHandler: function(event) {

		$(event.currentTarget).parents('#overlay:first').hide();
		$('#overlayFrame').attr('src', '');
	}
}

Foomo.OpenOverlay = function() {

	var a = this;
	$(".overlay").live('click', function(event) {
		a.clickHandler(event);
	});

}

Foomo.OpenOverlay.prototype = {

	clickHandler: function(event) {
		event.preventDefault();

		var url = $(event.target).attr('href');

		$('#overlayFrame').attr('src', url);
		$("#overlay").show();

	}
}





/* --- Back Button --------------------------------------------------------- */

Foomo.BackButton = function() {

	var a = this;
	$(".backButton").live('click', function(event) {
		a.clickHandler(event);
	});

}

Foomo.BackButton.prototype = {

	clickHandler: function(event) {
		//console.log("click");
		event.preventDefault();
		history.back();
	}
}