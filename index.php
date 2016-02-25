<?php
require_once("site.php");
$blog = new BlogSite();
$uri = trim($_SERVER['REQUEST_URI'], '/');

$dir = explode('/', $uri);
if (preg_match("'^[a-z]{2}$'", $dir[0])) {
	// This is a country code
	$locations = $blog->world_map->getByCountry($dir[0]);
	if (count($locations)) {
		if (count($dir) == 1) {
			require_once("components/tile/tile.php");
			foreach ($locations as $l) {
				$t = new Tile($l);
				$t->html();
			}
			exit;
		} else {
			$content_loaded = false;
			$gtfs_dir = strtolower("{$dir[0]}/{$dir[1]}");
			$xml = $blog->world_map->getLocation($dir[1]);
			if (!empty($xml)) {
				$content_loaded = true;
				echo "<h1>{$xml['name']}</h1>";
			}
			if (is_dir("gtfs/$gtfs_dir")) {
				$content_loaded = true;
				require_once("components/gtfs/gtfs.php");
				$gtfs = new GTFS();
				$gtfs->addLocation($gtfs_dir);
				$gtfs->html();
			}
			$img = $blog->world_map->getImages($dir[1]);
			if (count($img)) {
				$content_loaded = true;
				foreach ($img as $i) {
					$i->print_figure();
				}
			}
			if ($content_loaded) exit;
		}
	}
}

switch ($uri) {
// Show Homepage
case '':
	require_once("components/tile/tile.php");

	$wm = $blog->world_map;

	$countries = $wm->locationsByCountry();
	$countries = array_slice($countries, 0, 5);
	foreach ($countries as $c) {
		$c = new Tile($c);
		$tile[] = $c;
	}

	$tp = array_slice($wm->top_places, 0, min(24, count($wm->top_places)));
	foreach ($tp as $p) {
		$tile[] = new Tile($p);
	}

	WorldMap::html();
	foreach ($tile as $t) {
#		echo '<pre>' . print_r($t,1) . '</pre>';
		$t->html();
	}
	break;
default:
	if (strstr($uri, '/') === false) {
		$xml = $blog->loc($uri);
		if (!empty($xml)) {
			header("HTTP/1.1 301 Found");
			header("Location: {$xml['@attributes']['cc']}/" . BlogSite::urlencode($uri));
			print "<h1>" . urldecode($uri) . "</h1>";
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
