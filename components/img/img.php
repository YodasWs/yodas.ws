<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("components/component.php");
class Img implements Component {
	private $alt;
	private $src;
	private $xml;

	public function __construct($filename) {
		if (!file_exists($filename)) {
			throw Exception("Could not find $filename");
		}
		$this->xml = json_decode(json_encode(simplexml_load_file($filename)), true);
	}
	public function html() {
		require("html.php");
	}
}
