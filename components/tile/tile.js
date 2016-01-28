Math.randInt = function(min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min
}
window.Tile = function(){}
Tile.prototype.changeImage = function() {
	var $a = $(this),
		$i = $a.children('img'),
		n = $a.is('.expanded') ? 2 : 1,
		i = Number.parseInt(this.bg_i, 10) - n + 2 || 0,
		bg = [],
		img,k
	clearTimeout(this.bgto)
	$a.find('.alt').remove()
	for (var j=0; j<n; j++) {
		if (i == $i.length) i = 0
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
		this.tile = new Tile()
		this.tile.changeImage.call(this)
	}).on('click', function(){
		$(this).toggleClass('expanded').siblings('.tile.expanded').removeClass('expanded')
		this.tile.changeImage.call(this)
	})
})
