<?php
require_once("../site.php");
require_once("components/blog_entry.php");
$blog = new BlogSite();
$blog->page_type = 'ImageGallery';
$entry = new BlogEntry($_SERVER['REQUEST_URI']);

if (empty($entry->xml)) {
	header("HTTP/1.1 404 Not Found");
	echo "<h1>Coming Soon</h1>";
	exit;
}
?>
<?php
echo "<h1>{$entry->title}</h1>";
#echo "<pre>" . print_r($_SERVER, true) . "</pre>";
foreach ($entry->img as $img) {
	$img->print_figure();
}
foreach ($entry->xml as $name => $child) {
	if (!is_array($child)) {
		$child = array($child);
	}
	switch ($name) {
	}
}
#echo "<pre>" . print_r($entry, true) . "</pre>";
