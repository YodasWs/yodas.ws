<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("components/component.php");
class GTFS implements Component {

	public function html() {
		global $blog;
		$blog->javascript = "gtfs";
		$blog->javascript = "google-maps";
		require_once("components/gtfs/html.php");
	}

	public function addLocation($loc) {
		if (in_array($loc, $_SESSION['gtfs_locs'])) return true;
		if (file_exists("gtfs/{$loc}/shapes.txt")) {
			$_SESSION['gtfs_locs'][] = $loc;
		}
	}

	public function __construct() {
		$_SESSION['gtfs_locs'] = null;
		if (empty($_SESSION['gtfs_locs'])) $_SESSION['gtfs_locs'] = array();
	}
}
