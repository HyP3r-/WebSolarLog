<?php
require_once("classes/classloader.php");
Session::initializeLight();

$config = Session::getConfig();

require_once("template/" .  $config->template . "/header.php");
require_once("template/" .  $config->template . "/index.php");
?>
	<script type="text/javascript">
	var productionGraph, productionData;
	var meteringGraph, meteringData;
	var weatherGraph, weatherData;
	
	$(function(){
		WSL.connect.settings.useRunOnBlur = false; // We dont want to use the blur here
		
		$('#main-middle').append('<h2>production</h2><div id="production"><div id="productionGraph" style="height:200px;"></div><div id="productionLatest"></div></div>');
		$('#main-middle').append('<h2>metering</h2><div id="metering"><div id="meteringGraph" style="height:200px;"></div><div id="meteringLatest"></div></div>');
		$('#main-middle').append('<h2>weather</h2><div id="weather"><div id="weatherGraph" style="height:200px;"></div><div id="weatherLatest"></div></div>');

		// Initialize Graphs
		productionData=[];
	    productionGraph = $.jqplot('productionGraph', [new Array(1)], {
	        series: [ { yaxis: 'y2axis', label: '', showMarker: false, fill: false, lineWidth: 2, color: '#00ff00', fillAndStroke: true} ],
	        axes: {
	            xaxis: { renderer: $.jqplot.DateAxisRenderer, tickOptions: { formatString: '%H:%M:%S' }}
	        },
	        cursor: { zoom: false, showTooltip: false, show: false },
	        highlighter: { useAxesFormatters: false, showMarker: false, show: false },
	        grid: { gridLineColor: '#333333', background: 'transparent', borderWidth: 2 }
	    });

	    meteringData=[];
	    meteringGraph = $.jqplot('meteringGraph', [new Array(1)], {
	        series: [ { yaxis: 'y2axis', label: '', showMarker: false, fill: false, lineWidth: 2, color: '#0571B6', fillAndStroke: true} ],
	        axes: {
	            xaxis: { renderer: $.jqplot.DateAxisRenderer, tickOptions: { formatString: '%H:%M:%S' } }
	        },
	        cursor: { zoom: false, showTooltip: false, show: false },
	        highlighter: { useAxesFormatters: false, showMarker: false, show: false },
	        grid: { gridLineColor: '#333333', background: 'transparent', borderWidth: 2 }
	    });

	    weatherData=[];
	    weatherGraph = $.jqplot('weatherGraph', [new Array(1)], {
	        series: [ { yaxis: 'y2axis', label: '', showMarker: false, fill: false, lineWidth: 2, color: '#ff0000', fillAndStroke: true} ],
	        axes: {
	            xaxis: { renderer: $.jqplot.DateAxisRenderer, tickOptions: { formatString: '%H:%M:%S' } }
	        },
	        cursor: { zoom: false, showTooltip: false, show: false },
	        highlighter: { useAxesFormatters: false, showMarker: false, show: false },
	        grid: { gridLineColor: '#333333', background: 'transparent', borderWidth: 2 }
	    });
		
		handleLiveData(); // Fast update
	    window.setInterval(function(){handleLiveData()}, 5000);
	    window.setInterval(function(){cleanUp()}, 15000);

	    $('#contentLoading').fadeOut();
	});

	function handleLiveData() {
		console.log("WSL.connect.stats.active="+WSL.connect.stats.active);
		if (WSL.connect.stats.active < 1) {
			WSL.api.live(function(data) {
				$.each(data, function(){
					currentTime = (new Date()).getTime();
					if (this.type == "production" && this.data !== null) {
						$('#productionLatest').html('Latest value: ' + this.data.GP);
				        productionData.push([currentTime, parseInt(this.data.GP)]);
				        productionGraph.series[0].data = productionData;
				        productionGraph.resetAxesScale();
				        productionGraph.replot();
						
					}
					if (this.type == "metering" && this.data !== null) {
						usage = this.data.liveUsage - this.data.liveReturn;
						$('#meteringLatest').html('Latest value: ' + usage);
						meteringData.push([currentTime, usage]);
				        meteringGraph.series[0].data = meteringData;
				        meteringGraph.resetAxesScale();
				        meteringGraph.replot();
						
					}
					if (this.type == "weather" && this.data !== null) {
						$('#weatherLatest').html('Latest value: ' + this.data.temp);
						weatherData.push([currentTime, parseInt(this.data.temp)]);
						weatherGraph.series[0].data = weatherData;
						weatherGraph.resetAxesScale();
						weatherGraph.replot();
					}
				});
			});
		}
	}

	function cleanUp() {
		var maxArray = 50;
		if (productionData.length > maxArray) {
			productionData.splice(0, (productionData.length + 5 - maxArray));
		}
		if (meteringData.length > maxArray) {
			meteringData.splice(0, (meteringData.length + 5 - maxArray));
		}
		if (weatherData.length > maxArray) {
			weatherData.splice(0, (weatherData.length + 5 - maxArray));
		}
	}
	</script>
	<!-- END Wrapper -->
</body>
</html>