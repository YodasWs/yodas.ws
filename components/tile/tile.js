Math.randInt = function(min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min
}
HTMLElement.prototype.changeImage = function() {
	var $a = $(this),
		$i = $a.children('img'),
		i = Number.parseInt($a.data('i'), 10) + 1 || 0,
		alt = $('<div class="alt">'),
		img
	$a.find('.alt').remove()
	if (i == $i.length) i = 0
	img = $($i[i])
	if (img.attr('alt')) {
		alt.text(img.attr('alt')).appendTo($a)
	}
	$a.css({
		background: 'url(' + img.attr('src') + ') center center no-repeat',
		backgroundSize: 'cover'
	}).data('i', i)
	setTimeout(this.changeImage.bind(this), Math.randInt(5, 20) * 1000)
}
$(document).ready(function(){
	$('article.tile').each(function(){
		this.changeImage()
	})
})
