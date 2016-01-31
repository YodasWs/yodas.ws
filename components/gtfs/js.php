<?php session_start(); ?>
window.gtfs = gtfs || {}
gtfs.extremes = { lat:{min:180,max:0},lon:{min:180,max:0} }
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
			gtfs.poly[shape].weight = 3
			gtfs.poly[shape].label = '🚉'
			break;
		case 3: // Bus
			gtfs.poly[shape].weight = 1
			break;
		case 5: // Cable Car
			gtfs.poly[shape].weight = 2
			break;
		case 6: // Gondola
			gtfs.poly[shape].weight = 2
			break;
		}
	}
	if (gtfs.poly[shape].Polyline) {
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
// Load and Draw GTFS Shapes
gtfs.loadShapes = function(url) {
	$.ajax({
		url:'/gtfs/' + url + '/routes.txt',
		dateType:'text',
		success:function(data){
			data = data.split("\n")
			var head = gtfs.parseHeader(data.shift())
			data.forEach(function(r){
				r = r.split(',')
				if (r[head.route_id] == '') return
				// Save Pertinent Route Data
				gtfs.routes[r[head.route_id]] = gtfs.routes[r[head.route_id]] || {}
				gtfs.routes[r[head.route_id]].txtColor = '#' + (r[head.route_text_color] || '000000')
				gtfs.routes[r[head.route_id]].color = '#' + (r[head.route_color] || 'ffffff')
				gtfs.routes[r[head.route_id]].name = r[head.route_long_name]
				gtfs.routes[r[head.route_id]].type = r[head.route_type]
				gtfs.routes[r[head.route_id]].num = r[head.route_short_name]
				gtfs.routes[r[head.route_id]].stops = []
			})
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
				if (r[head.route_id] == '') return
				route = r[head.route_id]
				shape = r[head.shape_id]
				// Associate Shape to Route
				if (shape != '') gtfs.setShapeRoute(shape, route)
				// Associate Trip to Route
				gtfs.tripRoute[r[head.trip_id]] = route
			})
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
				if (r[head.shape_id] == '') return
				// Save Shape Point
				gtfs.poly[r[head.shape_id]] = gtfs.poly[r[head.shape_id]] || {}
				gtfs.poly[r[head.shape_id]].path = gtfs.poly[r[head.shape_id]].path || []
				gtfs.poly[r[head.shape_id]].path.push({
					lat: Number.parseFloat(r[head.shape_pt_lat]),
					lng: Number.parseFloat(r[head.shape_pt_lon])
				})
				// Save Extreme Points to calculate Map center
				gtfs.extremes.lat.min = Math.min(gtfs.extremes.lat.min, Number.parseFloat(r[head.shape_pt_lat]))
				gtfs.extremes.lon.min = Math.min(gtfs.extremes.lon.min, Number.parseFloat(r[head.shape_pt_lon]))
				gtfs.extremes.lat.max = Math.max(gtfs.extremes.lat.max, Number.parseFloat(r[head.shape_pt_lat]))
				gtfs.extremes.lon.max = Math.max(gtfs.extremes.lon.max, Number.parseFloat(r[head.shape_pt_lon]))
			})
			// Set Map Center
			gtfs.map.center = new google.maps.LatLng({
				lat: (gtfs.extremes.lat.min + gtfs.extremes.lat.max) / 2,
				lng: (gtfs.extremes.lon.min + gtfs.extremes.lon.max) / 2
			})
			// Paste Shapes on Map
			for (var i in gtfs.poly) {
				gtfs.poly[i].Polyline = new google.maps.Polyline({
					path: gtfs.poly[i].path,
					geodesic: true,
					strokeColor: gtfs.poly[i].color || '#008800',
					strokeWeight: gtfs.poly[i].weight || 4,
					strokeOpacity: 1,
					clickable: true
				})
				gtfs.poly[i].Polyline.setMap(gtfs.map)
			}
		}
	})
	$.ajax({
		url:'/gtfs/' + url + '/stops.txt',
		success:function(data){
			data = data.split("\n")
			var head = gtfs.parseHeader(data.shift())
			data.forEach(function(r){
				r = r.split(',')
				if (r[head.stop_id] == '') return
				gtfs.stops[r[head.stop_id]] = {
					lat: Number.parseFloat(r[head.stop_lat]),
					lng: Number.parseFloat(r[head.stop_lon]),
					name: r[head.stop_name]
				}
			})
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
				gtfs.routes[route_id].stops.push(gtfs.stops[stop_id])
			})
			// Build Lists of Route Stations
			for (i in gtfs.routes) {
				var r = gtfs.routes[i], $l = $('<ol>')
					$t = $('<section class="route" data-route-id="' + i + '">')
				$t.append('<h1 style="background:' + r.color + ';color:' + r.txtColor + '">' + (r.num ? r.num + ' ' : '') + r.name)
				r.stops.forEach(function(s, id){
					$l.append('<li data-station-id="' + id + '">' + s.name)
				})
				$('main').append($t.append($l))
			}
		}
	})
}
$('script[src*="maps.google.com/maps/api/js"]').load(function(){
	var zoom = $('#gtfs').width() < 550 ? 11 : 12
	// Load Google Maps
	gtfs.map = new google.maps.Map(document.getElementById('gtfs'), {
		center: new google.maps.LatLng(35.22, 139.07),
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		keyboardShortcuts: true,
		disableDefaultUI: true,
		scaleControl: true,
		scrollWheel: true,
		zoomControl: true,
		maxZoom: 17,
		minZoom: 10,
		zoom: zoom
	})
<?php
foreach ($_SESSION['gtfs_locs'] as $loc) {
	echo "\tgtfs.loadShapes('$loc')\n";
}
?>
	// Highlight Routes
	$('main').on('click', 'section.route', function(e) {
		var isOpen = $(e.target).closest('section').is('active')
		$('section.route.active').trigger('unfocus')
		if (!isOpen) {
			var $s = $(e.target).closest('section').addClass('active')
				route = $s.data('route-id'),
				shape = gtfs.routes[route].shape
			if (!shape || !gtfs.poly[shape]) return
			gtfs.poly[shape].Polyline.setOptions({
				strokeWeight: 4,
				zIndex: 1
			})
		}
	}).on('unfocus', function(e) {
		var $s = $(e.target).closest('section').removeClass('active'),
			route = $s.data('route-id'),
			shape = gtfs.routes[route].shape
		if (!shape || !gtfs.poly[shape]) return
		gtfs.poly[shape].Polyline.setOptions({
			strokeWeight: gtfs.poly[shape].weight,
			zIndex: 0
		})
	})
})
