<article class="tile">
<?php
if (!empty($this->img)) foreach ($this->img as $i) {
	echo '<pre>' . print_r($i, true) . '</pre>';
	$img = array("\t<img","src=\"{$i->src}\"", "data-date=\"{$i->date}\"");
	if (!empty($i->alt)) $img[] = "alt=\"{$i->alt}\"";
	$img[] = "/>\n";
	echo join(' ', $img);
}
?>
	<h1><?=$this->title?></h1>
	<pre><?=print_r($this->xml, true)?></pre>
</article>
