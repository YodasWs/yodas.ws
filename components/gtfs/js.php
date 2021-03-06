<?php session_start(); ?>
window.csv = window.csv || {}
csv.splitRow = function(r) {
	r = r.match(/((?!,)|(?=^))("[^"]*"|[^,]*)(?=,|$)/g)
	if (r && r.length) r = r.map(function(t) {
		return t.trim('"')
	})
	return r
}
gtfs = window.gtfs || {}
gtfs.extremes = { north:-90, south:180, east:-180, west:180 }
gtfs.loadedFiles = []
gtfs.tripRoute = {}
gtfs.routes = {}
gtfs.stops = {}
gtfs.poly = {}
gtfs.setShapeRoute = function(shape, route) {
	gtfs.poly[shape] = gtfs.poly[shape] || {}
	if (gtfs.routes[route]) {
		gtfs.routes[route].shape = shape
		if (!gtfs.poly[shape].color && gtfs.routes[route].color)
			gtfs.poly[shape].color = gtfs.routes[route].color
		switch (Number.parseInt(gtfs.routes[route].type, 10)) {
		case 2: // Rail
			gtfs.poly[shape].opacity = 1
			gtfs.poly[shape].weight = 3
			break;
		case 3: // Bus
			gtfs.poly[shape].weight = 2
			break;
		case 5: // Cable Car
			gtfs.poly[shape].weight = 2
			break;
		case 6: // Gondola
			gtfs.poly[shape].opacity = 1
			gtfs.poly[shape].weight = 2
			break;
		case 7: // Funicular
			gtfs.poly[shape].opacity = 1
			gtfs.poly[shape].weight = 2
			break;
		}
	}
	if (gtfs.poly[shape].Polyline) {
		if (gtfs.poly[shape].opacity) gtfs.poly[shape].Polyline.setOptions({opacity: gtfs.poly[shape].opacity})
		if (gtfs.poly[shape].weight) gtfs.poly[shape].Polyline.setOptions({strokeWeight: gtfs.poly[shape].weight})
		if (gtfs.poly[shape].color) gtfs.poly[shape].Polyline.setOptions({strokeColor: gtfs.poly[shape].color})
	}
}
gtfs.parseHeader = function(h) {
	var r = {}
	csv.splitRow(h).forEach(function(h, i) {
		r[h.trim()] = i
	})
	return r
}
gtfs.setBounds = function(pts) {
	var bounds = {
		north:-90,
		south:180,
		east:-180,
		west:180
	}
	pts.forEach(function(p){
		bounds.south = Math.min(bounds.south, p.lat)
		bounds.north = Math.max(bounds.north, p.lat)
		bounds.east = Math.max(bounds.east, p.lng)
		bounds.west = Math.min(bounds.west, p.lng)
	})
	gtfs.map.fitBounds(bounds)
}
gtfs.saveShapePoint = function(shape, lat, lng) {
	// Save Shape Point
	gtfs.poly[shape] = gtfs.poly[shape] || {}
	gtfs.poly[shape].path = gtfs.poly[shape].path || []
	gtfs.poly[shape].path.push({
		lat: lat, lng: lng
	})
}
gtfs.listAgencies = function(data) {
	data = data.split("\n")
	var head = gtfs.parseHeader(data.shift()),
		$a = $('<section class="agency">')
	data.forEach(function(r){
		r = csv.splitRow(r)
		if (!r || r[head.agency_id] == '') return
		$a.append('<h1>' + r[head.agency_name])
		if (r[head.agency_url]) $a.append('<a href="' + r[head.agency_url] + '" target="_blank">Agency Website</a>')
	})
	$('main section.agency').remove()
	$a.appendTo('main')
}
gtfs.parseStops = function(data) {
	data = data.split("\n")
	var head = gtfs.parseHeader(data.shift())
	data.forEach(function(r){
		r = csv.splitRow(r)
		if (!r || r[head.stop_id] == '') return
		// Save Pertinent Stop Information for easy retrieval
		gtfs.stops[r[head.stop_id]] = {
			lat: Number.parseFloat(r[head.stop_lat]),
			lng: Number.parseFloat(r[head.stop_lon]),
			name: r[head.stop_name],
			// Place Google Maps Marker
			Marker: new google.maps.Marker({
				position:{
					lat: Number.parseFloat(r[head.stop_lat]),
					lng: Number.parseFloat(r[head.stop_lon]),
				},
				icon:{
					url:'http://chart.apis.google.com/chart?chst=d_map_xpin_letter&chld=pin%7C%20%7CFE7569',
					anchor:new google.maps.Point(11,34),
					origin:new google.maps.Point(0,0),
					size:new google.maps.Size(21,34)
				},
				title: r[head.stop_name],
				visible: false,
				map: gtfs.map
			})
		}
	})
	$(document).trigger($.Event('loaded', { file:'stops.txt' }))
}
// Load and Draw GTFS Shapes
gtfs.loadGTFS = function(url) {
	if (
		!localStorage.getItem('gtfs.' + url + '.agency.txt') ||
		!localStorage.getItem('gtfs.' + url + '.agency.date') ||
		Number.parseInt(localStorage.getItem('gtfs.' + url + '.agency.date'), 10) < Date.now() - 1000 * 60 * 60 * 24 * 7
	) $.ajax({
		url:'/gtfs/' + url + '/agency.txt',
		dateType:'text',
		success:function(data){
			localStorage.setItem('gtfs.' + url + '.agency.date', Date.now())
			localStorage.setItem('gtfs.' + url + '.agency.txt', data)
			gtfs.listAgencies(data)
		}
	}); else gtfs.listAgencies(localStorage.getItem('gtfs.' + url + '.agency.txt'))
	if (
		!localStorage.getItem('gtfs.' + url + '.routes.date') ||
		!localStorage.getItem('gtfs.' + url + '.routes.head') ||
		!localStorage.getItem('gtfs.' + url + '.routes.array') ||
		Number.parseInt(localStorage.getItem('gtfs.' + url + '.routes.date'), 10) < Date.now() - 1000 * 60 * 60 * 24 * 7
	) $.ajax({
		url:'/gtfs/' + url + '/routes.txt',
		dateType:'text',
		success:function(data){
			data = data.split("\n")
			var head = gtfs.parseHeader(data.shift())
			data.forEach(function(r){
				r = r.split(',')
				if (!r || r[head.route_id] == '') return
				// Save Pertinent Route Data
				gtfs.routes[r[head.route_id]] = gtfs.routes[r[head.route_id]] || {}
				gtfs.routes[r[head.route_id]].txtColor = '#' + (r[head.route_text_color] || '000000')
				gtfs.routes[r[head.route_id]].color = '#' + (r[head.route_color] || 'ffffff')
				gtfs.routes[r[head.route_id]].icon = '&#x1f6' + (r[head.x_route_icon] || '8d') + ';'
				gtfs.routes[r[head.route_id]].name = r[head.route_long_name]
				gtfs.routes[r[head.route_id]].type = r[head.route_type]
				gtfs.routes[r[head.route_id]].num = r[head.route_short_name]
				gtfs.routes[r[head.route_id]].stops = []
			})
			localStorage.setItem('gtfs.' + url + '.routes.date', Date.now())
			localStorage.setItem('gtfs.' + url + '.routes.head', JSON.stringify(head))
			localStorage.setItem('gtfs.' + url + '.routes.array', JSON.stringify(gtfs.routes))
			$(document).trigger($.Event('loaded', { file:'routes.txt' }))
		}
	}); else {
		gtfs.routes = JSON.parse(localStorage['gtfs.' + url + '.routes.array'])
		$(document).trigger($.Event('loaded', { file:'routes.txt' }))
	}
	$.ajax({
		url:'/gtfs/' + url + '/trips.txt',
		dateType:'text',
		success:function(data){
			data = data.split("\n")
			var head = gtfs.parseHeader(data.shift())
			if (head.shape_id || head.shape_id === 0)
			data.forEach(function(r){
				r = r.split(',')
				if (!r || r[head.trip_id] == '') return
				route = r[head.route_id]
				shape = r[head.shape_id]
				// Associate Shape to Route
				if (shape != '') gtfs.setShapeRoute(shape, route)
				// Associate Trip to Route
				gtfs.tripRoute[r[head.trip_id]] = route
			})
			$(document).trigger($.Event('loaded', { file:'trips.txt' }))
		}
	})
	$.ajax({
		url:'/gtfs/' + url + '/shapes.txt',
		dateType:'text',
		success:function(data){
			data = data.split("\n")
			var head = gtfs.parseHeader(data.shift())
			data.forEach(function(r){
				r = csv.splitRow(r)
				if (!r || r[head.shape_id] == '') return
				// Save Shape Point
				gtfs.poly[r[head.shape_id]] = gtfs.poly[r[head.shape_id]] || {}
				gtfs.poly[r[head.shape_id]].path = gtfs.poly[r[head.shape_id]].path || []
				gtfs.poly[r[head.shape_id]].path.push({
					lat: Number.parseFloat(r[head.shape_pt_lat]),
					lng: Number.parseFloat(r[head.shape_pt_lon])
				})
				// Save Extreme Points for Map Bounds
				gtfs.extremes.south = Math.min(gtfs.extremes.south, Number.parseFloat(r[head.shape_pt_lat]))
				gtfs.extremes.north = Math.max(gtfs.extremes.north, Number.parseFloat(r[head.shape_pt_lat]))
				gtfs.extremes.east = Math.max(gtfs.extremes.east, Number.parseFloat(r[head.shape_pt_lon]))
				gtfs.extremes.west = Math.min(gtfs.extremes.west, Number.parseFloat(r[head.shape_pt_lon]))
			})
			// Set Map Bounds
			gtfs.map.fitBounds(gtfs.extremes)
			// Paste Shapes on Map
			for (var i in gtfs.poly) {
				if (!gtfs.poly.hasOwnProperty(i)) continue;
				if (typeof i === 'undefined') continue;
				if (i === 'undefined') continue;
				gtfs.poly[i].Polyline = new google.maps.Polyline({
					path: gtfs.poly[i].path,
					geodesic: true,
					strokeColor: gtfs.poly[i].color || '#008800',
					strokeWeight: gtfs.poly[i].weight || 2,
					opacity: gtfs.poly[i].opacity || .6,
					strokeOpacity: 1,
					clickable: true
				})
				gtfs.poly[i].Polyline.setMap(gtfs.map)
				// TODO: When Polyline clicked, activate Route Stop List
			}
			$(document).trigger($.Event('loaded', { file:'shapes.txt' }))
		}
	})
	if (
		!localStorage.getItem('gtfs.' + url + '.stops.date') ||
		!localStorage.getItem('gtfs.' + url + '.stops.text') ||
		Number.parseInt(localStorage.getItem('gtfs.' + url + '.stops.date'), 10) < Date.now() - 1000 * 60 * 60 * 24 * 7
	) $.ajax({
		url:'/gtfs/' + url + '/stops.txt',
		success:function(data){
			gtfs.parseStops(data)
			localStorage.setItem('gtfs.' + url + '.stops.date', Date.now())
			localStorage.setItem('gtfs.' + url + '.stops.text', data)
		}
	}); else {
		gtfs.parseStops(localStorage.getItem('gtfs.' + url + '.stops.text'))
	}
	$.ajax({
		url:'/gtfs/' + url + '/stop_times.txt',
		success:function(data){
			data = data.split("\n")
			var head = gtfs.parseHeader(data.shift())
			data.forEach(function(r){
				r = r.split(',')
				var trip_id = r[head.trip_id],
					stop_id = r[head.stop_id],
					route_id = gtfs.tripRoute[trip_id]
				if (!r[head.stop_id]) return
				if (
					gtfs.routes[route_id] && gtfs.routes[route_id].stops &&
					gtfs.routes[route_id].stops[gtfs.routes[route_id].stops.length - 1] != stop_id
				) gtfs.routes[route_id].stops.push(stop_id)
				if (gtfs.routes[route_id] && !gtfs.routes[route_id].shape) gtfs.routes[route_id].shape = trip_id
			})
			// Build Lists of Route Stations
			for (i in gtfs.routes) {
				var r = gtfs.routes[i], $l = $('<ol>')
					$t = $('<section data-route-id="' + i + '">')
				if (r.num) {
					$t.append('<h1 style="background:' + r.color + ';color:' + r.txtColor + '">' + r.icon + ' ' + r.num)
					$t.append('<h2 style="background:' + r.color + ';color:' + r.txtColor + '">' + r.name)
				} else {
					$t.append('<h1 style="background:' + r.color + ';color:' + r.txtColor + '">' + r.icon + ' ' + r.name)
				}
				r.stops.forEach(function(s){
					if (!gtfs.stops[s] || !gtfs.stops[s].name) {
						console.error('Stop ' + s + ' not found!')
						return
					}
					$l.append('<li data-stop-id="' + s + '">' + gtfs.stops[s].name)
				})
				$('main').append($t.append($l))
				if (!gtfs.poly[r.shape]) {
					gtfs.poly[r.shape] = {}
					gtfs.poly[r.shape].path = []
					// Use this List of Stops to draw a Polyline
					r.stops.forEach(function(s) {
						gtfs.poly[r.shape].path.push(gtfs.stops[s])
					})
					gtfs.setShapeRoute(r.shape, i)
					// Draw Polyline
					gtfs.poly[r.shape].Polyline = new google.maps.Polyline({
						path: gtfs.poly[r.shape].path,
						geodesic: true,
						strokeColor: (typeof gtfs.poly[r.shape].color == 'string' ? gtfs.poly[r.shape].color : '#008800'),
						strokeWeight: (gtfs.poly[r.shape].weight || 2),
						opacity: gtfs.poly[r.shape].opacity || .6,
						strokeOpacity: 1,
						clickable: true
					})
					gtfs.poly[r.shape].Polyline.setMap(gtfs.map)
				}
			}
			$(document).trigger($.Event('loaded', { file:'stop_times.txt' }))
		}
	})
}
$('script[src*="maps.google.com/maps/api/js"]').load(function(){
	$('#google-maps, .gtfs').show()
	// Load Google Maps
	gtfs.map = new google.maps.Map(document.getElementById('google-maps'), {
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		keyboardShortcuts: true,
		disableDefaultUI: true,
		scaleControl: true,
		scrollWheel: true,
		zoomControl: true,
		maxZoom: 18,
		minZoom: 9
	})
<?php
foreach ($_SESSION['gtfs_locs'] as $loc) {
	echo "gtfs.loadGTFS('$loc');";
}
?>
	gtfs.map.zoom = gtfs.map.getZoom()
	gtfs.map.addListener('zoom_changed', function(e) {
		// Make Lines Thicker for Easier Reading
		var weightAdjust = (gtfs.map.zoom >= 14 ? gtfs.map.zoom - 13 : 6 - Math.floor((gtfs.map.zoom - 1) / 2))
		for (var i in gtfs.poly) {
			if (!gtfs.poly[i].Polyline) continue;
			gtfs.poly[i].Polyline.setOptions({
				strokeWeight: gtfs.poly[i].weight + weightAdjust
			})
		}
	})
})
$(document).on('loaded', function(e) {
	gtfs.loadedFiles.push(e.file)
}).ready(function(){
	// Highlight Routes
	$('main').on('click', 'section[data-route-id]', function(e) {
		var isOpen = $(e.target).closest('section[data-route-id]').is('.active'),
			switching = $(e.target).closest('li[data-stop-id]').length > 0
		// If switched Stops, don't reset Map
		if (isOpen && switching) {
			switching = switching && $(e.target).closest('li[data-stop-id]').is('.active')
		}
		// Highlight Route on Map
		if (!isOpen) {
			$('section[data-route-id].active').trigger('unfocus')
			var $s = $(e.target).closest('section[data-route-id]').addClass('active')
				route = $s.data('route-id'),
				shape = gtfs.routes[route].shape,
				pts = []
			if (!shape || !gtfs.poly[shape]) return
			gtfs.poly[shape].Polyline.setOptions({
				strokeWeight: 4,
				opacity: 1,
				zIndex: 1
			})
			gtfs.routes[route].stops.forEach(function(s){
				pts.push(gtfs.stops[s])
			})
			$('html,body').animate({scrollTop: 0}, 300, function() {
				if (pts.length) gtfs.setBounds(pts)
				else gtfs.map.fitBounds(gtfs.extremes)
			})
		} else if (!switching) {
			// Reset Map
			$('section[data-route-id].active').trigger('unfocus')
		}
	}).on('unfocus', function(e) {
		var $s = $(e.target).closest('section[data-route-id]').removeClass('active'),
			route = $s.data('route-id'),
			shape = gtfs.routes[route].shape
		if (!shape || !gtfs.poly[shape]) return
		gtfs.map.fitBounds(gtfs.extremes)
		gtfs.poly[shape].Polyline.setOptions({
			strokeWeight: gtfs.poly[shape].weight,
			opacity: gtfs.poly[shape].opacity || .6,
			zIndex: 0
		})
	})
	$(document).on('click', function(e) {
		if (!$(e.target).closest('section[data-route-id]').length && !$(e.target).closest('#google-maps').length) {
			$('section[data-route-id].active').trigger('unfocus')
		}
	})
	// Show Station/Stop on Map
	$('main').on('click', 'li[data-stop-id]', function(e) {
		var $t = $(e.target).closest('li[data-stop-id]'),
			id = $t.data('stop-id'),
			route_id = $t.parents('section[data-route-id]').data('route-id'),
			isOpen = gtfs.stops[id].Marker.getVisible()
		if ($(e.target).closest('section[data-route-id]').is('.active') || !$t.is('.active')) {
			gtfs.hideStops()
			$('li[data-stop-id].active').removeClass('active')
			$('section[data-route-id].highlighted').removeClass('highlighted')
			if (!isOpen) {
				$('li[data-stop-id="' + id + '"]').addClass('active').parents('section[data-route-id]').addClass('highlighted')
				gtfs.stops[id].Marker.setVisible(true)
				gtfs.map.panTo(gtfs.stops[id].Marker.getPosition())
			}
		}
	})
})
gtfs.hideStops = function() {
	for (var id in gtfs.stops) {
		gtfs.stops[id].Marker.setVisible(false)
	}
}
