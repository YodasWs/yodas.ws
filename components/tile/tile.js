window.Tile = function(){}
Tile.prototype.loadImage = function() {
	var self = this,
		$a = $(this),
		$i = $($a.children('load-img')[0]),
		img = $i.clone().wrap('<div>').parent().html()
	if (!img) return
	img = $(img.replace('load-img', 'img'))
	img.appendTo($a)
	img.on('load', function() {
		$i.remove()
		self.tile.loadImage()
	})
}
Tile.prototype.changeImage = function() {
	var $a = $(this),
		$i = $a.children('img'),
		n = $a.is('.expanded') ? Math.min(2, $i.length) : 1,
		i = Number.parseInt(this.bg_i, 10) + 1 || 0,
		fadeTime = 0,
		bg = [],
		img,k
	clearTimeout(this.bgto)
	if (!$i.length) return
	if ($a.find('.alt').length) {
		fadeTime = 300
		$a.find('.alt').fadeOut(300, function() {
			$(this).remove()
		})
	}
	if (i >= $i.length) i -= $i.length
	for (var j=0; j<n; j++) {
		k = i + j
		while (k >= $i.length) k -= $i.length
		img = $($i[k])
		if (img.attr('alt')) {
			$('<div class="alt">').text(img.attr('alt')).appendTo($a).delay(fadeTime).fadeIn(300)
		}
		bg.push('url(' + img.attr('src') + ')')
	}
	$a.css({
		backgroundImage: bg.join(',')
	})
	this.bg_i = i
	this.bgto = setTimeout(this.tile.changeImage, Math.randInt(5, 20) * 1000)
}
$(document).ready(function(){
	$('article.tile').each(function(){
		// Instantiate Tile Objects
		this.tile = new Tile()
		this.tile.loadImage = this.tile.loadImage.bind(this)
		this.tile.loadImage()
		this.tile.changeImage = this.tile.changeImage.bind(this)
		this.tile.changeImage()
	}).on('click', function(e){
		const $t = $(this)
		if ($(e.target).is('a[href]')) {
			// Clicked a link, don't confuse user with expanding tile
			return true
		}
		// Expand/Collapse Tiles
		if (!$t.is('.expanded')) {
			// Adjust Background Image
			this.bg_i = this.bg_i - 1
			if (this.bg_i < 0) this.bg_i = $t.children('img').length - 1
		}
		$t.toggleClass('expanded').siblings('.tile.expanded').removeClass('expanded')
		this.tile.changeImage()
		$(window).trigger('resize', {
			eleScrollTo: $t
		})
	})
	// Close Tiles on Click
	$(document).on('click', function(e) {
		if (!$(e.target).closest('.tile').length) {
			var $t = $('.tile.expanded')
			$('.tile').removeClass('expanded')
			$(window).trigger('resize', {
				eleScrollTo: $t
			})
		}
	})
})
