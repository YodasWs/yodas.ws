<!DOCTYPE html>
<html lang="en-us">
<head>
<meta charset="utf-8"/>
<title><?=$this->title?></title>
<link rel="stylesheet" href="/"/>
<script src="/components/upgrdr/"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<?php foreach ($this->javascript as $js) {
	if (strpos($js, 'http://') === 0 || strpos($js, 'https://') === 0)
		echo "<script src=\"{$js}/\" async></script>";
	else
		echo "<script src=\"/components/{$js}/\" async></script>";
} ?>
</head>
<body>
<?php include_once("google_analytics.php"); ?>
<header>
	<a href="/" rel="home"><h1><?=$this->title?></h1></a>
</header>
<nav>
	<a href="/">Home</a>
	<li>By Country</li>
	<li>By Date</li>
</nav>
<main>
