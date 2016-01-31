<?php session_start(); ?>
window.gtfs = gtfs || {}
gtfs.extremes = { lat:{min:180,max:0},lon:{min:180,max:0} }
gtfs.routes = {}
gtfs.poly = {}
gtfs.setShapeRoute = function(shape, route) {
	gtfs.poly[shape] = gtfs.poly[shape] || {}
	if (gtfs.routes[route]) {
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
// Load and Draw GTFS Shapes
gtfs.loadShapes = function(url) {
	$.ajax({
		url:'/gtfs/' + url + '/routes.txt',
		dateType:'text',
		success:function(data){
			data = data.split("\n")
			data.shift()
			data.forEach(function(r){
				r = r.split(',')
				if (r[0] == '') return
				gtfs.routes[r[0]] = gtfs.routes[r[0]] || {}
				gtfs.routes[r[0]].color = '#' + r[6]
				gtfs.routes[r[0]].name = r[3]
				gtfs.routes[r[0]].type = r[4]
				gtfs.routes[r[0]].num = r[2]
			})
		}
	})
	$.ajax({
		url:'/gtfs/' + url + '/trips.txt',
		dateType:'text',
		success:function(data){
			data = data.split("\n")
			data.shift()
			data.forEach(function(r){
				r = r.split(',')
				if (r[0] == '') return
				route = r[0]
				shape = r[7]
				if (r[7] != '') gtfs.setShapeRoute(shape, route)
			})
		}
	})
	$.ajax({
		url:'/gtfs/' + url + '/shapes.txt',
		dateType:'text',
		success:function(data){
			var l
			data = data.split("\n")
			data.shift()
			data.forEach(function(r){
				r = r.split(',')
				if (r[0] == '') return
				gtfs.poly[r[0]] = gtfs.poly[r[0]] || {}
				gtfs.poly[r[0]].path = gtfs.poly[r[0]].path || []
				gtfs.poly[r[0]].path.push({
					lat: Number.parseFloat(r[1]),
					lng: Number.parseFloat(r[2])
				})
				gtfs.extremes.lat.min = Math.min(gtfs.extremes.lat.min, Number.parseFloat(r[1]))
				gtfs.extremes.lon.min = Math.min(gtfs.extremes.lon.min, Number.parseFloat(r[2]))
				gtfs.extremes.lat.max = Math.max(gtfs.extremes.lat.max, Number.parseFloat(r[1]))
				gtfs.extremes.lon.max = Math.max(gtfs.extremes.lon.max, Number.parseFloat(r[2]))
			})
			gtfs.map.center = new google.maps.LatLng({
				lat: (gtfs.extremes.lat.min + gtfs.extremes.lat.max) / 2,
				lng: (gtfs.extremes.lon.min + gtfs.extremes.lon.max) / 2
			})
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
}
$('script[src*="maps.google.com/maps/api/js"]').load(function(){
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
		zoom: 12
	})
<?php
foreach ($_SESSION['gtfs_locs'] as $loc) {
	echo "\tgtfs.loadShapes('$loc')\n";
}
?>
})
