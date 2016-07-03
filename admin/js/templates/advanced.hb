<form>
  <input type="hidden" name="s" value="save-advanced" />
  <fieldset>
    <legend>Advanced settings</legend>
    CO2 settings:<br>
    <label for="co2kwh">CO2 avoided per kWh:</label><input type="text" name="co2kwh" value="{{data.co2kwh}}"/>in grams<br />
    <label for="co2gas">CO2 value Gas:</label><input type="text" name="co2gas" value="{{data.co2gas}}"/>in grams<br />
    <label for="co2CompensationTree">CO2 consumption tree:</label><input type="text" name="co2CompensationTree" value="{{data.co2CompensationTree}}"/>in grams<br />
	<br>
	Energy prices:<br>
    <label for="costkwh">Costs kWh:</label><input type="text" name="costkwh" value="{{data.costkwh}}"/>in cents<br />

    <label for="costGas">Costs Gas:</label><input type="text" name="costGas" value="{{data.costGas}}"/>in cents<br />

    <label for="costWater">Costs Water:</label><input type="text" name="costWater" value="{{data.costWater}}"/>in cents<br />
    
    <hr>
    <br />
    <font color="FF0000">
	Recently we introduced the Communication Manager.<br />
	The communication manager makes it possible to create, for each device, specific communication parameters.<br />
	The Admin::Advanced::Paths section will soon be removed, so please use the communication manager and import your device with the import button.
	</font>
	<br />
    <div class="examples">
	    <h3>Deprecated Paths settings</h3>
	    <div>
			Below you need to set the path of the software you use to communicate with the device.<br>See the examples for help.<br />
			<div class="examples">
				<h3>Path examples</h3>
				<div style="min-height:180px;">
					<strong>Smartmeter:</strong><br />
					python3 /var/www/websolarlog/scripts/P1.py /dev/ttyUSB0<br />(executor script serialDevice)<br />
					<br />
					<strong>SMAspot 2.0.6:</strong><br />
					/home/root/smaspot/./smaspot<br />(script)<br />you need to configure the inverter in SMAspot<br /><br />
					<strong>Smartmeter through PHP:</strong><br />
				    php /var/www/wsl/utils/wslP1.php /dev/ttyUSB0<br />(executor script serialDevice)<br />
				    <br />
				    <strong>Aurora PowerOne:</strong><br />
				    /home/root/aurora-1.8.3/aurora -Y5 -l5 -M10 /dev/ttyUSB0<br />(executor parameters serialDevice)<br />
				    <br />
				</div>
    		</div>
    
		    <label for="aurorapath">Aurora PowerOne(RS485):</label><input type="text" name="aurorapath" value="{{data.aurorapath}}" size="35"/><br />
		    <label for="smagetpath">sma-get(RS485):</label><input type="text" name="smagetpath" value="{{data.smagetpath}}"  size="35"/><br />
			<span style="color:#f00;">We recommend to use SMAspot >=2.0.6</span><br>
		    <label for="smaspotWSLpath">SMAspot <span style="color:#f00;">>2.0.6</span>(BT):</label><input type="text" name="smaspotWSLpath" value="{{data.smaspotWSLpath}}"  size="35"/><br />
		    <label for="smartmeterpath">smartmeter:</label><input type="text" name="smartmeterpath" value="{{data.smartmeterpath}}"  size="35"/><br />
		    <label for="kostalpikopath">Piko.py:</label><input type="text" name="kostalpikopath" value="{{data.kostalpikopath}}"  size="35"/><br />
		    <label for="mastervoltpath">Mastervolt (RS485):</label><input type="text" name="mastervoltpath" value="{{data.mastervoltpath}}" size="35"/><br />
		    <label for="soladinsolgetpath">Soladin Solget(RS232):</label><input type="text" name="soladinSolgetpath" value="{{data.soladinSolgetpath}}" size="35"/><br />
		    <label for="deltaSoliviapath">Delta Solivia(RS???):</label><input type="text" name="deltaSoliviapath" value="{{data.deltaSoliviapath}}" size="35"/><br />
		    <label for="plugwiseStrech20IP">Plugwise Stretch 2.0:</label><input type="text" name="plugwiseStrech20IP" value="{{data.plugwiseStrech20IP}}"  size="30"/>Stretch IP adress<br />
		    <label for="plugwiseStrech20ID">Plugwise Stretch 2.0:</label><input type="text" name="plugwiseStrech20ID" value="{{data.plugwiseStrech20ID}}"  size="30"/>Stretch ID<br />
		    <div class="examples">
			    <h3>!! Path to deprecated SMAspot 2.0.4</h3>
	    			<div style="min-height:80px;">
	    				<span style="color:#f00;">Do not use SMAspot <=2.0.4 please do not use this version anymore. Instead use >=2.0.6 (below)</span><br>
	    				<label for="smaspotpath">SMAspot <span style="color:#f00;">2.0.4</span>(BT)</label><input type="text" name="smaspotpath" value="{{data.smaspotpath}}"  size="35"/><br />
	    			</div>
	    	</div>
    	</div>
    </div>
    <br>
    <hr>
    <label for="debugmode">Debug mode:</label>{{checkboxWithHidden 'debugmode' data.debugmode}} activate debug mode<br />
    <hr>
    <label for="phpMinify">PHP minify:</label>{{checkboxWithHidden 'phpMinify' data.phpMinify}} minify requests and sources<br />
    Fpr best performance make sure that the /websolarlog/PHPMinify/tmp is writable for the webserver.
    <hr>
    <label for="cacheRate" style="float:left;">Cache update Rate:</label>
    <br>
    <div id="sliderCacheRate" class="span-20"></div><br>
    <input type="text" name="cacheInputRate" id="cacheInputRate" value="{{data.cacheInputRate}}" />secs.
     {{infoTooltip title=""}}<br />
    <hr>
    configure stat-tools:<br />
    <label for="googleAnalytics">Google Analytics:</label><input type="text" name="googleAnalytics" value="{{data.googleAnalytics}}" />(XX-00000000-0)<br />
    <label for="piwikServerUrl">Piwik server url:</label><input type="text" name="piwikServerUrl" value="{{data.piwikServerUrl}}" /><br />
    <label for="piwikSiteId">Piwik site id:</label><input type="text" name="piwikSiteId" value="{{data.piwikSiteId}}" /><br />
    <button type="button" id="btnAdvancedSubmit">Save</button>
  </fieldset>
</form> 
 
<script>
  $(function() {
$(".examples").accordion({ 
        event: "click",
        active: false,
        collapsible: true,
        autoHeight: false

    });
    $(".examples").accordion({ 
        event: "click",
        active: false,
        collapsible: true,
        autoHeight: false

    });
  });
  </script>
	