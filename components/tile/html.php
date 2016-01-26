<article class="tile">
<?php
if (!empty($this->img)) foreach ($this->img as $i) {
	$img = array("\t<img","src=\"{$i['@attributes']['src']}\"");
	if (!empty($i['alt'])) $img[] = "alt=\"{$i['alt']}\"";
	if (!empty($i['date'])) $img[] = "data-date=\"{$i['date']}\"";
	$img[] = "/>\n";
	echo join(' ', $img);
}
?>
	<h1><?=$this->title?></h1>
</article>
