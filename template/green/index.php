<body>
<a name="top"></a>
<!-- Wrapper -->
<div id="wrapper">
	<div class="shell">
		<!-- Header -->
		<div id="header">
			<!-- Logo -->
			<div style="display: inline-block;">
				<h1 id="logo"><a href="./" title="Dashboard"><?php echo $config->title; ?></a></h1>
				<h2 id="subLogo"><a href="./" title="Dashboard"><?php echo $config->subtitle; ?></a></h2>
			</div>
			<div class="socials" style="width:170px;">
			  <b class="spiffy">
			  <b class="spiffy1"><b></b></b>
			  <b class="spiffy2"><b></b></b>
			  <b class="spiffy3"></b>
			  <b class="spiffy4"></b>
			  <b class="spiffy5"></b></b>
			  <div class="spiffyfg">
   				<a title="pvoutput" class="pvoutput" href="#">in</a>
				<a title="twitter" class="twitter" href="#">twitter</a>
				<a title="flickr" class="flickr" href="#">flickr</a>
				<a title="Facebook" class="facebook" href="#">Facebook</a>
				<input type="checkbox" value="1" id="stayLiveOnBlur"/>
				<div class="cl"></div>
				<div id="reqLoading">
					Request Loading...
						<img id="imgLoading" src='./template/green/css/images/loading.gif'/>
				</div>
			  </div>
			  <b class="spiffy">
			  <b class="spiffy5"></b>
			  <b class="spiffy4"></b>
			  <b class="spiffy3"></b>
			  <b class="spiffy2"><b></b></b>
			  <b class="spiffy1"><b></b></b></b>
			  <div id="languageSelect" style="display: none"></div>
			</div>
		</div>
		<!-- END Header -->
		<!-- Navigation -->
		<div id="navigation">
			<ul>
				<li id="mDashboard" class="nav-start"><a title="Dashboard" href="index.php"><?php echo _("Dashboard");?></a></li>
				<li id="mToday"><a title="Today" href="today.php"><?php echo _("Day");?></a></li>
				<li id="mMonth"><a title="Month" href="month.php"><?php echo _("Month");?></a></li>
				<li id="mYear"><a title="Year" href="year.php"><?php echo _("Year");?></a></li>
				<li id="mDetails"><a title="Details" href="details.php"><?php echo _("Details");?></a></li>
				<li id="mCompare"><a title="Compare" href="compare.php"><?php echo _("Compare");?></a></li>
				<li id="mProduction"><a title="Production" href="production.php"><?php echo _("Production");?></a></li>
				<li id="mMisc" class="nav-end"><a title="Misc" href="misc.php"><?php echo _("Other");?></a></li>
			</ul>
			<div class="cl"></div>
		</div>
		<!-- END Navigation -->
		<!-- Main -->
		<div id="main">
			<div id="main-top"></div>
			<div id="main-middle"><div id="contentLoading"><br><img src="template/green/css/images/contentLoading.gif"/></div>
				<!-- Content -->
				<div id="content">
				</div>
				<!-- END Content -->
				<!-- Sidebar -->
				<div id="sidebar"></div>
				<!-- END Sidebar -->
				<div class="cl"></div>
				<div style="display: table;">
        			<div style="display:table-cell;"><div id="right-column"></div></div>
    				<div id="columns" style="display:table-cell; width:100%;"></div>
				</div>
			</div>
			<div id="main-bottom"></div>
			<div id="smartMeterLive"></div>
		</div>
		<!-- END Main -->
		<!-- Footer -->
		<div id="footer">
			<div id="footer-middle">
				<div style="display: inline-block;">
					<a href="http://www.websolarlog.com" target="_blank">Check us out [at] WebSolarLog.com</a>
				</div>
				<div style="display: inline-block; text-align: right; width: 275px;">
					<div style="display: inline-block;">JS loadtime:&nbsp;</div>
					<div id="JSloadingtime" style="display: inline-block;"></div>
				</div>
				<div style="float: right;">
					<div id="version" style="display: inline-block;">
						<?php echo _("version") ?>:&nbsp;<?php echo($config->version_title . " (build " . $config->version_revision . ")") ?>
					</div>
				</div>
			<div style="text-align: left;align:center;">
			<hr style="background-color:#8BC32B;height:10px;margin:0 0 3px;">
			<div style="float:left; width:180px;margin-left:;">
			WebSolarLog:<br/>
			<a href="http://www.websolarlog.com/" target="_blank" class="leftFooter">WebSolarLog.com</a><br/>
			<a href="http://www.websolarlog.com/index.php/help-support/" target="_blank" class="leftFooter">Support Group</a><br/>
			<a href="http://www.websolarlog.com/index.php/development/" target="_blank" class="leftFooter">Development</a><br/>
			<a href="http://www.websolarlog.com/index.php/faqs/" target="_blank" class="leftFooter">FAQ</a><br/>
			<a href="http://tracker.websolarlog.com/login_anon.php?return=view_all_bug_page.php" target="_blank" class="leftFooter">Issue Tracker</a><br/>
			
			</div>
			<div style="float:left; width:180px;">
			Supported device:<br/>
			<a href="http://www.PowerOne.com/" target="_blank" class="leftFooter">PowerOne</a> <a href="http://www.curtronics.com/Solar/AuroraData.html" target="_blank">(Curtronics)</a><br/>
			<a href="http://www.sma.de/en/products/overview.html" target="_blank" class="leftFooter">SMA RS485</a> <a href="http://code.google.com/p/sma-get/" target="_blank">(SMA-get)</a><br/>
			<a href="http://www.sma.de/en/products/overview.html" target="_blank" class="leftFooter">SMA BlueTooth</a> <a href="http://code.google.com/p/sma-spot/" target="_blank">(SMA-spot)</a><br/>
			<a href="http://www.diehl.com/en/diehl-controls/photovoltaics/wechselrichter.html" target="_blank" class="leftFooter">Diehl</a>(not needed)<br/>			
			<a href="http://www.kostal-solar-electric.com/" target="_blank" class="leftFooter">Kostal Piko</a> <a href="http://sourceforge.net/projects/piko/" target="_blank">(Piko script)</a><br/>
			<a href="http://www.solar-inverter.com/en-GB/index.htm" target="_blank" class="leftFooter">Delta Solivia</a> <a href="https://github.com/bprice/DeltaPVOutput" target="_blank">(DeltaPVOutput)</a><br/>
			<a href="http://www.websolarlog.com/" target="_blank" class="leftFooter">Dutch SmartMeter</a> (software)<br/>
			<a href="http://www.openweathermap.org" target="_blank" class="leftFooter">OpenWeatherMap</a> (Webservice)<br/>
			</div>

			<div style="float:left; width:200px;">
			Solar Inverter Logger Demos:<br/>
			<a href="http://www.websolarlog.com/wsldemoproxy.php?brand=powerone&redir=true">PowerOne Inverter (RS485)</a><br/>
			<span class="leftFooter">SMA Inverter (RS485)<br/></span>
			<span class="leftFooter">SMA Inverter (BlueTooth)<br/></span>
			<span class="leftFooter">Effekta Inverter (RS485)<br/></span>
			<a href="http://diehl-inverter-demo.websolarlog.com/" target="_blank" class="leftFooter">Diehl Inverter (Ethernet)</a><br/>
			</div>
			
			
			<div style="float:left; width:200px;">
			Misc.:<br/>
			<a href="http://pvoutput.org/listteam.jsp?tid=602" target="_blank">Join PVoutput team WebSolarLog</a><br/>
			</div>
			<div class="cl"></div>
			</div>
			
		</div>
		<!-- END Footer -->
	</div>
</div>