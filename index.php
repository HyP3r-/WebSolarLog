<?php
require_once("classes/classloader.php");
Session::initializeLight();

// check if WSLConfig.json exists in the php tmp directory AND file-age is not older then 600sec/5min.
if(file_exists(sys_get_temp_dir()."/WSLConfig.json")){
	$data['configType'] = 'json';
	$config = json_decode(file_get_contents(sys_get_temp_dir()."/WSLConfig.json"));
}else{
	//Session::initializeLight();
	$data['configType'] = 'db';
	$config = Session::getConfig();
}

		

require_once("template/" .  $config->template . "/header.php");
require_once("template/" .  $config->template . "/index.php");
?>
<input type="hidden" id="graphReady" value="false"/>
<input type="hidden" id="frontendLiveInterval" value="<?php echo $config->frontendLiveInterval*1000;?>"/>
<script type="text/javascript">
    // Make sure the page is loaded
	$(function(){
		WSL.init_mainSummary("#main-middle");
		WSL.init_tabs("index",0, "#main-middle",
			function(){
				$('#graphReady').on("change",function(){
					if($('#graphReady').val() == 'true'){			
						window.setTimeout(function(){WSL.init_PageLiveValues("#content",function(){indexLiveInverters});},400);
						window.setTimeout(function(){WSL.init_PageIndexTotalValues("#sidebar",function(){sidebar});},800);
						window.setTimeout(function(){WSL.init_PageIndexLiveValues("#indexLiveInverters");},1200);
						
						$('#graphTodayContent canvas').ready(function(){
							window.setInterval(function(){WSL.init_PageIndexLiveValues("#indexLiveInverters");}, $('#frontendLiveInterval').val());
						});
						analyticsJSCodeBlock();
		    		}		    		
				})
    		}
		)	
	});
</script>
<!-- END Wrapper -->
</body>
</html>