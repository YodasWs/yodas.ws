<?php
chdir("{$_SERVER['DOCUMENT_ROOT']}/{$_SERVER['SITE_DIR']}");
session_start();
class BlogSite {
	const Site_Title = 'YodasWs';
	private $title = '';
	private $date = array(
		'year' => false,
		'mon' => false,
		'day' => false,
	);
	private $dirLayouts = "layouts/";
	private $fileHeader = "header.php";
	private $fileFooter = "footer.php";
	private $page_type = 'WebPage';
	private $javascript = array();
	private $page_wrap = true;
	private $gmaps = array();
	private $world_map;
	private $lang;

	public function flush() {
		ob_flush();
	}

	public function __construct($wrap=true) {
		ob_start();

		$this->page_wrap = $wrap;
		$this->date = self::getDate($_SERVER['REQUEST_URI']);

		// Set Preferred Language
		$this->lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		foreach ($this->lang as &$l) {
			if (strstr($l, ';')) $l = substr($l, 0, strpos($l, ';'));
			$l = substr($l, 0, 2);
		}
		if (empty($this->lang)) $this->lang = array('en');
	}

	public function date_as_dir($date = null) {
		if (empty($date)) $date = $this->date;
		if (is_string($date)) $date = self::getDate($date);
		return "{$date['year']}/" .  self::str_num($date['mon']) . '/' . self::str_num($date['day']);
	}

	public static function date_as_url($date) {
		if (is_string($date)) $date = self::getDate($date);
		return "/{$date['year']}/{$date['Mon']}/" . self::str_num($date['day']) . '/';
	}

	public static function getXMLFile($file=null, $lang=null) {
		global $blog;
		$xml = array();
		if (empty($file)) $file = $blog->date_as_dir();
		if (empty($file)) return array();
		if (empty($lang) and !empty($blog)) $lang = $blog->lang;
		if (empty($lang)) $lang = array('en');
		if (!is_array($lang)) $lang = array($lang);
		$lang = array_unique($lang);
		foreach ($lang as $l) {
			if (!file_exists("{$file}.{$l}.xml")) $l = substr($l, 0, 2);
			if (file_exists("{$file}.{$l}.xml")) {
				$xml = array_merge_recursive($xml, json_decode(json_encode(simplexml_load_file("{$file}.{$l}.xml", null, LIBXML_NOCDATA)), true));
				$xml = array_unique($xml);
			}
		}
		return $xml;
	}

	public static function getDate($str) {
		$date = array();
		$arr = explode('/', trim($str, '/'));
		if (preg_match("'^\d{4}$'", $arr[0])) {
			$date['year'] = $arr[0];
			$date['dir'] = $date['year'];
			if (!empty($arr[1]) and self::is_mon($arr[1])) {
				$date['mon'] = self::int_mon($arr[1]);
				$date['Mon'] = self::str_mon($arr[1]);
				$date['dir'] .= "/" . self::str_num($date['mon']);
				if (!empty($arr[2]) and (is_int($arr[2]) or is_float($arr[2]) or preg_match("'^\d+'", $arr[2]))) {
					if (checkdate($date['mon'], (int) $arr[2], $date['year'])) {
						$date['day'] = (int) $arr[2];
					}
				}
			}
		}
		return $date;
	}

	public static function date_toString($date) {
		if (is_string($date)) $date = self::getDate($date);
		return trim("{$date['day']} {$date['Mon']} {$date['year']}");
	}

	public static function int_mon($str) {
		if (!self::is_mon($str)) return false;
		if (is_numeric($str)) $str = (int) $str;
		if (is_int($str)) {
			if ($str >= 1 and $str <= 12) return $str;
			return false;
		}
		switch (strtolower(substr($str, 0, 3))) {
			case 'jan': return 1;
			case 'feb': return 2;
			case 'mar': return 3;
			case 'apr': return 4;
			case 'may': return 5;
			case 'jun': return 6;
			case 'jul': return 7;
			case 'aug': return 8;
			case 'sep': return 9;
			case 'oct': return 10;
			case 'nov': return 11;
			case 'dec': return 12;
		}
	}

	public static function is_mon($str) {
		if (is_numeric($str)) $str = (int) $str;
		if (is_int($str)) {
			return ($str >= 1 and $str <= 12);
		} else if (!is_string($str)) {
			return false;
		}
		if (in_array(strtolower(substr($str,0,3)), array(
			'jan','feb','mar','apr','may','jun',
			'jul','aug','sep','oct','nov','dec'
		))) return true;
		return false;
	}

	public static function str_mon($mon) {
		if (in_array(strtolower(substr($mon,0,3)), array(
			'jan','feb','mar','apr','may','jun',
			'jul','aug','sep','oct','nov','dec'
		))) return strtoupper(substr($mon,0,1)) . strtolower(substr($mon,1,2));
		$mon = (int) $mon;
		switch ($mon) {
			case 1: return "Jan";
			case 2: return "Feb";
			case 3: return "Mar";
			case 4: return "Apr";
			case 5: return "May";
			case 6: return "Jun";
			case 7: return "Jul";
			case 8: return "Aug";
			case 9: return "Sep";
			case 10: return "Oct";
			case 11: return "Nov";
			case 12: return "Dec";
		} return false;
	}

	public static function str_num($mon) {
		if (!is_numeric($mon)) return false;
		$mon = (int) $mon;
		$mon = "$mon";
		return str_pad($mon, 2, '0', STR_PAD_LEFT);
	}

	public static function urlencode($str) {
		if (is_string($str)) {
			$str = explode('/', ucwords($str));
		} else if (!is_array($str)) {
			throw new Exception(__FUNCTION__ . " only accepts Strings and Arrays");
		}
		// If Date, Convert Month String
		if (is_numeric($str[0]) and is_numeric($str[1]) and is_numeric($str[2])) {
			$str[1] = self::str_mon($str[1]);
		}
		if (strlen($str[0]) === 2) {
			// This is a country code, keep in lower case
			$str[0] = strtolower($str[0]);
		}
		$str = array_map('urlencode', $str);
		$str = implode('/', $str);
		return '/' . preg_replace("'%[a-f0-9]{2}'i", '', $str) . '/';
	}

	public function getWorldMap() {
		require_once("components/world_map/world_map.php");
		$this->world_map = WorldMap::singleton();
		return $this->world_map;
	}

	public function loc($loc) {
		if (empty($this->world_map)) $this->getWorldMap();
		$xml = $this->world_map->getLocation($loc);
		if (gettype($xml) != 'array') return false;
		return $xml;
	}

	public static function etag($time) {
		if (!$time) $time = new Date();
		return date("YMdHiT", $time);
	}

	public function __destruct() {
		$content = ob_get_clean();
		global $blog;

		if ($this->page_wrap) include_once($this->dirLayouts.$this->fileHeader);
		echo $content;
		if ($this->page_wrap) include_once($this->dirLayouts.$this->fileFooter);
	}

	public function __set($var, $val) {
		if (in_array($var, array(
			'title'
		))) {
			$this->$var = $val;
			return;
		}
		switch ($var) {
		case 'dirLayouts':
			// Does Directory Exist?
			if (is_dir($val))
				$this->$var = $val;
			return;
		case 'fileHeader':
		case 'fileFooter':
			// Does File Exist?
			if (is_file($this->dirLayouts.$val))
				$this->$var = $val;
			return;
		case 'page_type':
			if (in_array($val, array(
				'WebPage','ImageGallery','AboutPage','ItemPage','CollectionPage','SearchResultsPage'
			))) $this->$var = $val;
			return;
		case 'javascript':
			if (in_array($val, $this->javascript)) return;
			if (
				strpos($val, 'http://') === 0 ||
				strpos($val, 'https://') === 0
			) $this->javascript[] = $val;
			else if (
				substr($val, -3) == '.js' and
				file_exists('components/' . $val)
			) $this->javascript[] = $val;
			else if (
				file_exists("components/{$val}.js") ||
				file_exists("components/{$val}/js.php") ||
				file_exists("components/{$val}/{$val}.js")
			) $this->javascript[] = $val;
			switch ($val) {
			case "world_map":
				$this->javascript[] = "google-maps/markerclusterer.js";
				break;
			case "google-maps":
				$this->javascript[] = "http://maps.google.com/maps/api/js?key=AIzaSyBeRM7BDdB6UzJ-z_IJftYP6lMx3e4u5H4&v=3&region=US";
				break;
			}
#			preg_match_all("'-(\d+)(\.\d+)?'", $val, $file);
			return;
		case 'gmaps':
			if (empty($this->gmaps)) {
				echo '<div id="google-maps"></div>';
			}
			if (is_string($val)) {
				$this->gmaps[] = $val;
			} else {
				$this->gmaps[] = true;
			}
			return;
		}
	}

	public function __get($var) {
		if (in_array($var, array(
			'lang','title','javascript'
		))) return $this->$var;
		if (preg_match("'^(world_?)?map$'", $var)) return $this->getWorldMap();
		switch($var) {
		}
	}
}
?>
