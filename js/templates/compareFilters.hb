<div id="compareFilter">
    <table>
    	<tr>
    		<td>{{lang.inverter}}:</td>
	    	<td>
	    		<select id="devicenum">
	    			{{#each data.dayData.inverters}}
	    			<option value="{{this.id}}">{{this.name}}</option>
	    			{{/each}}
	    		</select>
	    	</td>
	    	</tr><tr>
    		<td>{{lang.compare}}:</td>
    		<td>
	    		<select id="whichMonth">
	    			{{#each data.dayData.month}}
	    			<option value="{{this.number}}">{{this.name}}</option>
	    			{{/each}}
	    		</select>/
	    		<select id="whichYear">
	    			{{#each data.dayData.year}}
	    			<option value="{{this.date}}">{{this.date}}</option>
	    			{{/each}}
	    		</select>
	    	</td>
    		<td colspan="2">{{lang.to}}:</td>
	    	<td>
	    		<select id="compareMonth">
	    			{{#each data.dayData.month}}
	    			<option value="{{this.number}}">{{this.name}}</option>
	    			{{/each}}
	    		</select>/
	    		<select id="compareYear">
	    			<option value="0">{{lang.expected}}</option>
					{{#each data.dayData.year}}
	    			<option value="{{this.date}}">{{this.date}}</option>
	    			{{/each}}
	    		</select>
	    	</td>
		</tr>
    </table>
</div>
