<?php
require_once("site.php");
$blog = new BlogSite();
$uri = trim($_SERVER['REQUEST_URI'], '/');

$dir = explode('/', $uri);
if (preg_match("'^[a-z]{2}$'", $dir[0])) {
	// This is a country code
	// TODO: Look up in world.xml
	$wm = $blog->world_map;
	$locations = $wm->getByCountry($dir[0]);
	if (count($locations)) {
		require_once("components/tile/tile.php");
		foreach ($locations as $l) {
			$t = new Tile($l);
			$t->html();
		}
		exit;
	}
}

switch ($uri) {
// Show Homepage
case '':
	require_once("components/tile/tile.php");

	$wm = $blog->world_map;
	$tp = array_slice($wm->top_places, 0, min(24, count($wm->top_places)));

	foreach ($tp as $p) {
		$tile[] = new Tile($p);
	}

	WorldMap::html();
	foreach ($tile as $t) {
		$t->html();
	}
	break;
case 'hakone':
	require_once("components/gtfs/gtfs.php");
	$gtfs = new GTFS();
	$gtfs->addLocation('jp/hakone');
	$gtfs->html();
	break;
case 'world':
	if (!strstr($_SERVER['HTTP_ACCEPT'], "text/html")) {
		header("HTTP/1.1 404 Not Found");
		echo '<h1>404 Not Found</h1>';
		exit;
	}
	$wm = $blog->world_map;
	print '<pre>';
	print_r($wm);
	print '</pre>';
	break;
default:
	if (strstr($uri, '/') === false) {
		$xml = $blog->loc($uri);
		if (!empty($xml)) {
			print "<h1>" . urldecode($uri) . "</h1>";
			print '<pre>' . print_r($xml, true) . '</pre>';
			exit;
		}
	}
	header("HTTP/1.1 404 Not Found");
	print <<<NotFoundHTML
<h1>404 Not Found</h1>
<div style="border:0 none;min-height:50vh">
	<p>Sorry, we couldn't find the requested file.</p>
	<p><a href="/">Return Home</a></p>
</div>
NotFoundHTML;
}
?>
