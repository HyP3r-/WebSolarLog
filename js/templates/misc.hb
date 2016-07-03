<div id="todayPosts">
<div class="columns"><h3>Misc</h3>
<br><br>
<div class="col posts">
<h3>{{lang.logger}} {{lang.uptime}}:</h3>
{{data.serverUptime.day}} {{lang.days}},{{data.serverUptime.hour}} {{lang.hours}},{{data.serverUptime.min}} {{lang.mins}}<br>
<br>
</div>
<div class="cl"></div>
<div class="col posts">
<div class="column span-22 last">
		<h3>Third Party software</h3>
			<div class="post">		
				<div  class="column span-22 accordion">
			{{#each data.slimConfig.devices}}
			<h3>{{this.name}}</h3>
			<div class="innerAccordionPeriod">
				<div class="post">
				{{#if_eq this.deviceApi compare="DutchSmartMeter"}}
				This device is using a custom made Python script to get data from the SmartMeter.
				{{/if_eq}}
				{{#if_eq this.deviceApi compare="DutchSmartMeterRemote"}}
				This device is using a custom made PHP appoarch to get data from the SmartMeter.
				{{/if_eq}}
				
				{{#if_eq this.deviceApi compare="Diehl-ethernet"}}
				This device is using the Diehl's ethernet connection and "API" to gather data.<br> 
				{{/if_eq}}
				
				{{#if_eq this.deviceApi compare="Open-Weather-Map"}}
				This device is using the Open Weather Map APi.<br> 
				<a href="http://openweathermap.org/" target="_blank">http://openweathermap.org/</a>
				{{/if_eq}}
				
				{{#if_eq this.deviceApi compare="AURORA"}}
				This device is using the Aurora software.<br>
				The software can be found here:<br>
				<a href="http://www.curtronics.com/Solar/AuroraData.html" target="_blank">http://www.curtronics.com/Solar/AuroraData.html</a>
				{{/if_eq}}
				
				{{#if_eq this.deviceApi compare="SMA-BT-WSL"}}
				This device is using the SMA-spot software.<br>
				The software can be found here:<br>
				<a href="http://code.google.com/p/sma-spot/" target="_blank">http://code.google.com/p/sma-spot/</a>
				{{/if_eq}}
				
				{{#if_eq this.deviceApi compare="SMA-BT"}}
				This device is using the SMA-spot software.<br>
				The software can be found here:<br>
				<a href="http://code.google.com/p/sma-spot/" target="_blank">http://code.google.com/p/sma-spot/</a>
				{{/if_eq}}
				
				{{#if_eq this.deviceApi compare="SMA-RS485"}}
				This device is using the SMA-get software.<br>
				The software can be found here:<br>
				<a href="http://code.google.com/p/sma-get/" target="_blank">http://code.google.com/p/sma-get/</a>
				{{/if_eq}}
				
				{{#if_eq this.deviceApi compare="KostalPiko"}}
				This device is using the Piko software.<br>
				The software can be found here:<br>
				<a href="http://sourceforge.net/projects/piko/" target="_blank">http://sourceforge.net/projects/piko/</a>
				{{/if_eq}}
				
				{{#if_eq this.deviceApi compare="DeltaSolivia"}}
                This device is using Python scripts.<br>
				The software can be found here:
               	https://github.com/bprice/DeltaPVOutput [^]
                {{/if_eq}}
				
				
				</div>
			</div>
			{{/each}}
			</div></div></div>
</div>
<div class="col posts">
	<div class="column span-22 last">
		<h3>PVoutput {{lang.information}}</h3>
			<div class="post">		
				{{lang.events}}
				
				<div  class="column span-22 accordion">
			{{#each data.slimConfig.devices}}
			<h3>{{this.name}}</h3>
			<div class="innerAccordionPeriod">
				<div class="post">		
					{{lang.events}}
					<table width="100%">
					{{#if_eq this.pvoutputEnabled compare=1}}
						This device is submitting to PVoutput:<br>
						<a href="http://pvoutput.org/list.jsp?sid={{this.pvoutputSystemId}}" target="_blank">Checkout my PVoutput page</a><br><br>
						{{#if_eq this.pvoutputWSLTeamMember compare=1}}
							and I'm a proud member of the <a href="http://pvoutput.org/listteam.jsp?tid=602" target="_blank">PVoutput WebSolarLog team</a> :)
						{{else}}
							and unfortunately i'm not a member of the <a href="http://pvoutput.org/listteam.jsp?tid=602" target="_blank">WebSolarLog team</a> :(
						{{/if_eq}}
					
					{{else}}
						This device itself is not submitting to PVoutput.<br>
						It could be that the data from this device is used by another device to submit is.<br>
					{{/if_eq}}
					
					</table>
				</div>
			</div>
			{{/each}}
			</div></div></div>
</div>
<div class="cl"></div>
<div class="col posts">
</div>
<div class="col posts">
	<div class="column span-22 last">
    	<h3>{{lang.Inverter}} {{lang.Information}}</h3>
		<div class="post">		
			{{lang.events}}
			<div class="column span-4 first">{{lang.Inv}}</div>
			<div class="column span-4">{{lang.Time}}</div>
			<div class="column span-11 last">{{lang.Event}}</div>
	
			{{#each data.slimConfig.devices}}
				{{#unless_eq this.events.info compare=null}}
					<div class="column span-4 first">{{this.name}}</div>
					<div class="column span-4" title="{{timestampDateFormat this.events.info.time format="HH:mm:ss"}}">{{timestampDateFormat this.events.info.time format="DD MMM YYYY"}}</div>
					<div class="column span-11 last">{{this.events.info.eventHTML}}</div>
				{{/unless_eq}}
			{{/each}}
		</div>
	</div>
</div>
<div class="col posts">
	<div class="column span-22 last">
    	<h3>{{lang.Inverter}} {{lang.systemInfo}}</h3>
		<div class="post">
<div  class="column span-22 accordion">
{{#each data.slimConfig.devices}}
{{#if_eq this.type compare="production"}}
<h3>Inv. {{this.name}}</h3>
<div class="innerAccordionPeriod">
<div class="column span-20">
	
		<div class="column span-6"><b>{{../../lang.system}}</b>:</div>
	</div>
	<div class="column span-20">
	
		<div class="column span-6">{{../../lang.power}}</div>
		<div class="column span-4">{{../../lang.expected}}</div>
		<div class="column span-4">{{../../lang.location}}</div>
	</div>
	<div class="column span-20">
		<div class="column span-6">{{this.plantPower}} {{../../lang.wp}}</div>
		<div class="column span-4">{{this.expectedKWH}} {{../../lang.kWh}}</div>
		<div class="column span-4"><a href="http://maps.google.com/maps?q={{../../data.slimConfig.lat}},+{{../../data.slimConfig.long}}+%28...%29&iwloc=A&hl=en&z=15" target="_blank">{{../../data.slimConfig.lat}}:{{../../data.slimConfig.long}}</a></div>
	</div>
	<div class="column span-20">
		{{#each this.panels}}
		<div class="column span-19 prepend-1"><b>- {{../../../lang.mpp}}{{this.id}}</b></div>
		<div class="column span-19 prepend-2">
			<div class="column span-8">{{../../../lang.description}}</div>
			<div class="column span-5">{{../../../lang.power}}</div>
			<div class="column span-4">{{../../../lang.orientation}}/{{../../../lang.pitch}}</div>
		</div>
		<div class="column span-19 prepend-2">
			<div class="column span-8">{{this.description}}</div>
			<div class="column span-5">{{this.amount}}*{{this.wp}}={{this.totalWp}} {{../../../lang.wp}}</div>
			<div class="column span-4">{{this.roofOrientation}}°/{{this.roofPitch}}°</div>
		</div>
		{{/each}}
		<br><br><br><br>
		<b>Photo's</b>
		<div class="column span-19">
		<a href="systemPhotos.php">Go to system photos</a>
		</div>
 	</div>
 </div>
{{/if_eq}}
{{/each}}
 	</div>
		</div>
	</div>
</div>
<div class="cl"></div>

<div class="col posts">
	<div class="column span-22 last">
		<h3>{{lang.Notice}}</h3>
		
		<div  class="column span-22 accordion">
			{{#each data.slimConfig.devices}}
			<h3>{{this.name}}</h3>
			<div class="innerAccordionPeriod">
					
				<div class="post">		
					{{lang.events}}
					<table width="100%">
						<thead>
							<tr>
								<th align="left">{{../lang.Time}}</th>
								<th align="left">{{../lang.Event}}</th>
							</tr>
						</thead>
						<tbody>
							{{#each this.events.notice}}
							<tr>
								<td style="white-space: nowrap;" title="{{timestampDateFormat this.time format="HH:mm:ss"}}">{{timestampDateFormat this.time format="DD MMM YYYY"}}</td>
								<td>{{this.eventHTML}}</td>
							</tr>
							{{/each}}
						</tbody>
					</table>
				</div>
			</div>
			{{/each}}
		</div>
	</div>
</div>
<div class="col posts">
	<div class="column span-22 last">
		<h3>{{lang.Alarm}}</h3>
			<div class="post">		
				{{lang.events}}
				
				<div  class="column span-22 accordion">
			{{#each data.slimConfig.devices}}
			<h3>{{this.name}}</h3>
			<div class="innerAccordionPeriod">
					
				<div class="post">		
					{{lang.events}}
					<table width="100%">
						<thead>
							<tr>
								<th align="left">{{../lang.Time}}</th>
								<th align="left">{{../lang.Event}}</th>
							</tr>
						</thead>
						<tbody>
							{{#each this.events.alarm}}
							<tr>
								<td style="white-space: nowrap;" title="{{timestampDateFormat this.time format="HH:mm:ss"}}">{{timestampDateFormat this.time format="DD MMM YYYY"}}</td>
								<td>{{this.eventHTML}}</td>
							</tr>
							{{/each}}
						</tbody>
					</table>
				</div>
			</div>
			{{/each}}
		</div>
	</div></div>
<div class="cl"></div>

</div>
</div>