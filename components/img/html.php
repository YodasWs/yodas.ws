<?php
#echo '<pre>' . print_r($this, true) . '</pre>';
$img = array(
	$delay_load ? "\t<load-img" : "\t<img",
	"src=\"{$this->src}\"",
	"data-date=\"{$this->date_toString()}\""
);
if (!empty($this->alt)) $img[] = "alt=\"{$this->alt}\"";
$img[] = $delay_load ? "></load-img>" : "/>\n";
echo join(' ', $img);
?>
