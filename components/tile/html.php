<article class="tile">
<?php
if (!empty($this->img)) foreach ($this->img as $i) {
	$img = array("\t<img","src=\"{$i['src']}\"");
	if (!empty($i['alt'])) $img[] = "alt=\"{$i['alt']}\"";
	if (!empty($i->date)) $img[] = "data-date=\"{$i->date}\"";
	$img[] = "/>\n";
	echo join(' ', $img);
}
?>
	<h1><a href="<?=$this->url?>"><?=$this->title?></a></h1>
</article>
