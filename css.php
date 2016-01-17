<?php
header("Content-Type: text/css; charset=UTF-8");
date_default_timezone_set('America/Detroit');

function etag($time) {
	return date("YMdHT", $time);
}

$file = trim($_SERVER['REQUEST_URI'], '/');
$file = explode('/', $file);
$file = end($file);
$minfile = explode('.', $file);
if (end($minfile) != 'css') $minfile[] = 'css';
array_splice($minfile, -1, 0, 'min');
$minfile = implode('.', $minfile);

// Internet Explorer Style Sheet
$isIE = (strpos($file, 'ie') === 0);

// List CSS Files to Combine
$files = array();
$glob = glob("{{layouts,components/*}/*.css,components/*/css.php}", GLOB_BRACE) or array();
foreach ($glob as $css) {
	if (strpos($css, 'ie') !== 0 || $isIE)
		$files[] = $css;
}

// If not minified in past week, minify now
if (strpos($_SERVER['HTTP_HOST'], 'test') === 0 or !file_exists($minfile) or time() - filemtime($minfile) > 7 * 24 * 60 * 60) {
	// Require CSSTidy
	require_once('csstidy/class.csstidy.php');
	$tidy = new csstidy();
	// Gather CSS
	ob_start();
	foreach($files as $file) {
		include($file);
	}
	// Set ETag
	header("Last-Modified: " . date('r'));
	header("ETag: " . etag(time()));
	// Output CSS to browser immediately and get CSS for slow minification process
	$css = ob_get_flush();
	// Minify
	$tidy->load_template('highest_compression');
	$tidy->parse($css);
	$css = $tidy->print->plain();
	file_put_contents($minfile, $css);
	exit;
} else {
	header("Last-Modified: " . date('r', filemtime($minfile)));
	// Determine if browser cached file
	if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) and $_SERVER['HTTP_IF_NONE_MATCH'] === etag(filemtime($minfile))) {
		header("HTTP/1.1 304 Not Modified");
		exit;
	}
	include($minfile);
}
?>
