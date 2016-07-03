<div id="mainSummary" class="ui-widget ui-widget-content ui-corner-all" >

<div class="column span">
	<div class="span" style="text-align:center;">{{data.lang.power}}:</div>
	<div class="cl"></div>
	<div class="column span-5 verticalLine" style="font-size: 25px;text-align:center;">
		<span style="font-size:10px;text-align:center;position:relative;top:-5px;">{{data.lang.generated}}</span><br>
		<a href="summary.php" style="position:relative;top:-2px;" class="summaryLink"
		{{#if_gt data.total.totalUsagekWh compare=0}}
		title="Today you used " 
		{{else}}
		title=""
		{{/if_gt}}
		>{{data.total.production.KWH}}</a><br>
		<span style="font-size:10px;text-align:center;position:relative;top:-7px;">(kWh)</span>
		<br>
	</div>
	{{#if_gt data.total.metering.returnKWH compare=0}}
	<div class="column span-5 verticalLine" style="font-size: 25px;text-align:center;">
		<span style="font-size:10px;text-align:center;position:relative;top:-5px;">{{data.lang.used}}</span><br>
		<a href="summary.php" style="position:relative;top:-2px;" class="summaryLink"
		{{#if_gt data.total.totalUsagekWh compare=0}}
		title="Today you used " 
		{{else}}
		title=""
		{{/if_gt}}
		>{{data.total.totalUsagekWh}}</a><br>
		<span style="font-size:10px;text-align:center;position:relative;top:-7px;">(kWh)</span>
		<br>
	</div>
	{{/if_gt}}
</div>
{{#if_gt data.total.metering.gasUsage compare=0}}
<div class="column span">
	<div class="span" style="text-align:center;">Gas:</div>
	<div class="cl"></div>
	<div class="column span-4 verticalLine" style="font-size: 25px;text-align:center;">
		<span style="font-size:10px;text-align:center;position:relative;top:-5px;">{{data.lang.used}}</span>
		<br>
		<a href="summary.php" style="position:relative;top:-2px;" class="summaryLink">{{data.total.metering.gasUsage}}</a><br>
		<span style="font-size:10px;text-align:center;position:relative;top:-7px;">(m3)</span>
	</div>
</div>
{{/if_gt}}

<div class="column span">
	<div class="span" style="text-align:center;">{{data.lang.trees}}:</div>
	<div class="cl"></div>
	<div class="column span-4 verticalLine" style="font-size: 25px;text-align:center;">
		<span style="font-size:10px;text-align:center;position:relative;top:-5px;">{{data.lang.subscriptTrees}}</span>
		<br>
		<a href="summary.php" style="position:relative;top:-2px;" class="summaryLink">{{data.total.trees}}</a><br>
	</div>
</div>
<div class="column span">
	<div class="span" style="text-align:center;" title="{{timestampDateFormat data.total.weather.time format="DD-MM-YYYY H:mm:ss"}}">{{data.lang.weather}}:</div>
	<div class="cl"></div>
	<div class="column span-14 verticalLine">
		<canvas id="layer1" style="z-index: 0;"></canvas>
	</div>
</div>

<div class="cl"></div>