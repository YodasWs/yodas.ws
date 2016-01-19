$(document).ready(function(){
	$('article.tile[data-bg]').each(function(){
		var $a = $(this).closest('article')
		$a.css({
			background: 'url(' + $a.attr('data-bg') + ') center center no-repeat',
			backgroundSize: 'cover'
		})
	})
})
