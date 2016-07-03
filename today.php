<?php
require_once("classes/classloader.php");
Session::initializeLight();

$config = Session::getConfig();

require_once("template/" .  $config->template . "/header.php");
require_once("template/" .  $config->template . "/index.php");
?>
	<script type="text/javascript">
    // Make sure the page is loaded
	$(function(){
		WSL.init_mainSummary("#main-middle");
		WSL.init_tabs("index",0,"#main-middle",
			function(){
				WSL.init_PageTodayValues("#columns", function(){
					WSL.init_PageTodayHistoryValues("#history"); // Initial load fast
				}); // Initial load fast
    		}
		);
	});
	analyticsJSCodeBlock();
	</script>
	<!-- END Wrapper -->
</body>
</html>