<?php
if ($delay_load) echo "<template id=\"{$this->fn}\">";
switch ($tag) {
case 'section':
	echo "<section itemscope itemtype=\"http://schema.org/Photograph\">";
	$caption_tag = 'h3';
	break;
case 'figure':
	echo "<figure itemscope itemtype=\"http://schema.org/Photograph\">";
	$caption_tag = 'figcaption';
	break;
}
if (!empty($this->heading)) {
	echo "<{$caption_tag}>{$this->heading}</{$caption_tag}>";
} else if (!empty($this->alt)) {
	echo "<{$caption_tag}>{$this->alt}</{$caption_tag}>";
}
$this->html();
if ($tag == 'section') {
	if (!empty($this->foursquare)) {
		echo $this->foursquare->venueMap();
	}
}
echo "</$tag>";
if ($delay_load) echo '</template>';
?>
