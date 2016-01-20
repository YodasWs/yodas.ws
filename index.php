<?php
require_once("site.php");
$blog = new BlogSite();


// TODO: View Blog Entry
if (preg_match("'^/\d{4}/\d\d/\d\d'", $_SERVER['REQUEST_URI'])) {
// TODO: View Month Calendar
} else if (preg_match("'^/\d{4}/\d\d'", $_SERVER['REQUEST_URI'])) {
// TODO: View Year Calendar/List
} else if (preg_match("'^/\d{4}'", $_SERVER['REQUEST_URI'])) {
} else switch (trim($_SERVER['REQUEST_URI'], '/')) {
// Show Homepage
case '':
	require_once("components/tile/tile.php");

	$wm = $blog->world_map;
	$tp = array_slice($wm->top_places, 0, 24);

	foreach ($tp as $p) {
		$tile[] = new Tile($p);
	}

	WorldMap::html();
	foreach ($tile as $t) {
		$t->html();
	}
	break;
case 'world':
	if (!preg_match("'(^|\W)text/html(\W|$)'", $_SERVER['HTTP_ACCEPT'])) {
		// header("HTTP/1.1 40x Unacceptable");
		exit;
	}
	$wm = $blog->world_map;
	print '<pre>';
	print_r($wm);
	print '</pre>';
	break;
default:
	$page = trim($_SERVER['REQUEST_URI'], '/');
	if (strstr($page, '/') === false) {
		$xml = $blog->loc($page);
			print "<h1>" . urldecode($page) . "</h1>";
		if (!empty($xml)) {
			print "<h1>" . urldecode($page) . "</h1>";
			print '<pre>' . print_r($xml, true) . '</pre>';
			exit;
		}
	}
	header("HTTP/1.1 404 Not Found");
	print <<<NotFoundHTML
<h1>404 Not Found</h1>
<p>Sorry, we couldn't find the requested file.</p>
<p><a href="/">Return Home</a></p>
NotFoundHTML;
}
?>
