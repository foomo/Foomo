/* --- INITIALIZATION --------------------------------------------------------- */

$(document).ready(function() {
	
	// create Foomo
	window.foomo = new Foomo;
	
	// create ToggleBox
	window.toggleBox = new Foomo.ToggleBox;
	
	// create TabBox
	window.tabBox = new Foomo.TabBox;
});





/* --- Foomo --------------------------------------------------------- */

Foomo = function(){}





/* --- Toggle Box --------------------------------------------------------- */

Foomo.ToggleBox = function() {
	
	var a = this;
	
	$(".toggleBox div.toogleButton").live('click', function(event) {
		a.clickHandler(event);
	});
}

Foomo.ToggleBox.prototype = {
	clickHandler: function(event) {
		//console.log("clicker ");
		
		$(event.currentTarget).parent('.toggleBox').find('.toggleContent:first').toggle();
		
		if($(event.currentTarget).parent('.toggleBox').find('.toggleContent:first').is(':hidden')){
			$(event.currentTarget).find('.toggleOpenIcon').text('+');
		} else {
			$(event.currentTarget).find('.toggleOpenIcon').text('-');
		}
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
		console.log("clicker "+$(event.currentTarget).index() );
		
		
		//$(event.currentTarget).parents('.tabNavi:first').find('li').removeClass('tabBox .tabNavi ul li.selected');
		//$(event.currentTarget).addClass('.tabBox .tabNavi ul li.selected');
		
        $(event.currentTarget).parents('.tabBox:first').find('.tabContent').hide();
		$(event.currentTarget).parents('.tabBox:first').find('.tabContent-'+( $(event.currentTarget).index()+1 )).show();
		
		
		//'.tabContent-'+( $(event.currentTarget).index()+1 )
		/*
		$(event.currentTarget).parent('.toggleBox').find('.toggleContent:first').toggle();
		
		if($(event.currentTarget).parent('.toggleBox').find('.toggleContent:first').is(':hidden')){
			$(event.currentTarget).find('.toggleOpenIcon').text('+');
		} else {
			$(event.currentTarget).find('.toggleOpenIcon').text('-');
		}
		*/
	}
}