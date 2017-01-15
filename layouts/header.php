<!DOCTYPE html>
<html lang="en-us">
<head>
<meta charset="utf-8"/>
<?php
if (!empty($this->title)) {
	echo "<title>" . self::Site_Title . " | {$this->title}</title>";
} else {
	echo "<title>" . self::Site_Title . "</title>";
}
?>
<base href="/" target="_top" />
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<link rel="stylesheet" href="/css"/>
<script src="/components/upgrdr.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script>if(!window.jQuery)document.write('<script src="/components/jquery.2-1-4.min.js"><\/script>')</script>
<script src="/components/site.js"></script>
<?php foreach ($this->javascript as $js) {
	if (strpos($js, 'http://') === 0 or strpos($js, 'https://') === 0)
		echo "<script src=\"{$js}\" async></script>";
	else if (substr($js, -3) == '.js' and file_exists($_SERVER['DOCUMENT_ROOT'] . '/components/' . $js))
		echo "<script src=\"/components/{$js}\" async></script>";
	else
		echo "<script src=\"/components/{$js}.js\" async></script>";
} ?>
</head>
<body itemscope itemtype="http://schema.org/<?=$this->page_type?>">
<header itemscope itmetype="http://schema.org/WPHeader">
	<a href="/" rel="home"><h1><?=self::Site_Title?></h1></a>
</header>
<nav itemscope itemtype="http://schema.org/SiteNavigationElement">
	<a href="/">Home</a>
	<li>Countries<ul><?php
		$wm = $this->getWorldMap();
		$locs = $wm->locationsByCountry();
		foreach (array_keys($locs) as $cc) {
			echo "<li><a href=\"/$cc/\">" . $wm->getCountryName($cc, $this->lang[0]) . '</a></li>';
		}
	?></ul></li>
	<li>By Date<ul><?php
		chdir($_SERVER['DOCUMENT_ROOT']);
		$dirs = glob("2[01][0-9][0-9]", GLOB_ONLYDIR);
		foreach ($dirs as $dir) {
			echo "<li><a href=\"/$dir/\">$dir</a></li>";
		}
	?></ul></li>
</nav>
<main>
