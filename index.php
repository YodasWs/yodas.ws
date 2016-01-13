<?php
require_once("site.php");
$blog = new BlogSite();

// TODO: View Blog Entry
if (preg_match("'^/\d{4}/\d\d/\d\d'", $_SERVER['REQUEST_URL'])) {
// TODO: View Month Calendar
} else if (preg_match("'^/\d{4}/\d\d'", $_SERVER['REQUEST_URL'])) {
// TODO: View Year Calendar/List
} else if (preg_match("'^/\d{4}'", $_SERVER['REQUEST_URL'])) {
} else switch ($_SERVER['REQUEST_URL']) {
// Show Homepage
case '/':
case '':
	require_once("components/world_map/world_map.php");
	require_once("components/tile/tile.php");

	$wm = WorldMap::singleton();
	$tp = array_slice($wm->top_places, 0, 24);

	foreach ($tp as $p => $n) {
		$tile[] = new Tile($p);
	}

	WorldMap::html($blog);
	foreach ($tile as $t) {
		$t->html();
	}
	break;
}
?>
<?php
?>
