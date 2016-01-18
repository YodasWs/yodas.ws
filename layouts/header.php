<!DOCTYPE html>
<html lang="en-us">
<head>
<meta charset="utf-8"/>
<title><?=$this->title?></title>
<link rel="stylesheet" href="/main.css"/>
<script src="/components/upgrdr/"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<?php foreach ($this->javascript as $js) {
	echo "<script src=\"/components/{$js}/\" async></script>";
} ?>
</head>
<body>
<?php include_once("google_analytics.php"); ?>
<header>
	<a href="/" rel="home"><h1><?=$this->title?></h1></a>
</header>
<nav><ul>
	<li><a href="/">Home</a></li>
</ul></nav>
<main>
