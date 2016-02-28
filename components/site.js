window.yodasws = {}
$(document).ready(function(){
	// Make Main Menu Sticky
//	$('<div>').css({
//		height: $('body > nav').outerHeight(true)
//	}).insertBefore('body > nav')
//	$('body > nav').addClass('sticky').css({
//		position: 'fixed'
//	})
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
	$('.sticky').each(function() {
		var $t = $(this)
		if ($t.css('position') == 'fixed')
			height += $t.outerHeight(false)
	})
	return Math.max(height, 50)
}
