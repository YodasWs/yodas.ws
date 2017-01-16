<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("components/component.php");
class Img implements Component {
	private $fsq;
	private $date;
	private $xml;
	private $fn;

	public function __construct($filename) {
		global $blog;
		if (!is_string($filename) or empty($filename)) {
			error_log("String not given to Img::__construct()");
			return false;
		}
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
			$this->fn = implode('.', $filename);
			if (file_exists($this->fn)) {
				break;
			}
		}
		if (!file_exists($this->fn)) {
			error_log("Could not find $this->fn");
			return false;
		}
		$this->xml = json_decode(json_encode(simplexml_load_file($this->fn, null, LIBXML_NOCDATA)), true);
	}

	public function print_figure($delay_load = false) {
		$tag = 'figure';
		require('html.php');
	}

	public function print_entry($delay_load = false) {
		$tag = 'section';
		require('html.php');
	}

	public function html($delay_load = false) {
		global $blog;
		$blog->javascript = 'img';
		$img = array(
			$delay_load ? "\t<load-img" : "\t<img",
			"data-date=\"" . BlogSite::date_toString($this->date) . '"',
			"src=\"{$this->src}\"",
			"itemprop=\"image\""
		);
		if (!empty($this->alt)) $img[] = "alt=\"{$this->alt}\"";
		if (!empty($this->height)) $img[] = "data-height=\"{$this->height}\"";
		$style = array();
		if (!empty($this->height)) $style[] = "max-height:{$this->height}px";
		$img[] = 'style="' . join(';', $style) . '"';
		$img[] = $delay_load ? "></load-img>" : "/>";
		echo join(' ', $img);
	}

	public function __get($var) {
		if (in_array($var, array(
			'alt','src','height','heading'
		))) return $this->xml[$var];
		switch ($var) {
		case 'date':
			return BlogSite::date_toString($this->date);
		case 'foursquare':
			if (get_class($this->fsq) == 'Foursquare') {
				return $this->fsq;
			}
			if (empty($this->xml['foursquare'])) {
				return false;
			}
			require_once("components/4sq/4sq.php");
			$this->fsq = new Foursquare($this->xml['foursquare']);
			return $this->fsq;
		}
	}
	public function __isset($var) {
		if (in_array($var, array(
			'alt','src','height','foursquare','heading'
		))) return !empty($this->xml[$var]);
	}
}
