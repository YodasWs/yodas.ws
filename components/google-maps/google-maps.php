<?php
require_once("components/component.php");
class GoogleMaps implements Component {

	private $height = 64;
	private $width = 64;

	public function html() {
		global $blog;
		require_once("components/google-maps/html.php");
	}

	public static function url($lat, $lng, $z, $w=150, $h=null) {
		if (empty($h)) $h = $w;
		return "http://maps.google.com/maps/api/staticmap?markers={$lat},{$lng}&size={$w}x{$h}&format=png32&scale=2&zoom=$z";
	}

	public function __set($var, $val) {
		switch ($var) {
		case 'center':
			if (empty($val)) throw Exception("Cannot accept empty Center");
			if (is_array($val)) {
			} else if (is_string($val)) {
			}
			break;
		case 'size':
			if (is_int($val) and $val > 0) {
				$this->height = $val;
				$this->width = $val;
			} else if (is_array($val)) {
				if (empty($val[0])) {
					if (!empty($val['w'])) $val[0] = $val['w'];
					else if (!empty($val['width'])) $val[0] = $val['width'];
				}
				if (empty($val[1])) {
					if (!empty($val['h'])) $val[1] = $val['h'];
					else if (!empty($val['height'])) $val[1] = $val['height'];
					else if (!empty($val['w'])) $val[1] = $val['w'];
					else if (!empty($val['width'])) $val[1] = $val['width'];
					else if (!empty($val[0])) $val[1] = $val[0];
				}
				if (!empty($val[0]) and !empty($val[1])) {
					foreach ([0,1] => $i) {
						if (!is_int($val[$i]) or $val < 0) throw Exception("Size must be positive integer");
					}
					$this->height = $val[1];
					$this->width = $val[0];
				}
			} else {
				throw Exception("Invalid Size value");
			}
			break;
		}
	}
}
