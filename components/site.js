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
$(window).on('resize', function(e, d) {
	if (d && d.eleScrollTo && d.eleScrollTo.length) {
		// Scroll to keep target in sight
		setTimeout(function() {
			var winHeight = $(window).height(),
				scrollPad = d.eleScrollTo.is('.expanded') ? 5 : winHeight / 10,
				scrollBtm = d.eleScrollTo.offset().top + d.eleScrollTo.outerHeight(),
				scrollTop = d.eleScrollTo.offset().top - yodasws.stickyHeight() - scrollPad,
				scrollPos = $('body').prop('scrollTop'),
				doScroll = false
			if (scrollPos > scrollTop) doScroll = true
			if (scrollPos + winHeight + scrollPad < scrollBtm) doScroll = true
			if (doScroll)
			$('html,body').animate({
				scrollTop: scrollTop
			}, 500, 'swing')
		}, 1000)
	}
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
