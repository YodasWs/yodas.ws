<?php
if ($delay_load) echo "<template id=\"{$this->fn}\">";
switch ($tag) {
case 'figure':
	echo "<figure itemscope itemtype=\"http://schema.org/Photograph\">";
	break;
}
if (!empty($this->alt)) {
	echo "<h3>{$this->alt}</h3>";
}
$this->html();
if (!empty($this->foursquare)) {
	echo $this->foursquare->venueMap();
}
echo "</$tag>";
if ($delay_load) echo '</template>';
?>
