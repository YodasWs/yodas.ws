<?php
require_once("site.php");
require_once("components/blog_entry.php");
$blog = new BlogSite();
$xml = $blog->getXMLFile();
$entry = new BlogEntry($_SERVER['REQUEST_URI']);

if (empty($xml)) {
	header("HTTP/1.1 404 Not Found");
	echo "<h1>Coming Soon</h1>";
	exit;
}
?>
<?php
echo "<pre>" . print_r($_SERVER, true) . "</pre>";
echo "<pre>" . print_r($entry, true) . "</pre>";
foreach ($xml as $name => $child) {
	if (!is_array($child)) {
		$child = array($child);
	}
	switch ($name) {
	case 'img':
		foreach ($child as $img) {
			$img = new Img($img);
			echo $img->html();
		}
		break;
	}
}
