<?php
session_start(); 	
?>
<!Doctype html>
<html lang="en">
<head>
<title>Configuration</title>
<meta charset="utf-8">
<link rel="shortcut icon" href="css/images/favicon.ico" />
<link rel="stylesheet" href="../css/blueprint/screen.css" type="text/css" media="screen, projection"/>
<link rel="stylesheet" href="../css/blueprint/print.css" type="text/css" media="print"/>
<link rel="stylesheet" href="../css/slick.grid.css" type="text/css" media="screen"/>
<!--[if lt IE 8]><link rel="stylesheet" href="css/blueprint/ie.css" type="text/css" media="screen, projection"/><![endif]-->

<link rel="stylesheet" href="../css/jquery.pnotify.default.css" type="text/css" />
<link rel="stylesheet" href="css/style.css" type="text/css" />
<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" href="../js/jqueryuicss/jquery-ui.min.css" type="text/css" />
<link rel="stylesheet" href="../js/jqueryuicss/jquery.ui.overrule.css" type="text/css" />

<script type="text/javascript">var isFront=false;</script>
<script type="text/javascript" src="../js/jquery-2.1.0.min.js"></script>
<script type="text/javascript" src="../js/moment-2.4.0.min.js"></script>
<script type="text/javascript" src="../js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript" src="../js/jquery.calculation.min.js"></script>
<script type="text/javascript" src="../js/jquery.pnotify-1.2.0.min.js"></script>
<script type="text/javascript" src="../js/handlebars-1.3.js"></script>
<script type="text/javascript" src="../js/helpers.js"></script>
<script type="text/javascript" src="../js/suncalc.js"></script>
<script type="text/javascript" src="../js/websolarlog.js"></script>
<script type="text/javascript" src="js/admin.js"></script>
<script type="text/javascript" src="../js/SlickGrid/slick.core.js"></script>
<script type="text/javascript" src="../js/SlickGrid/slick.grid.js"></script>
<script type="text/javascript" src="../js/SlickGrid/slick.formatters.js"></script>
<script type="text/javascript" src="../js/SlickGrid/slick.editors.js"></script>
<script type="text/javascript" src="../js/SlickGrid/slick.dataview.js"></script>
</head>
<body>
	<!-- Wrapper -->
	<div id="wrapper">
		<div class="shell">
			<!-- Header -->
			<div id="header">
				<!-- Logo -->
				<h1 id="logo"><a href="../" title="Dashboard">WebSolarLog</a></h1>
				<div class="socials">
					<a title="in" class="in" href="#">in</a>
					<a title="twitter" class="twitter" href="#">twitter</a>
					<a title="flickr" class="flickr" href="#">flickr</a>
					<a title="Facebook" class="facebook" href="#">Facebook</a>
					<div class="cl"></div>
				</div>
			</div>
			<!-- END Header -->
			<!-- Navigation -->
			<div id="navigation">
				<ul>
					<li class="nav-start"><a href="#" class="btnGeneral">General</a></li>
					<li><a href="#devices" class="btnDevices">Devices</a></li>
					<li><a href="#communication" class="btnCommunication">Communication</a></li>
					<li><a href="#graphs" class="btnGraphs">Graphs</a></li>
					<li><a href="#email" class="btnEmail">eMail</a></li>
					<li><a href="#yields" class="btnYields">Yields</a></li>
					<li><a href="#advanced" class="btnAdvanced">Advanced</a></li>
					<li><a href="#social" class="btnSocial">Social</a></li>
					<!-- <li><a href="#tariff" id="btnTariff">Tariff</a></li>-->
					<li><a href="#update" class="btnUpdate">Update</a></li>
					<li><a href="#backup" class="btnBackup">Backup</a></li>
					<li id="LiPlugwise"><a href="#plugwise" class="btnPlugwise">Plugwise</a></li>
					<!-- li><a href="#DataMaintenance" id="btnDataMaintenance">DBM</a></li -->
					<li class="nav-end"><a href="#diagnostics" class="btnDiagnostics">Diagnostics</a></li>
				</ul>
				<div class="cl"></div>
			</div>
			<!-- END Navigation -->
			<!-- Main -->
			<div id="main">
				<div id="main-top"></div>
				<div id="main-middle">
					<!-- Content -->
					<header>
						<h1 id="site-title">WSL :: Configuration</h1>
						<font color="#ff0000;">Recently we made a high impact change on the way WSL is configured to communicate with external devices. Please see Admin::Communication and this forum post for more info:</font>
<a href="https://groups.google.com/d/msg/websolarlog/OFzkDKLSvcM/YKnpGmxGo_4J" target="_blank">
WebSolarLog Group: New communication manager is now active</a>
					</header>
					<div id="content">
    					<header>
    						<h2 id="page-title">Welcome to the configuration</h2>
    					</header>
            			<!-- Defining content section article -->
					</div>
					<!-- END Content -->
					<!-- Sidebar -->
					<div id="sidebar"></div>
					<!-- END Sidebar -->
					<div class="cl"></div>
					<br><br>
					<div class="column span-40 first">&nbsp;</div>
					<div class="column last"><button id="forceGet">force page reload</button></div>
				</div>
				<div id="main-bottom"></div>
			</div>
			<!-- END Main -->
			<!-- Footer -->
			<div id="footer">
				<div id="footer-middle">
					<span class="author"><a href="#">..</a></span>
					<p></p>
				</div>
				<div id="footer-bottom"></div>
				<div id="JSloadingtime"></div>
			</div>
			<!-- END Footer -->
		</div>
	</div>
	<!-- END Wrapper -->
</body>
</html>