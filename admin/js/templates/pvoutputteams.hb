{{#each data.devices}}
	<div class="column span-6">{{../lang.teamName}}&nbsp;</div><div class="column span-20">{{this.pvOutputTeams.TeamName}}</div><br>
	<div class="column span-6">{{../lang.description}}&nbsp;</div><div class="column span-20">{{this.pvOutputTeams.Description}}</div>
	<div style="clear:both"></div>
	<div class="column span-6">{{../lang.energyGenerated}}&nbsp;</div><div class="column span-20">{{this.pvOutputTeams.EnergyGenerated}} {{this.pvOutputTeams.EnergyGeneratedNotation}}</div><br>
	<div class="column span-6">{{../lang.energyAverage}}&nbsp;</div><div class="column span-20">{{this.pvOutputTeams.EnergyAverage}} Wh</div><br>
	<div class="column span-6">{{../lang.teamSize}}&nbsp;</div><div class="column span-20">{{this.pvOutputTeams.TeamSize}} Wp</div><br>
	<div class="column span-6">{{../lang.averageSize}}&nbsp;</div><div class="column span-20">{{this.pvOutputTeams.AverageSize}} Wp</div><br>
	<div class="column span-6">{{../lang.numberOfSystems}}&nbsp;</div><div class="column span-20">{{this.pvOutputTeams.NumberOfSystems}}</div><br>
	<div class="column span-6">{{../lang.outputs}}&nbsp;</div><div class="column span-20">{{this.pvOutputTeams.Outputs}}</div><br>
	<hr>
{{else}}
	It looks like you are not part of the WebSolarLog PVoutput team.<br>
	<a href="#pvoutput" id="joinWSLPVoTeam">I want to take place in the WSL PVoutput team</a>.
{{/each}}
