Math.randInt = function(min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min
}
window.Tile = function(){}
Tile.prototype.changeImage = function() {
	var $a = $(this),
		$i = $a.children('img,load-img'),
		n = $a.is('.expanded') ? 2 : 1,
		i = Number.parseInt(this.bg_i, 10) + 1 || 0,
		bg = [],
		img,k
	clearTimeout(this.bgto)
	if (!$i.length) return
	$a.find('.alt').remove()
	if (i >= $i.length) i -= $i.length
	for (var j=0; j<n; j++) {
		k = i + j
		while (k >= $i.length) k -= $i.length
		img = $($i[k])
		if (img.attr('alt')) {
			$('<div class="alt">').text(img.attr('alt')).appendTo($a)
		}
		bg.push('url(' + img.attr('src') + ')')
	}
	$a.css({
		backgroundImage: bg.join(',')
	})
	this.bg_i = i
	this.bgto = setTimeout(this.tile.changeImage.bind(this), Math.randInt(5, 20) * 1000)
}
$(document).ready(function(){
	$('article.tile').each(function(){
		// Instantiate Tile Objects
		this.tile = new Tile()
		this.tile.changeImage.call(this)
	}).on('click', function(e){
		var $t = $(this)
		// Expand/Collapse Tiles
		if (!$t.is('.expanded')) {
			// Adjust Background Image
			this.bg_i = this.bg_i - 1
			if (this.bg_i < 0) this.bg_i = $t.children('img,load-img').length - 1
		}
		// Scroll
		setTimeout(function() {
			$('html,body').animate({
				scrollTop: $t.offset().top - yodasws.stickyHeight()
			}, 500, 'swing')
		}, 500)
		$t.toggleClass('expanded').siblings('.tile.expanded').removeClass('expanded')
		this.tile.changeImage.call(this)
		$(window).trigger('resize')
	})
	// Close Tiles on Click
	$(document).on('click', function(e) {
		if (!$(e.target).closest('.tile').length) {
			$('.tile').removeClass('expanded')
			$(window).trigger('resize')
		}
	})
})
