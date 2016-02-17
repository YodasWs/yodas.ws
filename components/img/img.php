<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("components/component.php");
class Img implements Component {
	private $date;
	private $xml;

	public function __construct($filename) {
		global $blog;
		$filename = explode('.', $filename);
		$this->date = BlogSite::getDate($filename[0]);
		if (end($filename) != 'xml') {
			$filename[] = 'xml';
		}
		$lang_i = count($filename) - 2;
		if (!preg_match("'^[a-z]{2}$'", $filename[$lang_i])) {
			array_splice($filename, -1, 0, $blog->lang[0]);
			$lang_i = count($filename) - 2;
		}
		for ($i=0; $i<count($blog->lang); $i++) {
			$filename[$lang_i] = $blog->lang[$i];
			$fn = implode('.', $filename);
			if (file_exists($fn)) {
				break;
			}
		}
		if (!file_exists($fn)) {
			error_log("Could not find $fn");
			return false;
		}
		$this->xml = json_decode(json_encode(simplexml_load_file($fn)), true);
	}

	public function html($delay_load = false) {
		global $blog;
		$blog->javascript = 'img';
		require("html.php");
	}

	public function __get($var) {
		if (in_array($var, array(
			'alt','src'
		))) return $this->xml[$var];
		switch ($var) {
		case 'date':
			return BlogSite::date_toString($this->date);
		}
	}
	public function __isset($var) {
		if (in_array($var, array(
			'alt','src'
		))) return !empty($this->xml[$var]);
	}
}
