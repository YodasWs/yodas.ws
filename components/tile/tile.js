Math.randInt = function(min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min
}
HTMLElement.prototype.changeImage = function() {
	var $a = $(this),
		$i = $a.children('img'),
		i = Math.randInt(0, $i.length-1)
	$a.css({
		background: 'url(' + $($i[i]).attr('src') + ') center center no-repeat',
		backgroundSize: 'cover'
	})
	setTimeout(this.changeImage.bind(this), Math.randInt(5, 20) * 1000)
}
$(document).ready(function(){
	$('article.tile').each(function(){
		this.changeImage()
	})
})
