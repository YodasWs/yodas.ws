<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("site.php");
require_once("components/world_map/world_map.php");
$worldxml = simplexml_load_file('world.xml');
define('bubbleNumPic',0);

// 15 Dec 2008
// Creates the text within the Info Window for given Google Maps Marker
function loadGMarker($xml, $i) {
	// Upgraded 8 Oct 2010 to HTML output in return $txt
//	$locale = ($xml->locale[$i]['area']) ? $xml->locale[$i]['area'] : $xml->locale[$i]->google; // Added Area Markers, 26 Jan 2009
	$locale = $xml->locale[$i]->google;
	// Add Click Event to display Info Window, 15 Jul 2008
	$txt = "<div class=\"gMarker\">";
	if (count($xml->locale[$i]->date) == 1) { // If one date for locale, link directly to it, 1 Oct 2008
		$temp = BlogSite::getDate($xml->locale[$i]->date);
		$href = ($temp['file']) ? $temp['path'] : $locale;
	} else $href = $locale; // 1 Oct 2008
#	$txt .= "<a class=\"map\" href=\"http://yodas.ws/$href\">$locale</a>";
	$txt .= "<a>$locale</a>";
	if (count($xml->locale[$i]->date) > 1) { // If multiple dates, offer link to latest, 11 Dec 2008
		$dates = array();
		foreach ($xml->locale[$i]->date as $date) {
			$date = (string) $date;
			$date = explode(' ', $date);
			$dates[] = "$date[2] " . BlogSite::str_num( BlogSite::int_mon($date[1])) . " " . (((int) $date[0] < 10 and strpos((string) $date[0], '0') !== 0) ? '0' : '') . "$date[0]";
		}
		rsort($dates);
		$date = explode(' ', $dates[0]);
#		$txt .= "<br/><small>Last Visit: <a class=\"map\" href=\"http://yodas.ws/{$date[0]}/" . BlogSite::str_mon($date[1]) . "/{$date[2]}\">{$date[2]} " . BlogSite::str_mon($date[1]) . " {$date[0]}</a></small>";
	}
	// Display Pics in Info Bubbles, 29 Sep 2008
	if (count($xml->locale[$i]->img) > 0 and count($xml->locale[$i]->img) <= bubbleNumPic) {
		foreach ($xml->locale[$i]->img as $img) $txt .= "<img src=\"http://yodas.ws/{$img['src']}\" height=\"100\" alt=\"$locale\" />";
	} else if (count($xml->locale[$i]->img) > bubbleNumPic) {
		$k = randArray(bubbleNumPic, 0, count($xml->locale[$i]->img)-1);
		for ($j=0; $j<bubbleNumPic; $j++) {
			$num = $k[$j];
			$txt .= "<img src=\"http://yodas.ws/{$xml->locale[$i]->img[$num]['src']}\" height=\"100\" alt=\"$locale\" />";
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

echo <<<startWorldMap
var map;
// Load Google Maps JavaScript API
var mapPan;
win = new Array();
markers = new Array();
function loadWorldMap() {
	if (!document.getElementById("worldmap")) return false;
	geocode = new google.maps.Geocoder();
	ops = { zoom: 2, center: new google.maps.LatLng(47, 15), mapTypeId: google.maps.MapTypeId.ROADMAP,
		disableDefaultUI: true, mapTypeControl: false, scaleControl: false, zoomControl: true, panControl: true,
		minZoom: 1, maxZoom: 10
	};
	if ($('#worldmap').width() < 670) ops.zoom--
	map = new google.maps.Map(document.getElementById("worldmap"), ops);
	$('#worldmap').slideDown('slow');\n
startWorldMap;
$areas = array();
for ($i=0; $i<count($worldxml->locale); $i++) { // Load Area Zoom Levels, 26 Jan 2009
	$locale = $worldxml->locale[$i];
	if (!$locale['area']) continue;
	$area = $locale['area'];
	$areas["$area"] = ($locale['zoom']) ? $locale['zoom'] : 7;
}
echo "\ti=0;";
for ($i=0; $i<count($worldxml->locale); $i++) { // Load Locale Markers
	if (empty($worldxml->locale[$i]->google)) continue;
	$locale = $worldxml->locale[$i]->google;
	$locale = preg_replace("|'|", "\\'", $locale);
	$win = loadGMarker($worldxml, $i);
	if ($worldxml->locale[$i]['home']) $zed = 500;
	else if ($worldxml->locale[$i]['zed']) $zed = $worldxml->locale[$i]['zed'];
	else $zed = 400;
	if (!$worldxml->locale[$i]['lat'] or !$worldxml->locale[$i]['lng']) {
		echo <<<gMap
markers[$i]=false; geocode.geocode({'address': "$locale"}, function(point, status) {
if (status == google.maps.GeocoderStatus.OK) try {
	markers[$i] = new google.maps.Marker({ position: point[0].geometry.location, map: map, title: "$locale", zIndex: $zed });
	win[$i] = new google.maps.InfoWindow({content: '$win'});
	google.maps.event.addListener(markers[$i], 'click', function() {
		win.forEach(function(e){e.close()})
		win[$i].open(map, markers[$i]);
	});
	document.getElementById('hiddenLatLng').innerHTML += "$locale: " + point[0].geometry.location.lat() + ', ' + point[0].geometry.location.lng() + "<br/>\\n";
	markers[$i].setMap(map);
} catch (e) {
	markers[$i] = false;
} });\n
gMap;
	} else { // Use LatLng coords if available, 9 Oct 2010
		echo <<<gMap
	pnt = new google.maps.LatLng({$worldxml->locale[$i]['lat']}, {$worldxml->locale[$i]['lng']});
	markers[$i] = new google.maps.Marker({ position: pnt, map: map, title: "$locale", zIndex: $zed });
	win[$i] = new google.maps.InfoWindow({content: '$win'});
	google.maps.event.addListener(markers[$i], 'click', function() {
		win.forEach(function(e){e.close()})
		win[$i].open(map, markers[$i]);
	});
	markers[$i].setMap(map);\n
gMap;
	}
}
$numMarkers = count($worldxml->locale);
echo <<<EndWorldMap
}
function panWorldMap() { // 21 Dec 2011
	var marker
	do {
		marker = Math.floor(Math.random() * $numMarkers);
	} while(!markers[marker].getPosition)
	map.panTo(markers[marker].getPosition());
	map.setZoom(6);
	$(win).each(function(i, ele) { ele.close(); });
	win[marker].open(map, markers[marker]);
	mapPan = setTimeout(panWorldMap, 20000);
}

EndWorldMap;

// Explore Foursquare Venues
#$url = "{$fsq['venue_url']}explore?v=20120112&client_id={$fsq['client_id']}&client_secret={$fsq['secret']}";
#$venue = @file_get_contents($url);

echo <<<AutoPan
$('script[src*="maps.google.com/maps/api/js"]').load(function(){
loadWorldMap();
mapPan = setTimeout(panWorldMap, 30000);
$('#worldmap').mouseenter(function() {
	clearTimeout(mapPan);
}).mouseleave(function() {
	mapPan = setTimeout(panWorldMap, 10000);
});
})
AutoPan;
?>
