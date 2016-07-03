
<div id="todayHistoryAcc" class="column span-22 accordion">
{{#each data}}
			<h3>{{this.deviceName}}</h3>
			<div>
			<div style="min-height:200px;height:200px;overflow-y:scroll;">	
					{{#each this.data}}
					<div class="column span-10 last">{{timestampDateFormat this.date format="HH:mm:ss"}}</div>
					<div class="column span-4" style="text-align:right;">{{this.GP}}</div>
					{{/each}}
					</div>
			</div>
{{/each}}
</div>