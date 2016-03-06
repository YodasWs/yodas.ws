<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("site.php");
require_once("components/world_map/world_map.php");
$fmt = filemtime('world2.xml');
$worldmap = json_decode(json_encode(simplexml_load_file('world2.xml')), true);
#print_r($worldmap['locale']); exit;

if (
	strpos($_SERVER['HTTP_HOST'], 'test') !== 0 and
	time() - $fmt < 60 * 24 * 60 * 60 and
	!empty($_SERVER['HTTP_IF_NONE_MATCH']) and
	$_SERVER['HTTP_IF_NONE_MATCH'] === BlogSite::etag($fmt)
) {
	header("HTTP/1.1 304 Not Modified");
	exit;
}
date_default_timezone_set('America/Detroit');
header("Last-Modified: " . date('r'));
header("ETag: " . BlogSite::etag(time()));
?>
yodasws = window.yodasws || {}
yodasws.worldMap = {
	geocoder: {},
	infoWindows: [],
	map: {},
	markers: [],
	options: {},
	panTimer: 0,
	oldCenter: false,
	panRandom: function() {
		var marker,
			end = (new Date()).getTime() + 1000 * 60
		if (!yodasws.worldMap.markers) return
		do {
			marker = Math.floor(Math.random() * <?=count($worldmap['locale'])?>)
			if ((new Date()).getTime() > end) return
		} while (!yodasws.worldMap.markers[marker] || !yodasws.worldMap.markers[marker].getPosition)
		yodasws.worldMap.map.panTo(yodasws.worldMap.markers[marker].getPosition())
		yodasws.worldMap.map.setZoom(6)
		yodasws.worldMap.infoWindows.forEach(function(e) { e.close() })
		yodasws.worldMap.infoWindows[marker].open(yodasws.worldMap.map, yodasws.worldMap.markers[marker])
		yodasws.worldMap.panTimer = setTimeout(yodasws.worldMap.panRandom, 20000)
	}
}

$('script[src*="maps.google.com/maps/api/js"]').load(function(){
	var c = 0,
		$wm = $('#worldmap')
		fnScroll = function() {
			// Scroll into view
			$('html,body').animate({
				scrollTop: $wm.offset().top - yodasws.stickyHeight()
			}, 500, 'swing')
		}
	if (!document.getElementById("worldmap")) return false;
	yodasws.worldMap.geocoder = new google.maps.Geocoder();
	yodasws.worldMap.options = {
		zoom: 2,
		center: new google.maps.LatLng(47, 15),
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		keyboardShortcuts: true,
		disableDefaultUI: true,
		mapTypeControl: false,
		scaleControl: false,
		scrollWheel: true,
		zoomControl: true,
		panControl: true,
		minZoom: 1,
		maxZoom: 10
	};
	if ($wm.width() < 670) yodasws.worldMap.options.zoom--
	yodasws.worldMap.map = new google.maps.Map(document.getElementById("worldmap"), yodasws.worldMap.options)
	yodasws.worldMap.panTimer = setTimeout(yodasws.worldMap.panRandom, 30000)
	$wm.on('mouseenter', function() {
		clearTimeout(yodasws.worldMap.panTimer)
	}).on('mouseleave', function() {
		yodasws.worldMap.panTimer = setTimeout(yodasws.worldMap.panRandom, 10000)
	}).on('click', function() {
		$t = $(this)
		if ($t.is('.expanded')) return
		yodasws.worldMap.oldCenter = yodasws.worldMap.map.getCenter()
		fnScroll()
		$t.addClass('expanded')
		setTimeout(function(){
			$(window).trigger('resize')
		}, 500)
	})
	// Collapse World Map on Click
	$(document).on('click', function(e) {
		if (!$(e.target).closest('#worldmap').length || $(e.target).is('#worldmap + *')) {
			yodasws.worldMap.oldCenter = yodasws.worldMap.map.getCenter()
			$wm.removeClass('expanded')
			setTimeout(function(){
				$(window).trigger('resize')
			}, 500)
		}
	})
	$(window).on('resize', function() {
		if (c) clearTimeout(c)
		c = setTimeout(function(m) {
			google.maps.event.trigger(m, 'resize')
			if (yodasws.worldMap.oldCenter) {
				yodasws.worldMap.map.setCenter(yodasws.worldMap.oldCenter)
				yodasws.worldMap.oldCenter = false
			}
			c = 0
		}, 100, yodasws.worldMap.map)
	})

<?php

// 15 Dec 2008
// Creates the text within the Info Window for given Google Maps Marker
function loadGMarker($xml) {
	// Upgraded 8 Oct 2010 to HTML output in return $txt
	$locale = $xml['name'];
	// Add Click Event to display Info Window, 15 Jul 2008
	$txt = "<div class=\"gMarker\">";
	$href = '/' . $xml['@attributes']['cc'] . '/' . BlogSite::urlencode($locale);
	$txt .= "<a href=\"{$href}\">$locale</a>";
	if (count($xml->date) > 1) { // If multiple dates, offer link to latest, 11 Dec 2008
		$dates = array();
		foreach ($xml->date as $date) {
			$date = (string) $date;
			$date = explode(' ', $date);
			$dates[] = "$date[2] " . BlogSite::str_num( BlogSite::int_mon($date[1])) . " " . (((int) $date[0] < 10 and strpos((string) $date[0], '0') !== 0) ? '0' : '') . "$date[0]";
		}
		rsort($dates);
		$date = explode(' ', $dates[0]);
#		$txt .= "<br/><small>Last Visit: <a class=\"map\" href=\"http://yodas.ws/{$date[0]}/" . BlogSite::str_mon($date[1]) . "/{$date[2]}\">{$date[2]} " . BlogSite::str_mon($date[1]) . " {$date[0]}</a></small>";
	}
	// Display Pics in Info Bubbles, 29 Sep 2008
	if (count($xml['img']) > 0 and count($xml['img']) <= 0) {
		foreach ($xml['img'] as $img) $txt .= "<img src=\"http://yodas.ws/{$img['src']}\" height=\"100\" alt=\"$locale\" />";
	} else if (count($xml['img']) > 0) {
		$k = randArray(0, 0, count($xml['img'])-1);
		for ($j=0; $j<0; $j++) {
			$num = $k[$j];
			$txt .= "<img src=\"http://yodas.ws/{$xml['img'][$num]['src']}\" height=\"100\" alt=\"$locale\" />";
		}
	}
	$txt .= "</div>";
	$txt = preg_replace("/'/", "\\'", $txt);
	return $txt;
}
function randArray($size, $min=0, $max=100) {
	$list = array();
	for ($i=0; $i<$size; $i++) $list[] = rand($min, $max);
	for ($i=1; $i<$size; $i++) for ($j=0; $j<$i; $j++) while ($list[$i] == $list[$j]) $list[$i] = rand($min, $max);
	return $list;
}

echo "var i=0;";
for ($i=0; $i<count($worldmap['locale']); $i++) { // Load Locale Markers
	if (empty($worldmap['locale'][$i]['name'])) continue;
	$locale = $worldmap['locale'][$i]['name'];
	$locale = preg_replace("|'|", "\\'", $locale);
	$win = loadGMarker($worldmap['locale'][$i]);
	if ($worldmap['locale'][$i]['home']) $zed = 500;
	else if ($worldmap['locale'][$i]['@attributes']['zed']) $zed = $worldmap['locale'][$i]['@attributes']['zed'];
	else $zed = 400;
	if (!$worldmap['locale'][$i]['@attributes']['lat'] or !$worldmap['locale'][$i]['@attributes']['lng']) {
		echo <<<gMap
yodasws.worldMap.markers[$i]=false
yodasws.worldMap.geocoder.geocode({'address': "$locale"}, function(point, status) {
if (status == google.maps.GeocoderStatus.OK) try {
	yodasws.worldMap.markers[$i] = new google.maps.Marker({ position: point[0].geometry.location, map: yodasws.worldMap.map, title: "$locale", zIndex: $zed });
	yodasws.worldMap.infoWindows[$i] = new google.maps.InfoWindow({content: '$win'});
	google.maps.event.addListener(yodasws.worldMap.markers[$i], 'click', function() {
		yodasws.worldMap.infoWindows.forEach(function(e){e.close()})
		yodasws.worldMap.infoWindows[$i].open(yodasws.worldMap.map, yodasws.worldMap.markers[$i]);
	});
	document.getElementById('hiddenLatLng').innerHTML += "$locale: " + point[0].geometry.location.lat() + ', ' + point[0].geometry.location.lng() + "<br/>";
	yodasws.worldMap.markers[$i].setMap(yodasws.worldMap.map);
} catch (e) {
	yodasws.worldMap.markers[$i] = false;
} });
gMap;
	} else { // Use LatLng coords if available, 9 Oct 2010
		echo <<<gMap
\n	pnt = new google.maps.LatLng({$worldmap['locale'][$i]['@attributes']['lat']}, {$worldmap['locale'][$i]['@attributes']['lng']});
	yodasws.worldMap.markers[$i] = new google.maps.Marker({ position: pnt, map: yodasws.worldMap.map, title: "$locale", zIndex: $zed });
	yodasws.worldMap.infoWindows[$i] = new google.maps.InfoWindow({content: '$win'});
	google.maps.event.addListener(yodasws.worldMap.markers[$i], 'click', function() {
		yodasws.worldMap.infoWindows.forEach(function(e){e.close()})
		yodasws.worldMap.infoWindows[$i].open(yodasws.worldMap.map, yodasws.worldMap.markers[$i]);
	});
	yodasws.worldMap.markers[$i].setMap(yodasws.worldMap.map);
gMap;
	}
}
echo <<<EndWorldMap
})
EndWorldMap;
?>
