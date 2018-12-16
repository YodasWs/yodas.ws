$(window).on('resize', (e, d) => {
	if (d && d.eleScrollTo && d.eleScrollTo.length) {
		// Scroll to keep target in sight
		d.eleScrollTo[0].scrollIntoView({
			behavior: 'smooth',
			block: 'start',
		});
	}
});
