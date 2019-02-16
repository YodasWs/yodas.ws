const csv = {};
if (typeof csv.splitRow !== 'function') csv.splitRow = (r) => {
	r = r.match(/((?!,)|(?=^))("[^"]*"|[^,]*)(?=,|$)/g);
	if (r && r.length) r = r.map(t => t.replace(/^[\s\uFEFF\xA0]*"*[\s\uFEFF\xA0]*|[\s\uFEFF\xA0]*"*[\s\uFEFF\xA0]*$/g, ''));
	return r;
};

const gtfs = {
	extremes: { north: -90, south: 180, east: -180, west: 180 },
	loadedFiles: [],
	tripRoute: {},
	routes: {},
	stops: {},
	poly: {},
};

gtfs.setShapeRoute = (shape, route_id) => {
	gtfs.poly[shape] = gtfs.poly[shape] || {};
	if (gtfs.routes[route_id]) {
		gtfs.routes[route_id].shape = shape;
		if (!gtfs.poly[shape].color && gtfs.routes[route_id].color)
			gtfs.poly[shape].color = gtfs.routes[route_id].color;
		switch (Number.parseInt(gtfs.routes[route_id].type, 10)) {
			case 2: // Rail
				gtfs.poly[shape].opacity = 1;
				gtfs.poly[shape].weight = 3;
				break;
			case 3: // Bus
				gtfs.poly[shape].weight = 2;
				break;
			case 5: // Cable Car
				gtfs.poly[shape].weight = 2;
				break;
			case 6: // Gondola
				gtfs.poly[shape].opacity = 1;
				gtfs.poly[shape].weight = 2;
				break;
			case 7: // Funicular
				gtfs.poly[shape].opacity = 1;
				gtfs.poly[shape].weight = 2;
				break;
		}
	}
	if (gtfs.poly[shape].Polyline) {
		if (gtfs.poly[shape].opacity) gtfs.poly[shape].Polyline.setOptions({opacity: gtfs.poly[shape].opacity});
		if (gtfs.poly[shape].weight) gtfs.poly[shape].Polyline.setOptions({strokeWeight: gtfs.poly[shape].weight});
		if (gtfs.poly[shape].color) gtfs.poly[shape].Polyline.setOptions({strokeColor: gtfs.poly[shape].color});
	}
};

gtfs.parseHeader = (h) => {
	const r = {};
	csv.splitRow(h).forEach((h, i) => {
		r[h.trim()] = i;
	});
	return r;
};

gtfs.setBounds = (pts) => {
	if (!gtfs.map || !gtfs.map.fitBounds) return;
	const bounds = {
		north: -90,
		south: 180,
		east: -180,
		west: 180,
	};
	pts.forEach((p) => {
		bounds.south = Math.min(bounds.south, p.lat);
		bounds.north = Math.max(bounds.north, p.lat);
		bounds.east = Math.max(bounds.east, p.lng);
		bounds.west = Math.min(bounds.west, p.lng);
	});
	gtfs.map.fitBounds(bounds);
};

gtfs.saveShapePoint = (shape, lat, lng) => {
	// Save Shape Point
	gtfs.poly[shape] = gtfs.poly[shape] || {};
	gtfs.poly[shape].path = gtfs.poly[shape].path || [];
	gtfs.poly[shape].path.push({
		lat,
		lng,
	});
};

gtfs.listAgencies = function(data) {
	data = data.split('\n')
	console.log('in gtfs.listAgencies, data:', data);
	const head = gtfs.parseHeader(data.shift());
	const $a = document.createElement('section');
	$a.classList.add('agency');
	data.forEach((r) => {
		const row = csv.splitRow(r);
		if (!row || row[head.agency_id] === '') return;
		console.log('agency:', row);
		console.log('head:', head);
		const h1 = document.createElement('h1');
		h1.innerText = row[head.agency_name];
		$a.appendChild(h1);
		console.log('$a:', $a);
		if (row[head.agency_url]) {
			const lnkAgency = document.createElement('a');
			lnkAgency.href = row[head.agency_url];
			lnkAgency.target = '_blank';
			lnkAgency.innerText = 'Agency Website';
			$a.appendChild(lnkAgency);
		}
	});
	// $('main section.agency').remove();
	console.log('built', $a);
	element[0].appendChild($a);
	// TODO: Note that AngularJS is replacing everything in <main> with contents of home.html
};

gtfs.parseStops = (data) => {
	if (data.length > 10) {
		console.log('Too many gtfs stops!');
		return;
	}
	data.forEach((r) => {
		const row = csv.splitRow(r);
		if (!row || row[head.stop_id] == '') return;
		// Save Pertinent Stop Information for easy retrieval
		if (Number.parseFloat(row[head.stop_lat]) && Number.parseFloat(row[head.stop_lon]))
			gtfs.stops[row[head.stop_id]] = {
				lat: Number.parseFloat(row[head.stop_lat]),
				lng: Number.parseFloat(row[head.stop_lon]),
				name: row[head.stop_name],
			};
		if (window.google) {
			// Place Google Maps Marker
			gtfs.stops[row[head.stop_id]].Marker = new google.maps.Marker({
				position: {
					lat: Number.parseFloat(row[head.stop_lat]),
					lng: Number.parseFloat(row[head.stop_lon]),
				},
				icon: {
					url: 'http://chart.apis.google.com/chart?chst=d_map_xpin_letter&chld=pin%7C%20%7CFE7569',
					anchor: new google.maps.Point(11,34),
					origin: new google.maps.Point(0,0),
					size: new google.maps.Size(21,34),
				},
				title: row[head.stop_name],
				visible: false,
				map: gtfs.map,
			});
		}
	});
	console.log('gtfs stops:', gtfs.stops);
	$(document).trigger($.Event('loaded', {
		file: 'stops.txt',
		locale,
	}));
};

// Load and Draw GTFS Shapes
gtfs.loadGTFS = function(url) {
	console.log('in gtfs.loadGTFS', url);
	if (
		!localStorage.getItem(`gtfs.${url}.agency.txt`)
		|| !localStorage.getItem(`gtfs.${url}.agency.date`)
		|| Number.parseInt(localStorage.getItem(`gtfs.${url}.agency.date`), 10) < Date.now() - 1000 * 60 * 60 * 24 * 7
	) $.ajax({
		url: `/yodas.ws/gtfs/${url}/agency.txt`,
		dateType: 'text',
		success: (data) => {
			localStorage.setItem(`gtfs.${url}.agency.date`, Date.now());
			localStorage.setItem(`gtfs.${url}.agency.txt`, data);
			gtfs.listAgencies(data);
			$(document).trigger($.Event('loaded', {
				file: 'agency.txt',
				locale,
			}));
		},
	}); else {
		gtfs.listAgencies(localStorage.getItem(`gtfs.${url}.agency.txt`));
		$(document).trigger($.Event('loaded', {
			file: 'agency.txt',
			locale,
		}));
	}

	if (
		!localStorage.getItem(`gtfs.${url}.routes.date`)
		|| !localStorage.getItem(`gtfs.${url}.routes.head`)
		|| !localStorage.getItem(`gtfs.${url}.routes.array`)
		|| Number.parseInt(localStorage.getItem(`gtfs.{url}.routes.date`), 10) < Date.now() - 1000 * 60 * 60 * 24 * 7
	) $.ajax({
		url: `/yodas.ws/gtfs/${url}/routes.txt`,
		dateType: 'text',
		success: (data) => {
			data = data.split('\n');
			const head = gtfs.parseHeader(data.shift());
			data.forEach((r) => {
				const row = csv.splitRow(r);
				if (!row || row[head.route_id] == '') return;
				// Save Pertinent Route Data
				gtfs.routes[row[head.route_id]] = gtfs.routes[row[head.route_id]] || {};
				gtfs.routes[row[head.route_id]].txtColor = '#' + (row[head.route_text_color] || '000000');
				gtfs.routes[row[head.route_id]].color = '#' + (row[head.route_color] || 'ffffff');
				gtfs.routes[row[head.route_id]].icon = `&#x1f6${row[head.x_route_icon] || '8d'};`;
				gtfs.routes[row[head.route_id]].name = row[head.route_long_name];
				gtfs.routes[row[head.route_id]].type = row[head.route_type];
				gtfs.routes[row[head.route_id]].num = row[head.route_short_name];
				gtfs.routes[row[head.route_id]].stops = [];
			});
			localStorage.setItem(`gtfs.${url}.routes.date`, Date.now());
			localStorage.setItem(`gtfs.${url}.routes.head`, JSON.stringify(head));
			localStorage.setItem(`gtfs.${url}.routes.array`, JSON.stringify(gtfs.routes));
			$(document).trigger($.Event('loaded', {
				file: 'routes.txt',
				locale,
			}));
		},
	}); else {
		gtfs.routes = JSON.parse(localStorage[`gtfs.${url}.routes.array`]);
		$(document).trigger($.Event('loaded', {
			file: 'routes.txt',
			locale,
		}));
	}

	gtfs.shapeTrips = (head, data) => {
		if (Number.isInteger(head.shape_id) && head.shape_id >= 0) {
			data.forEach((r) => {
				r = r.split(',');
				if (!r || r[head.trip_id] === '') return;
				const route_id = r[head.route_id];
				const shape = r[head.shape_id];
				// Associate Shape to Route
				if (shape != '') gtfs.setShapeRoute(shape, route_id);
				// Associate Trip to Route
				gtfs.tripRoute[r[head.trip_id]] = route_id;
			});
		}
		$(document).trigger($.Event('loaded', {
			file: 'trips.txt',
			locale,
		}));
	};

	if (
		!localStorage.getItem(`gtfs.${url}.trips.date`)
		|| !localStorage.getItem(`gtfs.${url}.trips.head`)
		|| !localStorage.getItem(`gtfs.${url}.trips.array`)
		|| Number.parseInt(localStorage.getItem(`gtfs.{url}.trips.date`), 10) < Date.now() - 1000 * 60 * 60 * 24 * 7
	) $.ajax({
		url: `/yodas.ws/gtfs/${url}/trips.txt`,
		dateType: 'text',
		success: (data) => {
			data = data.split('\n');
			const head = gtfs.parseHeader(data.shift());
			gtfs.tripRoute = data.map(r => r.split(','));
			localStorage.setItem(`gtfs.${url}.trips.date`, Date.now());
			localStorage.setItem(`gtfs.${url}.trips.head`, JSON.stringify(head));
			localStorage.setItem(`gtfs.${url}.trips.array`, JSON.stringify(gtfs.tripRoute));
			gtfs.shapeTrips(head, gtfs.tripRoute);
			$(document).trigger($.Event('loaded', {
				file: 'agency.txt',
				locale,
			}));
		},
	}); else {
		const head = localStorage.getItem(`gtfs.${url}.trips.head`);
		gtfs.tripRoute = localStorage.getItem(`gtfs.${url}.trips.array`);
		gtfs.shapeTrips(head, gtfs.tripRoute);
		$(document).trigger($.Event('loaded', {
			file: 'tripss.txt',
			locale,
		}));
	}

	$.ajax({
		url: `/yodas.ws/gtfs/${url}/shapes.txt`,
		dateType: 'text',
		success: (data) => {
			data = data.split('\n');
			const head = gtfs.parseHeader(data.shift());
			data.forEach((r) => {
				r = csv.splitRow(r);
				if (!r || r[head.shape_id] == '') return;
				// Save Shape Point
				gtfs.poly[r[head.shape_id]] = gtfs.poly[r[head.shape_id]] || {};
				gtfs.poly[r[head.shape_id]].path = gtfs.poly[r[head.shape_id]].path || [];
				gtfs.poly[r[head.shape_id]].path.push({
					lat: Number.parseFloat(r[head.shape_pt_lat]),
					lng: Number.parseFloat(r[head.shape_pt_lon]),
				});
				// Save Extreme Points for Map Bounds
				gtfs.extremes.south = Math.min(gtfs.extremes.south, Number.parseFloat(r[head.shape_pt_lat]));
				gtfs.extremes.north = Math.max(gtfs.extremes.north, Number.parseFloat(r[head.shape_pt_lat]));
				gtfs.extremes.east = Math.max(gtfs.extremes.east, Number.parseFloat(r[head.shape_pt_lon]));
				gtfs.extremes.west = Math.min(gtfs.extremes.west, Number.parseFloat(r[head.shape_pt_lon]));
			});
			// Set Map Bounds
			if (gtfs.map && gtfs.map.fitBounds) 
				gtfs.map.fitBounds(gtfs.extremes);
			// Paste Shapes on Map
			Object.values(gtfs.poly).forEach((poly, i) => {
				if (typeof i === 'undefined') return;
				if (i === 'undefined') return;
				if (window.google && gtfs.map)  {
					poly.Polyline = new google.maps.Polyline({
						path: poly.path,
						geodesic: true,
						strokeColor: poly.color || '#008800',
						strokeWeight: poly.weight || 2,
						opacity: poly.opacity || .6,
						strokeOpacity: 1,
						clickable: true,
					});
					poly.Polyline.setMap(gtfs.map);
				}
				// TODO: When Polyline clicked, activate Route Stop List
			});
			$(document).trigger($.Event('loaded', {
				file: 'shapes.txt',
				locale,
			}));
		},
	});

	if (
		!localStorage.getItem(`gtfs.${url}.stops.date`)
		|| !localStorage.getItem(`gtfs.${url}.stops.head`)
		|| !localStorage.getItem(`gtfs.${url}.stops.text`)
		|| Number.parseInt(localStorage.getItem(`gtfs.${url}.stops.date`), 10) < Date.now() - 1000 * 60 * 60 * 24 * 7
	) $.ajax({
		url: `/yodas.ws/gtfs/${url}/stops.txt`,
		success: (data) => {
			data = data.split('\n');
			const head = gtfs.parseHeader(data.shift());
			gtfs.parseStops(head, data);
			localStorage.setItem(`gtfs.${url}.stops.date`, Date.now());
			localStorage.setItem(`gtfs.${url}.stops.head`, head);
			localStorage.setItem(`gtfs.${url}.stops.text`, data);
		},
	}); else {
		gtfs.parseStops(
			JSON.parse(localStorage.getItem(`gtfs.${url}.stops.head`)),
			JSON.parse(localStorage.getItem(`gtfs.${url}.stops.text`))
		);
	}

	$.ajax({
		url: `/yodas.ws/gtfs/${url}/stop_times.txt`,
		success: (data) => {
			data = data.split('\n');
			const head = gtfs.parseHeader(data.shift());
			console.log('loaded gtfs stop_times.txt');
			data.forEach((r) => {
				r = r.split(',');
				if (!r[head.stop_id]) return;
				const trip_id = r[head.trip_id];
				const stop_id = r[head.stop_id];
				const route_id = gtfs.tripRoute[trip_id];
				if (
					gtfs.routes[route_id]
					&& gtfs.routes[route_id].stops
					&& gtfs.routes[route_id].stops[gtfs.routes[route_id].stops.length - 1] !== stop_id
				) gtfs.routes[route_id].stops.push(stop_id);
				if (gtfs.routes[route_id] && !gtfs.routes[route_id].shape) gtfs.routes[route_id].shape = trip_id;
			});
			console.log('adding gtfs routes:', gtfs.routes);
			// Build Lists of Route Stations
			Object.entries(gtfs.routes).forEach(([i, r]) => {
				console.log('adding gtfs route:', r);
				const $l = $('<ol>');
				const $t = $(`<section data-route-id="${i}">`);
				if (r.num) {
					$t.append('<h1 style="background:' + r.color + ';color:' + r.txtColor + '">' + r.icon + ' ' + r.num);
					$t.append('<h2 style="background:' + r.color + ';color:' + r.txtColor + '">' + r.name);
				} else {
					$t.append('<h1 style="background:' + r.color + ';color:' + r.txtColor + '">' + r.icon + ' ' + r.name);
				}
				r.stops.forEach((s) => {
					if (!gtfs.stops[s] || !gtfs.stops[s].name) {
						console.error('Stop ' + s + ' not found!');
						return;
					}
					$l.append('<li data-stop-id="' + s + '">' + gtfs.stops[s].name);
				});
				console.log('gtfs element:', element[0]);
				element[0].appendChild($t.append($l)[0]);
				if (!gtfs.poly[r.shape]) {
					gtfs.poly[r.shape] = {};
					gtfs.poly[r.shape].path = [];
					// Use this List of Stops to draw a Polyline
					r.stops.forEach((s) => {
						gtfs.poly[r.shape].path.push(gtfs.stops[s]);
					});
					gtfs.setShapeRoute(r.shape, i)
					if (window.google) {
						// Draw Polyline
						gtfs.poly[r.shape].Polyline = new google.maps.Polyline({
							path: gtfs.poly[r.shape].path,
							geodesic: true,
							strokeColor: (typeof gtfs.poly[r.shape].color == 'string' ? gtfs.poly[r.shape].color : '#008800'),
							strokeWeight: (gtfs.poly[r.shape].weight || 2),
							opacity: gtfs.poly[r.shape].opacity || .6,
							strokeOpacity: 1,
							clickable: true,
						});
						if (gtfs.map && gtfs.map.fitBounds) 
							gtfs.poly[r.shape].Polyline.setMap(gtfs.map);
					}
				}
			});
			$(document).trigger($.Event('loaded', {
				file: 'stop_times.txt',
				locale,
			}));
		},
	});
};

gtfs.hideStops = () => {
	for (const id in gtfs.stops) {
		gtfs.stops[id].Marker.setVisible(false);
	}
};
