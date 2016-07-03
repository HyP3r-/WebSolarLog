<?php
/*
 * already loaded in parent (index/today/month/year/compare/production).php file
 * $config = Session::getConfig(); 
 */ 

?>
<!DOCTYPE HTML>
<html>
<head>
	<title><?php echo $config->title; ?></title>
	<META http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="shortcut icon" href="template/green/css/images/favicon.ico" />
	
	<?php if($config->phpMinify == false){?>
	<link rel="stylesheet" href="css/blueprint/screen.css" type="text/css" media="screen, projection"/>
	<link rel="stylesheet" href="css/blueprint/print.css" type="text/css" media="print"/>
	<link rel="stylesheet" href="template/green/css/style.css" type="text/css" media="all" />
	<link rel="stylesheet" href="css/jquery.jqplot.min.css" type="text/css" />
	<link rel="stylesheet" href="css/jquery.jqplot.overrule.style.css" type="text/css" />
	<link rel="stylesheet" href="css/jquery.pnotify.default.css" type="text/css" />

	<link rel="stylesheet" href="js/jqueryuicss/jquery-ui.min.css" type="text/css" />
	<link rel="stylesheet" href="js/jqueryuicss/jquery.ui.overrule.css" type="text/css" />
	<link rel="stylesheet" href="template/green/css/custom.css" type="text/css" media="all" />
	
	<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="js/excanvas.min.js"></script><![endif]-->
	<script type="text/javascript">var isFront=true;</script>
	<script type="text/javascript" src="js/jquery-2.1.0.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.10.3.min.js"></script>
	<script type="text/javascript" src="js/jquery.pnotify-1.2.0.min.js"></script>
	<script type="text/javascript" src="js/moment-2.4.0.min.js"></script>
	<script type="text/javascript" src="js/handlebars-1.3.js"></script>
	<script type="text/javascript" src="js/helpers.js"></script>
	<script type="text/javascript" src="js/suncalc.js"></script>
	<script type="text/javascript" src="js/jquery.jqplot-1.0.8r1250.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.json2.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.barRenderer.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.canvasTextRenderer.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.canvasOverlay.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.dateAxisRenderer.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.meterGaugeRenderer.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.cursor.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.trendline.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.pointLabels.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.highlighter.min.js"></script>
    <script type="text/javascript" src="js/jqplot_plugins/jqplot.enhancedLegendRenderer.min.js"></script>
    <script type="text/javascript" src="js/astrocal.js"></script>
    <script type="text/javascript" src="js/websolarlog.js"></script>
    <?php }else{?>
    <script type="text/javascript">var isFront=true;</script>

    <link rel="stylesheet" href="PHPMinify/min/?g=css" type="text/css" media="all" />
    
    <link rel="stylesheet" href="PHPMinify/min/?g=cssPrint" type="text/css" media="print"/>
    <link rel="stylesheet" href="PHPMinify/min/?g=cssProjection" type="text/css" media="projection"/>
    
	<!--[if lt IE 8]><link rel="stylesheet" href="css/blueprint/ie.css" type="text/css" media="screen, projection"/><![endif]-->

    
    <script type="text/javascript" src="PHPMinify/min/?g=js1"></script>
    <script type="text/javascript" src="PHPMinify/min/?g=js2"></script>
    <script type="text/javascript" src="PHPMinify/min/?g=js3"></script>

    <?php }?>
</head>