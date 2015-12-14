<?php
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

	public function flush() {
		ob_flush();
	}

	public function __construct() {
		ob_start();
		include_once($this->dirLayouts.$this->fileHeader);

		$this->date = self::date($_SERVER['REQUEST_URL']);
	}

	public static function date($str) {
		$date = array();
		$arr = explode('/', trim($str, '/'));
		if (preg_match("'^\d{4}$'", $arr[0])) {
			$date['year'] = $arr[0];
			if (!empty($arr[1]) and self::is_mon($arr[1])) {
				$date['mon'] = self::int_mon($arr[1]);
				if (!empty($arr[2]) and is_numerical($arr[2])) {
					if (checkdate($date['mon'], (int) $arr[2], $date['year'])) {
						$date['day'] = (int) $arr[2];
					}
				}
			}
		}
		return $arr;
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
		switch (strtolower(substr($str, 0, 3))) {
		case 'jan':
		case 'feb':
		case 'mar':
		case 'apr':
		case 'may':
		case 'jun':
		case 'jul':
		case 'aug':
		case 'sep':
		case 'oct':
		case 'nov':
		case 'dec':
			return true;
		}
		return false;
	}

	public static function str_mon($mon) {
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

	public function __destruct() {
		include_once($this->dirLayouts.$this->fileFooter);
		ob_end_flush();
	}

	public function __set($var, $val) {
		// Does Directory Exist?
		if (in_array($var,array('dirLayouts'))) {
			if (is_dir($val))
				$this->$var = $val;
		}
		// Does File Exist?
		if (in_array($var,array('fileHeader','fileFooter'))) {
			if (is_file($this->dirLayouts.$val))
				$this->$var = $val;
		}
	}

	public function __get($var) {
		if (in_array($var, array(
			'title'
		))) return $this->$var;
	}
}
?>
