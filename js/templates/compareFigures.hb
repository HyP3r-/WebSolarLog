<div style="display:table-cell;"><div id="columns" style="display:table-cell; width:100%;">
<div id="todayPosts"><div class="columns">
<h3>Compare</h3>
<div class="column span-16" style="border-left:solid 1px #ccc;">
	<div class="column span-4 first">{{lang.month}}</div>
	<div class="column span-5" style="text-align:right;">{{lang.harvested}}</div>
	<div class="column span-5" style="text-align:right;">{{lang.CumHarvested}}</div>
	<div class="cl"></div>
	{{#each which}}
		{{#each this}}
		<div class="tr1">
			<div class="column span-4 first">{{this.date}}</div>
			<div class="column span-5" style="text-align:right;">{{this.harvested}}</div>
			<div class="column span-5" style="text-align:right;">{{this.displayKWH}}</div>
			<div class="cl"></div>
		</div>
		{{/each}}
	{{/each}}
</div>
<div class="column span-17">
	<div class="column span-4 first">{{lang.month}}</div>
	<div class="column span-5" style="text-align:right;">{{lang.expected}}</div>
	<div class="column span-5" style="text-align:right;">{{lang.CumExpected}}</div>
	<div class="cl"></div>
	{{#each compare}}
		{{#each this}}
		<div class="tr2">
			<div class="column span-4 first" >{{this.date}}</div>
			<div class="column span-5" style="text-align:right;">{{this.harvested}}</div>
			<div class="column span-5 last" style="text-align:right;">{{this.displayKWH}}</div>
			<div class="cl"></div>
		</div>
		{{/each}}
	{{/each}}
</div>
<div class="column span-11">
	<div class="column span-5" style="text-align:right;">{{lang.difference}}</div>
	<div class="column span-5" style="text-align:right;">{{lang.CumDifference}}</div>
	<div class="cl"></div>
	{{#each diff}}
		<div class="tr3">
			<div class="column span-5" style="text-align:right;color:{{this.diffHarvestedColor}}">{{this.diffDailyCalc}}</div>
			<div class="column span-5" style="text-align:right;color:{{this.diffColor}}">{{this.diffCumCalc}}</div>
			<div class="cl"></div>
		</div>
	{{/each}}
	<div class="cl"></div>
</div></div>
<div class="cl"></div>
*{{difference}}={{lang.harvested}}-{{lang.expected}}
</div>
</div>
</div>
<script>$(function() {
$("div.tr1:even").css('background-color','#ddd');
$("div.tr2:even").css('background-color','#ddd');
$("div.tr3:even").css('background-color','#ddd');
});</script>