<?php
require_once("classes/classloader.php");
Session::initializeLight();

$config = Session::getConfig();

require_once("template/" .  $config->template . "/header.php");
require_once("template/" .  $config->template . "/index.php");

?>
				<<script type="text/javascript">
				$(function(){
					$("#contentLoading").hide();
					WSL.init_systemPhotos("#content");
				});

				 // Initial load fast
				analyticsJSCodeBlock();
				</script>
				
	<!-- END Wrapper -->
</body>
</html>