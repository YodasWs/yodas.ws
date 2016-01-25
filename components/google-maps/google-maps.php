<?php
require_once("components/component.php");
class GoogleMaps implements Component {

	public function html() {
		global $blog;
		require_once("components/google-maps/html.php");
	}

	public static function url($lat, $lng, $z, $w=150, $h=null) {
		if (empty($h)) $h = $w;
		return "http://maps.google.com/maps/api/staticmap?markers={$lat},{$lng}&size={$w}x{$h}&format=png32&zoom=$z";
	}
}
