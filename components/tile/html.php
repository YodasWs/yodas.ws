<article class="tile">
<?php
if (!empty($this->img)) foreach ($this->img as $i) {
	echo <<<ImgHTML
\t<img src="{$i['src']}" />\n
ImgHTML;
}
?>
	<h1><a href="<?=$this->url?>"><?=$this->title?></a></h1>
</article>
