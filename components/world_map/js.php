<?php
header('Content-type: text/javascript');
echo <<<startWorldMap
var map;
// Load Google Maps JavaScript API
var mapPan;
win = new Array();
markers = new Array();
function loadWorldMap() {
	if (!document.getElementById("worldmap")) return false;
	geocode = new google.maps.Geocoder();
	ops = { zoom: 2, center: new google.maps.LatLng(35, 7), mapTypeId: google.maps.MapTypeId.ROADMAP,
		disableDefaultUI: true, mapTypeControl: false, scaleControl: false, zoomControl: true, panControl: true,
		minZoom: 1, maxZoom: 10
	};
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
	$locale = ($worldxml->locale[$i]->google) ? $worldxml->locale[$i]->google : $worldxml->locale[$i]['area'];
	$locale = preg_replace("|'|", "\\'", $locale);
	$win = loadGMarker($worldxml, $i);
	if (!$worldxml->locale[$i]['lat'] or !$worldxml->locale[$i]['lng']) {
		if ($worldxml->locale[$i]['home']) $zed = 500;
		else if ($worldxml->locale[$i]['zed']) $zed = $worldxml->locale[$i]['zed'];
		echo <<<gMap
markers[$i]=false; geocode.geocode({'address': "$locale"}, function(point, status) {
if (status == google.maps.GeocoderStatus.OK) try {
	markers[$i] = new google.maps.Marker({ position: point[0].geometry.location, map: map, title: "$locale", zIndex: $zed });
	win[$i] = new google.maps.InfoWindow({content: '$win'});
	google.maps.event.addListener(markers[$i], 'click', function() {
		$(win).each(function(i, ele) { ele.close(); });
		win[$i].open(map, markers[$i]);
	});
	document.getElementById('hiddenLatLng').innerHTML += "$locale: " + point[0].geometry.location.lat() + ', ' + point[0].geometry.location.lng() + "<br/>\\n";
	markers[$i].setMap(map);
} catch (e) {
	markers[$i] = false;
} });\n
gMap;
	} else { // Use LatLng coords if available, 9 Oct 2010
		if ($worldxml->locale[$i]['home']) $zed = 500;
		else if ($worldxml->locale[$i]['zed']) $zed = $worldxml->locale[$i]['zed'];
		echo <<<gMap
	pnt = new google.maps.LatLng({$worldxml->locale[$i]['lat']}, {$worldxml->locale[$i]['lng']});
	markers[$i] = new google.maps.Marker({ position: pnt, map: map, title: "$locale", zIndex: $zed });
	win[$i] = new google.maps.InfoWindow({content: '$win'});
	google.maps.event.addListener(markers[$i], 'click', function() {
		$(win).each(function(i, ele) { ele.close(); });
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
	var marker = Math.floor(Math.random() * $numMarkers);
	map.panTo(markers[marker].getPosition());
	map.setZoom(6);
	$(win).each(function(i, ele) { ele.close(); });
	win[marker].open(map, markers[marker]);
	mapPan = setTimeout('panWorldMap();', 20000);
}
EndWorldMap;

// Explore Foursquare Venues
#$url = "{$fsq['venue_url']}explore?v=20120112&client_id={$fsq['client_id']}&client_secret={$fsq['secret']}";
#$venue = @file_get_contents($url);

echo <<<AutoPan
loadWorldMap();
mapPan = setTimeout('panWorldMap();', 30000);
$('#worldmap').mouseenter(function() {
	clearTimeout(mapPan);
}).mouseleave(function() {
	mapPan = setTimeout('panWorldMap();', 10000);
});
AutoPan;
?>
