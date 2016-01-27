<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("components/component.php");
class WorldMap implements Component {

	private static $self;
	private $top_places = array();
	private $xml;

	public static function html() {
		global $blog;
		$blog->javascript = "world_map";
		require_once("components/world_map/html.php");
	}

	public static function singleton() {
		if (empty(self::$self)) {
			self::$self = new WorldMap();
		}
		return self::$self;
	}

	public static function grabLocation($loc) {
		$wm = self::$self;
		return $wm->getLocation($loc);
	}

	public function getLocation($loc) {
		foreach ($this->xml['locale'] as $l) {
			if (!empty($l['google']) and (
				(string) $l['google'] == $loc or BlogSite::urlencode((string) $l['google']) == $loc
			)) return $l;
		}
		return false;
	}

	public function __construct() {
		global $blog;
		$this->xml = json_decode(json_encode(simplexml_load_file('world.xml')), true);

		$lang_xml = false;
		foreach ($blog->lang as $l) if (file_exists("world.{$l}.xml")) {
			$lang_xml = simplexml_load_file("world.{$l}.xml");
			break;
		}
		if (empty($lang_xml)) $lang_xml = simplexml_load_file('world.en.xml');
		$lang_xml = json_decode(json_encode($lang_xml), true);
		$this->xml = array_merge_recursive($this->xml, $lang_xml);
	}

	public function __get($var) {
		if (in_array($var, array(
		))) return $this->$var;
		switch ($var) {
		case 'top_places':
			if (!empty($this->top_places)) return $this->top_places;
			$this->top_places = array();
			foreach ($this->xml['locale'] as $l) {
				$this->top_places[] = $l;
			}
			uasort($this->top_places, function($a, $b) {
				if (empty($a['img']) and !empty($b['img'])) return 1;
				if (!empty($a['img']) and empty($b['img'])) return -1;
				if (!empty($a['img']) and !empty($b['img'])) {
					if (count($a['img']) == count($b['img'])) return 0;
					return (count($a['img']) < count($b['img'])) ? 1 : -1;
				}
				return 0;
			});
			return $this->top_places;
		}
	}

}
