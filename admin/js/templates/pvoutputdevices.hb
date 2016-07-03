{{#each data.devices}}
	<div class="column span-6">{{this.name}}&nbsp;</div><div class="column span-6">
	{{#if_eq this.pvoutputWSLTeamMember compare=true}}
	joined</div> <a href="#social" id="deviceTeamState-{{this.id}}-0"><img src="images/link_delete.png" class="icon"> leave team</a><br>
	{{else}}
	not joined</div><a href="#social" id="deviceTeamState-{{this.id}}-1"><img src="images/link.png" class="icon"> join team</a><br>
	{{/if_eq}}
{{else}}
	There are no devices that can connect to PVoutput.
{{/each}}
