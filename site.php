<?php
class BlogSite {
	private $title = 'YodasWs';

	public function flushContent() {
		ob_flush();
	}

	public function __construct() {
		ob_start();
		include("layouts/header.php");
	}

	public function __destruct() {
		include("layouts/footer.php");
		ob_end_flush();
	}

	public function __set($var, $val) {
	}

	public function __get($var) {
		if (in_array($var, array(
			'title'
		))) return $this->$var;
	}
}
?>
