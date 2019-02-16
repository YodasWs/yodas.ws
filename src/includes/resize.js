$(window).on('resize', (e, d) => {
	if (d && d.scrollTo && d.scrollTo.element) {
		let element = d.scrollTo.element;
		if (d.scrollTo.element instanceof jQuery) {
			element = element[0];
		}
		// Scroll to keep target in sight
		element.scrollIntoView({
			behavior: d.scrollTo.behavior || 'smooth',
			block: d.scrollTo.block || 'start',
		});
	}
});
