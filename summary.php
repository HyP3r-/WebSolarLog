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
		$("#contentLoading").hide();
		WSL.init_summaryPage("#content");
	});

	 // Initial load fast
	analyticsJSCodeBlock();
	</script>
	<!-- END Wrapper -->
</body>
</html>