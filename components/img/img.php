<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("components/component.php");
class Img implements Component {
	private $date;
	private $xml;

	public function __construct($filename) {
		global $blog;
		$filename = explode('.', $filename);
		if (end($filename) != 'xml') {
			$filename[] = $blog->lang;
			$filename[] = 'xml';
		}
		$this->date = explode('/', $filename[0]);
		foreach ($this->date as &$d) {
			$d = (int) $d;
		}
		$this->date['mon'] = BlogSite::str_mon($this->date[1]);
		$filename = implode('.', $filename);
		if (!file_exists($filename)) {
			throw new Exception("Could not find $filename");
		}
		$this->xml = json_decode(json_encode(simplexml_load_file($filename)), true);
	}

	public function html() {
		require("html.php");
	}

	public function __get($var) {
		if (in_array($var, array(
			'alt','src'
		))) return $this->xml[$var];
		switch ($var) {
		case 'date':
			return "{$this->date[2]} {$this->date['mon']} {$this->date[0]}";
		}
	}
	public function __isset($var) {
		if (in_array($var, array(
			'alt','src'
		))) return !empty($this->xml[$var]);
	}
}
