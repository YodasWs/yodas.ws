<?php
require_once("site.php");
$blog = new BlogSite();
$uri = trim($_SERVER['REQUEST_URI'], '/');
$dir = explode('/', $uri);

// Convert Location Names to Lower Case
if (preg_match("'^[a-z]{2}$'i", $dir[0]) and preg_match("'[A-Z]'", $dir[0])) {
	header("HTTP/1.1 301 Found");
	header("Location: /" . BlogSite::urlencode($uri) . "/");
	print "<h1>" . urldecode($uri) . "</h1>";
	exit;
}

// First Level is Country Code
if (preg_match("'^[a-z]{2}$'", $dir[0])) {
	// This is a country code
	$locations = $blog->world_map->getByCountry($dir[0]);
	if (count($locations)) {
		if (count($dir) == 1) {
			require_once("components/tile/tile.php");
			foreach ($locations as $l) {
				if (!is_array($l)) continue;
				$t = new Tile($l);
				$t->html();
			}
			exit;
		} else {
			// Load Location Data
			$content_loaded = false;
			$gtfs_dir = strtolower("{$dir[0]}/{$dir[1]}");
			$xml = $blog->world_map->getLocation($dir[1]);
			if (empty($xml)) {
				// Location not in World Map, go to Country Page
				header("HTTP/1.1 303 See Other");
				header("Location: /" . BlogSite::urlencode($dir[0]) . "/");
				print "<h1>" . urldecode($uri) . "</h1>";
				exit;
			} else if (count($xml['date']) === 1 or is_string($xml['date'])) {
				// Only one entry? Go there!
				header("HTTP/1.1 303 See Other");
				header("Location: /" . BlogSite::urlencode($xml['date']) . "/");
				print "<h1>" . urldecode($uri) . "</h1>";
				exit;
			} else {
				$content_loaded = true;
				$blog->title = $xml['name'] . ', ' . $blog->world_map->getCountryName($dir[0]);
				echo "<header>";
				echo "<h1>{$xml['name']}</h1>";
				echo "<h2><a href=\"/{$dir[0]}/\">" . $blog->world_map->getCountryName($dir[0]) . "</a></h2>";
				echo "</header>";
			}
			// Load GTFS Component
			if (is_dir("gtfs/$gtfs_dir")) {
				$content_loaded = true;
				require_once("components/gtfs/gtfs.php");
				$gtfs = new GTFS();
				$gtfs->addLocation($gtfs_dir);
				$gtfs->html();
			}
			// Load Location Images
			$img = $blog->world_map->getImages($dir[1]);
			if (count($img)) {
				$content_loaded = true;
				foreach ($img as $i) {
					$i->print_figure();
				}
			}
			if ($content_loaded) exit;
			// If nothing to display, drop through to 404
		}
	}
}

switch ($uri) {
// Show Homepage
case '':
	require_once("components/tile/tile.php");

	$wm = $blog->world_map;

	// Get Latest Entries
	define('EntriesFiles', 'entries.txt');
	if (strpos($_SERVER['HTTP_HOST'], 'dev') === 0 or !file_exists(EntriesFiles) or time() - filemtime(EntriesFiles) > 24 * 60 * 60) {
		$entries = glob('20{0,1}{0,1,2,3,4,5,6,7,8,9}/{0,1}{0,1,2,3,4,5,6,7,8,9}/{0,1,2,3}{0,1,2,3,4,5,6,7,8,9}{,.en}.xml', GLOB_BRACE);
		foreach ($entries as &$entry) {
			$entry = array(
				'file' => $entry,
				'mtime' => filemtime($entry),
			);
		}
		usort(
			$entries,
			function($a, $b) {
				return $b['mtime'] - $a['mtime'];
			}
		);
		file_put_contents(EntriesFiles, serialize($entries));
	}
	$entries = unserialize(file_get_contents(EntriesFiles));
	$entries = array_slice($entries, 0, min(5, count($entries)));
	foreach ($entries as $entry) {
		$tile[] = new Tile($entry['file']);
	}

	// Show top places
	$tp = array_slice($wm->top_places, 0, min(24, count($wm->top_places)));
	foreach ($tp as $p) {
		$tile[] = new Tile($p);
	}

	// Show top countries
	$countries = $wm->locationsByCountry();
	$countries = array_slice($countries, 0, 5);
	foreach ($countries as $c) {
		$c = new Tile($c);
		$tile[] = $c;
	}

	// Output HTML
	WorldMap::html();
	foreach ($tile as $t) {
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
