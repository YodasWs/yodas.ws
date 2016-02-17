document.registerElement('load-img', {
	prototype: HTMLImageElement
})
HTMLImageElement.prototype.getSrc = function() {
	return this.attributes['src']
}
$(document).ready(function(){
	$('load-img').each(function() {
	})
})
$(document).on('load', function() {
	// TODO: Load first image
	// TODO: Then load next image
})
