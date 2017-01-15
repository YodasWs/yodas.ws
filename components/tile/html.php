<article class="tile <?=$this->__get('class_list')?>">
<?php
if (!empty($this->img)) foreach ($this->img as $k => $i) {
	$i->html($k > 0);
}
?>
	<h1><a href="<?=$this->url?>"><?=$this->title?></a></h1>
</article>
