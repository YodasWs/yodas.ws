<?php
require_once("site.php");
$blog = new BlogSite();
$xml = $blog->getXMLFile();

header("HTTP/1.1 404 Not Found");
?>
<h1>Coming Soon</h1>
