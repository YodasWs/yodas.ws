/*
$(document).ready(function(){
	$('load-img').each(function() {
	})
})
$(document).on('load', function() {
	// TODO: Load first image
	// TODO: Then load next image
})
/**/
window.ImgTile = function(){}
$(document).ready(function(){
	$('figure[itemtype$="Photograph"]').each(function(){
		// Instantiate Tile Objects
		this.tile = new ImgTile()
	}).on('click', function(e){
		var $t = $(this)
		// Expand/Collapse Tiles
		if (!$t.is('.expanded')) {
		}
		$t.toggleClass('expanded').siblings('.expanded').removeClass('expanded')
		$(window).trigger('resize', {
			eleScrollTo: $t
		})
	})
	// Close Tiles on Click
	$(document).on('click', function(e) {
		if (!$(e.target).closest('figure[itemtype$="Photograph"]').length) {
			var $t = $('figure[itemtype$="Photograph"].expanded')
			$('figure[itemtype$="Photograph"]').removeClass('expanded')
			$(window).trigger('resize', {
				eleScrollTo: $t
			})
		}
	})
})
