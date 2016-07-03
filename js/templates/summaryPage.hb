<div id="summaryPage" >
	<div class="column span">
		<div class="span-48">
			<div class="span-14 first summaryblock">	
				<strong>CO2 related:</strong><br>
				Power CO2: {{data.total.totalUsageKWHCO2}} kg<br>
				Gas CO2:{{data.total.metering.gasUsageCO2}} kg<br>
			</div>
			<div class="span-14 summaryblock">
				<strong>Solar data:</strong><br>
				{{data.total.production.KWH}} kWh generated, which equals 
				<span title="{{data.total.costkwh}} * {{data.total.production.KWH}} = {{data.total.production.costs}}"> {{data.total.production.costs}}</span><br>
				
				The generated kWh's equals {{data.total.production.CO2avoid}} kg avoided CO2.<br>
				<br>
				<a href="systemPhotos.php">system photo's</a><br>		
			</div>
			<div class="span-14 last summaryblock">
				{{#if_gt data.total.weather.pressure compare=0}}
				<strong>Weather data:</strong><br>
				<canvas id="layer1" style="z-index: 0;"></canvas><br>
				Degree days:{{data.total.weather.degreeDays}}<br>
				Avarage wind speed:{{data.total.weather.avgWindSpeed}} m/s<br>
				Avarage temperature:{{data.total.weather.avgTemp}} &deg;C<br>
				Avarage wind Chill:{{data.total.weather.avgWindChill}} &deg;C<br>
				Current Wind chill:{{data.total.weather.currentWindChill}}  &deg;C<br>
				{{else}}
				No weather data available.
				{{/if_gt}}<br>
			</div>
			
			
			<!-- ##################################-->
			<!-- ############ new line ############-->
			<!-- ##################################-->
			<div class="cl"></div>
			
			
			<div class="span-14 first summaryblock">
				<strong>trees</strong><br>
				Total household CO2 usage equals the daily consumption of <span title="The household produces {{data.total.householdCO2}} CO2 and a tree consumses {{data.total.co2CompensationTree}} grams of CO2 a day so we need {{data.total.totalUsageCO2Trees}} trees to compensate this"><strong>{{data.total.householdTrees}}</strong></span> trees.
			</div>
			
			<div class="span-14 summaryblock">
				<strong>Household usage data:</strong><br>
				from grid: {{data.total.householdUsage}} kWh, {{data.total.householdCO2}} kg, {{data.total.householdCosts}}<br>
				from sun: {{data.total.usedBeforeMeterKWH}} kWh, {{data.total.usedBeforeCO2}} kg, {{data.total.usedBeforeMeterCosts}}<br>
				household: <span title="We generated {{data.total.production.KWH}} of that we used {{data.total.usedBeforeMeterKWH}} in the house, so we returned {{data.total.metering.returnKWH}} to the grid">{{data.total.totalUsagekWh}} kWh</span><br>
				Money{{#if_gt data.total.metering.gasUsageCO2 compare=0}}combined with {{data.total.metering.gasUsageCO2}} CO2 for gas {{/if_gt}}gives a total of {{data.total.householdCO2}} for the household">{{data.total.householdCO2}}</span></strong> kg CO2 and 
				
				<span title="{{data.total.moneySign}} {{data.total.costkwh}} * {{data.total.totalUsagekWh}} = {{data.total.moneySign}} {{data.total.totalUsageKWHCosts}}">{{data.total.totalUsageKWHCosts}}</span><br>
				{{#if_gt data.total.metering.returnKWH compare=0}}
    			<strong>Gas:</strong><br>
				{{data.total.metering.gasUsage}} m3 usage<br>
				<span title="m3 gas per degree day;(18 - {{data.total.weather.avgTemp}} = {{data.total.weather.degreeDays}} degree days)">m3/dd: {{data.total.m3PerdegreeDays}}</span>
				{{/if_gt}}
			</div>
			<div class="span-14 last summaryblock">
				<strong>Grid data:</strong><br>
				{{#if_gt data.total.metering.returnKWH compare=0}}
				
				<span title="{{data.total.moneySign}} {{data.total.costkwh}} * {{data.total.metering.returnKWH}} = {{data.total.moneySign}} {{data.total.metering.returnCosts}}"><strong>{{data.total.metering.returnKWH}}</strong> kWh returned to your neighbors</span><br>
				The returned kWh's equals <span title="Somebody else is getting your carbon free power :D"><strong>{{data.total.metering.returnCO2}}</strong> kg CO2 and <span title="We returned this to the grid, so somebody else can use it."> and {{data.total.moneySign}} {{data.total.metering.returnCosts}}</span>
				{{else}}
				You used all the generated power in your own house, so there are no kWh's returned to the grid.				
				{{/if_gt}}
			</div>
			<div class="cl"></div>
		</div>
	</div>
</div>
