<?php
require_once("classes/classloader.php");
Session::initializeLight();

$config = Session::getConfig();

require_once("template/" .  $config->template . "/header.php");
require_once("template/" .  $config->template . "/index.php");
$date = Common::getValue('date', 0);
?>
	<script type="text/javascript">
    // Make sure the page is loaded
	WSL.init_details("#details",'<?php echo $date;?>'); // Initial load fast
	analyticsJSCodeBlock();
	</script>
</body>
</html>