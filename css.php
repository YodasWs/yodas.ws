<?php
header("Content-Type: text/css");

$file = trim($_SERVER['REQUEST_URI'], '/');
$file = explode('/', $file);
$file = end($file);
$minfile = explode('.', $file);
array_splice($minfile, -1, 0, 'min');
$minfile = implode('.', $minfile);

// Internet Explorer Style Sheet
$isIE = (strpos($file, 'ie') === 0);

// List CSS Files to Combine
$files = array();
$glob = glob("{layouts,components/*}/*.css", GLOB_BRACE) or array();
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
	// Output CSS to browser immediately and get CSS for slow minification process
	$css = ob_get_flush();
	// Minify
	$tidy->load_template('highest_compression');
	$tidy->parse($css);
	$css = $tidy->print->plain();
	file_put_contents($minfile, $css);
	exit;
} else {
	include($minfile);
}
?>
