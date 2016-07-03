<h2>History {{monthName month}} {{year}}</h2>
<table>
	<thead>
		<tr>
			<th width="50">Day</th>
			<th width="50">Kwh</th>
			<th width="50">Device</th>
			<th width="70" align="center">Actions</th>
	</thead>
	<tbody>
{{#each data}}
		<tr>
			<td align="right">{{@key}}</td>
			<td align="right">{{this.energy.KWH}}</td>
			<td align="right">{{this.deviceHistory.amount}}</td>
			<td align="center"><a href="#" class="btn btnYieldDayEdit" data-energy-id="{{this.energy.id}}" data-day="{{@key}}">edit</a></td>
		</tr>
{{/each}}
	</tdbody>
</table>