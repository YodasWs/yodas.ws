<article class="tile">
<?php
if (!empty($this->img)) foreach ($this->img as $k => $i) {
	$i->html($k > 0);
}
?>
	<h1><?=$this->title?></h1>
</article>
