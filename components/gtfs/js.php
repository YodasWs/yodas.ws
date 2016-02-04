<?php session_start(); ?>
window.gtfs = gtfs || {}
gtfs.extremes = { north:-90, south:180, east:-180, west:180 }
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
			gtfs.poly[shape].label = '🚆'
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
	h.split(',').forEach(function(h, i) {
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
// Load and Draw GTFS Shapes
gtfs.loadGTFS = function(url) {
	$.ajax({
		url:'/gtfs/' + url + '/agency.txt',
		dateType:'text',
		success:function(data){
			data = data.split("\n")
			var head = gtfs.parseHeader(data.shift())
			data.forEach(function(r){
				r = r.split(',')
				if (r[0] == '') return
				var $a = $('<section class="agency">')
				$a.append('<h1>' + r[head.agency_name])
				if (r[head.agency_url]) $a.append('<a href="' + r[head.agency_url] + '" target="_blank">Agency Website</a>')
				$a.appendTo('main')
			})
		}
	})
	$.ajax({
		url:'/gtfs/' + url + '/routes.txt',
		dateType:'text',
		success:function(data){
			data = data.split("\n")
			var head = gtfs.parseHeader(data.shift())
			data.forEach(function(r){
				r = r.split(',')
				if (r[0] == '') return
				// Save Pertinent Route Data
				gtfs.routes[r[head.route_id]] = gtfs.routes[r[head.route_id]] || {}
				gtfs.routes[r[head.route_id]].txtColor = '#' + (r[head.route_text_color] || '000000')
				gtfs.routes[r[head.route_id]].color = '#' + (r[head.route_color] || 'ffffff')
				gtfs.routes[r[head.route_id]].name = r[head.route_long_name]
				gtfs.routes[r[head.route_id]].type = r[head.route_type]
				gtfs.routes[r[head.route_id]].num = r[head.route_short_name]
				gtfs.routes[r[head.route_id]].stops = []
				switch (Number.parseInt(gtfs.routes[r[head.route_id]].type, 10)) {
				case 2: // Rail
					gtfs.routes[r[head.route_id]].label = '🚆'
					break;
				case 3: // Bus
					gtfs.routes[r[head.route_id]].label = '🚏'
					break;
				case 5: // Cable Car
					gtfs.routes[r[head.route_id]].label = '🚞'
					break;
				case 6: // Gondola
					gtfs.routes[r[head.route_id]].label = '🚡'
					break;
				case 7: // Funicular
					gtfs.routes[r[head.route_id]].label = '🚞'
					break;
				}
			})
			$(document).trigger($.Event('loaded', { file:'routes.txt' }))
		}
	})
	$.ajax({
		url:'/gtfs/' + url + '/trips.txt',
		dateType:'text',
		success:function(data){
			data = data.split("\n")
			var head = gtfs.parseHeader(data.shift())
			data.forEach(function(r){
				r = r.split(',')
				if (!head.shape_id) return
				if (r[0] == '') return
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
				r = r.split(',')
				if (r[0] == '') return
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
	$.ajax({
		url:'/gtfs/' + url + '/stops.txt',
		success:function(data){
			data = data.split("\n")
			var head = gtfs.parseHeader(data.shift())
			data.forEach(function(r){
				r = r.split(',')
				if (r[0] == '') return
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
						title: r[head.stop_name],
						visible: false,
						map: gtfs.map
					})
				}
			})
			$(document).trigger($.Event('loaded', { file:'stops.txt' }))
		}
	})
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
				// TODO: If the same as the last stop, don't add
				if (gtfs.routes[route_id] && gtfs.routes[route_id].stops)
					gtfs.routes[route_id].stops.push(stop_id)
				if (!gtfs.routes[route_id].shape) gtfs.routes[route_id].shape = trip_id
			})
			// Build Lists of Route Stations
			for (i in gtfs.routes) {
				var r = gtfs.routes[i], $l = $('<ol>')
					$t = $('<section data-route-id="' + i + '">')
				$t.append('<h1 style="background:' + r.color + ';color:' + r.txtColor + '">' + (r.num ? r.num + ' ' : '') + r.name)
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
						opacity: gtfs.poly[i].opacity || .6,
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
	// Load Google Maps
	gtfs.map = new google.maps.Map(document.getElementById('gtfs'), {
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		keyboardShortcuts: true,
		disableDefaultUI: true,
		scaleControl: true,
		scrollWheel: true,
		zoomControl: true,
		maxZoom: 18,
		minZoom: 10
	})
<?php
foreach ($_SESSION['gtfs_locs'] as $loc) {
	echo "\tgtfs.loadGTFS('$loc')\n";
}
?>
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
			gtfs.map.fitBounds(gtfs.extremes)
		}
	}).on('unfocus', function(e) {
		var $s = $(e.target).closest('section[data-route-id]').removeClass('active'),
			route = $s.data('route-id'),
			shape = gtfs.routes[route].shape
		if (!shape || !gtfs.poly[shape]) return
		gtfs.poly[shape].Polyline.setOptions({
			strokeWeight: gtfs.poly[shape].weight,
			opacity: gtfs.poly[i].opacity || .6,
			zIndex: 0
		})
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
				gtfs.stops[id].Marker.setLabel(gtfs.routes[route_id].label || '')
				gtfs.stops[id].Marker.setVisible(true)
			}
		}
	})
})
gtfs.hideStops = function() {
	for (var id in gtfs.stops) {
		gtfs.stops[id].Marker.setVisible(false)
	}
}
