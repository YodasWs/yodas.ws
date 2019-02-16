angular.module('compGtfs')
	.component('compGtfs', {
		template: '',
		bindings: {
			locale: '<',
		},
		controller: 'ctrlGtfs',
	})
	.controller('ctrlGtfs', ['$document', '$scope', function($document, $scope) {
		this.$onInit = () => {
		};
	}])
	.directive('compGtfs', [() => ({
		link(scope, element, attrs, controller, transcludeFn) {
			// Require Web Workers
			if (!window.Worker || !scope.$ctrl.workers || !scope.$ctrl.workers.gtfs) return;
			const gtfs = scope.$ctrl.workers.gtfs;

			const locale = scope.$ctrl.locale;
			if (!locale.gtfs) return;

			if (document.querySelectorAll('script[src*="maps.google.com/maps/api/js"]').length === 0) {
				const script = document.createElement('script');
				script.defer = true;
				script.src = 'http://maps.google.com/maps/api/js?key=AIzaSyA-Skp5Z1NNJSUZsMJbev-5IxTUyDJmVDk&v=3&region=US';
				script.addEventListener('load', () => {
					console.log('gtfs, Google Maps JavaScript loaded!');

					if (document.getElementById('google-maps') instanceof Element) {
						// Load Google Maps
						gtfs.map = new google.maps.Map(document.getElementById('google-maps'), {
							mapTypeId: google.maps.MapTypeId.ROADMAP,
							keyboardShortcuts: true,
							disableDefaultUI: true,
							scaleControl: true,
							scrollWheel: true,
							zoomControl: true,
							maxZoom: 18,
							minZoom: 9,
						});

						gtfs.map.zoom = gtfs.map.getZoom();
						gtfs.map.addListener('zoom_changed', (e) => {
							// Make Lines Thicker for Easier Reading
							const weightAdjust = (gtfs.map.zoom >= 14 ? gtfs.map.zoom - 13 : 6 - Math.floor((gtfs.map.zoom - 1) / 2));
							for (const i in gtfs.poly) {
								if (!gtfs.poly[i].Polyline) continue;
								gtfs.poly[i].Polyline.setOptions({
									strokeWeight: gtfs.poly[i].weight + weightAdjust,
								});
							}
						});
					}

					$('#google-maps, .gtfs').show();
				});
				document.head.appendChild(script);
			}

			// Call loadGTFS on each location in travels.json
			// eg: gtfs.loadGTFS('js/hakone');
			if (locale.gtfs) {
				console.log('loading gtfs for', locale.name);
				if (gtfs.loadGTFS) // TODO: Web Workers
					gtfs.loadGTFS(`${locale.cc}/${locale.name.toLowerCase().replace(/\W+/g, '_')}`);
			}

			$(document).on('loaded', (e) => {
				if (locale.name === e.locale.name) {
					if (gtfs.loadedFiles) // TODO: Web Workers
						gtfs.loadedFiles.push(e.file);
				}
			}).ready(() => {
				// Highlight Routes
				element.on('click', 'section[data-route-id]', (e) => {
					if (!gtfs.routes) return; // TODO: Web Workers
					const isOpen = $(e.target).closest('section[data-route-id]').is('.active');
					let switching = $(e.target).closest('li[data-stop-id]').length > 0;
					// If switched Stops, don't reset Map
					if (isOpen && switching) {
						switching = switching && $(e.target).closest('li[data-stop-id]').is('.active');
					}
					// Highlight Route on Map
					if (!isOpen) {
						$('section[data-route-id].active').trigger('unfocus');
						const $s = $(e.target).closest('section[data-route-id]').addClass('active');
						const route_id = $s.data('route-id');
						const shape = gtfs.routes[route_id].shape;
						const pts = [];
						if (!shape || !gtfs.poly[shape]) return;
						gtfs.poly[shape].Polyline.setOptions({
							strokeWeight: 4,
							opacity: 1,
							zIndex: 1,
						});
						gtfs.routes[route_id].stops.forEach((s) => {
							pts.push(gtfs.stops[s]);
						});
						$('html,body').animate({scrollTop: 0}, 300, () => {
							if (pts.length) gtfs.setBounds(pts);
							else if (gtfs.map && gtfs.map.fitBounds) gtfs.map.fitBounds(gtfs.extremes);
						});
					} else if (!switching) {
						// Reset Map
						$('section[data-route-id].active').trigger('unfocus');
					}
				}).on('unfocus', (e) => {
					if (!gtfs.routes) return; // TODO: Web Workers
					const $s = $(e.target).closest('section[data-route-id]').removeClass('active');
					const route_id = $s.data('route-id');
					const shape = gtfs.routes[route_id].shape;
					if (!shape || !gtfs.poly[shape]) return
					if (gtfs.map && gtfs.map.fitBounds) gtfs.map.fitBounds(gtfs.extremes)
					gtfs.poly[shape].Polyline.setOptions({
						strokeWeight: gtfs.poly[shape].weight,
						opacity: gtfs.poly[shape].opacity || .6,
						zIndex: 0,
					});
				});

				// Show Station/Stop on Map
				element.on('click', 'li[data-stop-id]', (e) => {
					if (!gtfs.stops) return; // TODO: Web Workers
					const $t = $(e.target).closest('li[data-stop-id]');
					const id = $t.data('stop-id');
					const route_id = $t.parents('section[data-route-id]').data('route-id');
					const isOpen = gtfs.stops[id].Marker.getVisible();
					if ($(e.target).closest('section[data-route-id]').is('.active') || !$t.is('.active')) {
						gtfs.hideStops();
						$('li[data-stop-id].active').removeClass('active');
						$('section[data-route-id].highlighted').removeClass('highlighted');
						if (!isOpen) {
							$('li[data-stop-id="' + id + '"]').addClass('active').parents('section[data-route-id]').addClass('highlighted');
							gtfs.stops[id].Marker.setVisible(true);
							if (gtfs.map && gtfs.map.panTo) gtfs.map.panTo(gtfs.stops[id].Marker.getPosition());
						}
					}
				});
			}).on('click', (e) => {
				if (!$(e.target).closest('section[data-route-id]').length && !$(e.target).closest('#google-maps').length) {
					$('section[data-route-id].active').trigger('unfocus');
				}
			});
		},
	})]);
