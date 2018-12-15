$(window).on('resize', (e, d) => {
	if (d && d.eleScrollTo && d.eleScrollTo.length) {
		// Scroll to keep target in sight
		setTimeout(() => {
			d.eleScrollTo[0].scrollIntoView({
				behavior: 'smooth',
			});
		}, 1000);
	}
});
