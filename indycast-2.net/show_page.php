<?
include_once('../indycast.net/common.php');
$page = $_GET['page'];
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$device = 'device';
if(empty($page)) {
  $page = 'index';
}
if(strpos($ua, 'mobile') !== False) {
  $device = 'smartphone';
}
if(strpos($ua, 'android') !== False) {
  $device = 'Android smartphone';
}
if(strpos($ua, 'iOS') !== False or strpos($ua, 'iPhone') != False) {
  $device = 'Apple device';
}
if(strpos($ua, 'windows') !== False) {
  $device = 'Windows machine';
}

if(isset($_GET['callsign'])) {
  $callsign = $_GET['callsign'];
} else {
  $callsign = '';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <style type="text/css">code{white-space: pre;}</style>
  <link href='http://fonts.googleapis.com/css?family=Inconsolata' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Lora' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Arvo' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Slabo+27px' rel='stylesheet' type='text/css'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta charset="utf-8" />
  <meta name="description" content="indycast: community driven podcasting" />
  <meta property="og:site_name" content="Indycast" />
  <meta property="og:url" content="http://indycast.net" />
  <meta property="og:title" content="About indycast, community driven podcasting" />
  <meta property="og:type" content="website" />
  <meta property="og:description" content="Welcoming lazy hackers from the world over." />
  <meta property="og:image" content="http://indycast.net/og-image.php" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:site" content="@indycaster" />
  <meta name="twitter:creator" content="@indycaster" />
  <meta name="twitter:title" content="incycast: community drivne podcasting" />
  <meta name="twitter:url" content="http://indycast.net" />
  <meta name="twitter:description" content="Welcoming lazy hackers from the world over." />
  <meta name="twitter:image:src" content="http://indycast.net/images/twit-image.jpg" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="favicon.ico" >
  <link href="/css/bootstrap.min.css" rel="stylesheet">
  <link href="/css/sb-admin.css" rel="stylesheet"> 
  <link href="/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>
  <div id="wrapper">
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="navbar-header hidden-lg">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/">Indycast</a>
      </div>

      <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul class="nav navbar-nav side-nav">
          <li <?= $page == 'index' ? 'class="active"':'' ?>><a href="/"><i class="fa fa-fw fa-calendar"></i> Free Podcasts</a></li>
          <li <?= $page == 'reminder' ? 'class="active"':'' ?>><a href="/reminder"><i class="fa fa-fw fa-pencil-square-o"></i> Email Me Radio</a></li>
   <!--       <li <?= $page == 'live' ? 'class="active"':'' ?>><a href="/live"><i class="fa fa-fw fa-clock-o"></i> Listen Live</a></li>-->
          <li <?= $page == 'about' ? 'class="active"':'' ?>><a href="/about"><i class="fa fa-fw fa-book"></i> Our Story</a></li>
          <li><a href="https://github.com/kristopolous/DRR/wiki/Donating-Money"><i class="fa fa-fw fa-heart"></i> Support Us</a></li>
          <li><a href="https://github.com/kristopolous/DRR"><i class="fa fa-fw fa-code"></i> Source Code</a></li>
        </ul>
      </div>
    </nav>

    <div class="container-fluid page-content">
<? 
if(file_exists("content/$page.php")) {
  include("content/$page.php");
}
?>
    </div>
  </div>
  <script src="/js/evda.min.js"></script>
  <script src="/js/jquery.js"></script>
  <script src="/js/bootstrap.min.js"></script>
</body>
</html>
