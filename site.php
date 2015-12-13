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

		$uri = explode('/', trim($_SERVER['REQUEST_URL'], '/'));
		if (preg_match("'^\d{4}$'", $uri[0])) {
			$this->date['year'] = $uri[0];
			if (!empty($uri[1]) and self::is_mon($uri[1])) {
				$this->date['mon'] = self::int_mon($uri[1]);
				if (!empty($uri[2]) and is_numerical($uri[2])) {
					if (checkdate($this->date['mon'], (int) $uri[2], $this->date['year'])) {
						$this->date['day'] = (int) $uri[2];
					}
				}
			}
		}
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
