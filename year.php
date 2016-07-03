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
		WSL.init_tabs("index",3,"#main-middle",
			function(){
				WSL.init_PageYearValues("#columns","#periodList"); // Initial load fast
    		}
		)
	});
	analyticsJSCodeBlock();
	</script>
	<!-- END Wrapper -->
</body>
</html>