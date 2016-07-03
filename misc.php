<?php
require_once("classes/classloader.php");
Session::initializeLight();

$config = Session::getConfig();

require_once("template/" .  $config->template . "/header.php");
require_once("template/" .  $config->template . "/index.php");
?>

	<script type="text/javascript">
	$(function(){
		WSL.init_tabs("index", 0, "#main-middle", function(){
			WSL.init_misc(1,"#columns"); // Initial load fast
		});
		

	});
	analyticsJSCodeBlock();
	</script>
	
</body>
</html>