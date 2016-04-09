window.yodasws = {}
$(document).ready(function(){
	// Make Main Menu Sticky
	$('body > nav').css({
		top: $('body > header').outerHeight(false) - 1
	})
	$('body > nav + *').css({
		'margin-top': $('body > header').outerHeight(false) + $('body > nav').outerHeight(false) - 1
	})
	// Keep Main Menus Open on Click
	$(document).on('click', 'body > nav > li', function(e) {
		$(e.target).toggleClass('active').siblings().removeClass('active')
	})
	// Close Main Menus on Click
	$(document).on('click', function(e) {
		if (!$(e.target).closest('body > nav').length) {
			$('body > nav > li').removeClass('active')
		}
	})
})
yodasws.stickyHeight = function() {
	var height = 0
	$('body > header, body > nav').each(function() {
		height += $(this).outerHeight(false)
	})
	$('.sticky').each(function() {
		var $t = $(this)
		if ($t.css('position') == 'fixed')
			height += $t.outerHeight(false)
	})
	return height
}
