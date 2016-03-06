<?php
chdir($_SERVER['DOCUMENT_ROOT']);
session_start();
class BlogSite {
	private $title = 'YodasWs';
	private $date = array(
		'year' => false,
		'mon' => false,
		'day' => false,
	);
	private $dirLayouts = "layouts/";
	private $fileHeader = "header.php";
	private $fileFooter = "footer.php";
	private $javascript = array();
	private $page_wrap = true;
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

	public static function getDate($str) {
		$date = array();
		$arr = explode('/', trim($str, '/'));
		if (preg_match("'^\d{4}$'", $arr[0])) {
			$date['year'] = $arr[0];
			if (!empty($arr[1]) and self::is_mon($arr[1])) {
				$date['mon'] = self::int_mon($arr[1]);
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
		return trim("{$date['day']} {$date['mon']} {$date['year']}");
	}

	public static function int_mon($str) {
		if (!self::is_mon($str)) return false;
		if (is_numeric) $str = (int) $str;
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
		if (is_numeric) $str = (int) $str;
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
		return preg_replace("'%(a-f0-9){2}'i", '', $str);
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

		if ($this->page_wrap) include_once($this->dirLayouts.$this->fileHeader);
		echo $content;
		if ($this->page_wrap) include_once($this->dirLayouts.$this->fileFooter);
	}

	public function __set($var, $val) {
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
		case 'javascript':
			if (in_array($val, $this->javascript)) return;
			if (
				strpos($val, 'http://') === 0 ||
				strpos($val, 'https://') === 0
			) $this->javascript[] = $val;
			else {
				preg_match_all("'-(\d+)(\.\d+)?'", $val, $file);
				if (
					file_exists("components\\{$val}\\js.php") ||
					file_exists("components\\{$val}\\{$val}.js")
				) $this->javascript[] = $val;
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
