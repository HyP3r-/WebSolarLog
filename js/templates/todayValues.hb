<div id="todayPosts">
	<div class="columns">
		<div class="col posts">
			<h3>{{lang.today}}</h3>
			<div class="post">
				<div class="container">
					<div  class="column span-22">
						<div class="column span-22">
							<div class="column span-22"><h4>{{lang.maxGridPower}}</h4></div>
							<div class="column span-5 first">{{lang.inv}}</div>
							<div class="column span-4" style="text-align:right;">{{lang.watt}}</div>
							<div class="column span-8 last">{{lang.time}}</div>
				    		{{#each data.maxPower}}
				    			{{#each this}}
					    			<div class="column span-5 first">{{this.name}}</div>
					    			<div class="column span-4" style="text-align:right;">{{this.maxGP}}</div>
					    			<div class="column span-8 last">{{timestampDateFormat this.date format="HH:mm:ss"}}</div>
				    			{{/each}}
				    		{{/each}}
					    </div>
					    	
						<div class="column span-22">
					    	<div class="column span-22"><h4>{{lang.TotalKWh}}</h4></div>
							<div class="column span-5 first">{{lang.inv}}</div>
							<div class="column span-4" style="text-align:right;">{{lang.kwh}}</div>
							<div class="column span-8 last">{{lang.time}}</div>
							{{#each data.maxEnergy}}
								{{#each this}}
					    			<div class="column span-5 first">{{this.name}}</div>
					    			<div class="column span-4" style="text-align:right;">{{this.kWh}}</div>
					    			<div class="column span-8 last">{{timestampDateFormat this.date format="HH:mm:ss"}}</div>
					    		{{/each}}
					    	{{/each}}
					    </div>
					</div>
				</div>								
			</div>
		</div>
		<div class="col projects">
		<h3>&nbsp;</h3>
			<div id="historyContainer">historyContainer</div>
			<div class="cl"></div>									
		</div>							
	</div>
	<div class="cl"></div>
	</div>
</div>