<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("components/component.php");
class Img implements Component {
	private $xml;

	public function __construct($filename) {
		global $blog;
		$filename = explode('.', $filename);
		if (end($filename) != 'xml') {
			$filename[] = $blog->lang;
			$filename[] = 'xml';
		}
		$filename = implode('.', $filename);
		if (!file_exists($filename)) {
			throw new Exception("Could not find $filename");
		}
		$this->xml = json_decode(json_encode(simplexml_load_file($filename)), true);
	}
	public function html() {
		require("html.php");
	}
}
