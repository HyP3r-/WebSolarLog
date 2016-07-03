<h4 style="color: red">This is still being tested, make a backup before editing!</h4><br /><br />
<select id="yield_inverter_select">
</select>
<br />
<br />
<a href="#" class="btn btnYieldDayAdd">add new</a><br />
<br />
<ul>
{{#each data}}
	<li>
		{{@key}}
		<ul data-year-id="{{@key}}">
			{{#each this}}
				<li><a href="#{{@key}}" class="btnYieldMonth" data-month-id="{{@key}}">{{monthName @key }}</a></li>
			{{/each}}
		</ul>
	</li>
{{/each}}
</ul>