<?php
require_once("site.php");
$blog = new BlogSite();

switch ($_SERVER['REQUEST_URL']) {
case '/':
case '':
	require_once("components/world_map/world_map.php");
	break;
}
?>
	<article>
		<h1>Shanghai</h1>
	</article>
<?php
?>
