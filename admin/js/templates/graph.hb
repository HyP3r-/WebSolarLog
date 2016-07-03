<div id="graphFormId">
  <fieldset style="width:560px;">
   <legend>{{capitalizer title=data.inverter.type}} graph: {{data.name}}</legend>
  		<font style="color:red;font-size:15px;">!This page is under construction!
Do not (NOT!), change anything!</font> 
	    <div class="cl"></div>
		<strong>Graph layout</strong>:<br>
		<div class="column" style="width:554px;text-align:center;border:1px solid #ddd;">x2axis</div>
		<div class="column" style="width:556px;">
		<div class="column last" style="width:80px;border:1px solid #ddd;height:75px;">
		<div style="margin-left:auto;margin-right:auto;width:40px;">left<br>yaxis</div></div>
		<div class="column last" style="width:250px;border:1px solid #ddd;height:75px;">
		<div style="margin-left:auto;margin-right:auto;width:110px;"><br>graph lines section</div></div>
		<div class="column last" style="width:220px;border:1px solid #ddd;height:75px;">
		<div style="margin-left:auto;margin-right:auto;width:60px;">right<br>y(2-8)axis</div></div>
		<div class="column" style="width:554px;text-align:center;border:1px solid #ddd;">xaxis</div>
		</div>
			<div class="column span-28" id="axes">
				<div class="column span-8 first">Location</div>
				<div class="column span-6">Axe</div>
				<div class="column span-8">description</div>
				<div class="column span-4">..</div>
			 </div>
{{#data.axes}}
<div class="column span-28" >
	    			<form method="post" id="axe-{{this.id}}">
	    			<input type="hidden" name="graphName" value="{{../data.name}}">
	    			<input type="hidden" name="id" value="{{this.id}}">
	<div class="column span-0 first" style="display:none;">{{this.id}}</div>
	<div class="column span-8 first name">{{this.name}}</div>
	<div class="column span-6 axe">{{this.axe}}</div>
	<div class="column span-8"><input type="text" style="width:160px" name="label"  class="label" value="{{this.json.label}}"></div>
	<div class="column span-1"><input type="image" class="saveAxe" src="../admin/images/disk.png"/></div>
</form>
</div>
{{/data.axes}}
<div class="column span-28" id="series">
<br>
<strong>Config Series:</strong>
</div>
		<div class="column span-28">
			<div class="column span-30 first">
			<div class="column span-8 first">Serie</div>
			<div class="column span-8 first">@ axe</div>
			<div class="column span-4 first">load line:</div>
			<div class="column span-5 first">default hide line:</div>						
							
				<ul>    
	    			{{#data.series}}
	    			<form id="serie-{{this.id}}">
	    			<input type="hidden" name="graphName" value="{{../data.name}}">
	    			<input type="hidden" name="id" value="{{this.id}}">
						<div class="column span-8 first" style="overflow:hidden;">{{this.name}}</div>
						<div class="column span-8 first">
						<select name="yaxis" class="saveSerie" width="60" style="width: 60px"> 
						    {{#../data.axesList}}
								 <option value="{{this.axe}}" {{#if_eq this.axe compare=../this.json.yaxis}}selected{{/if_eq}}>{{this.axe}}&nbsp;&nbsp;&nbsp;({{this.label}})</option>
							{{/../data.axesList}}
							</select>
						</div>
						<div class="column span-4 first">
							{{checkboxWithHidden 'serieHidden' this.show '' 'saveSerie'}}
						</div>
						<div class="column span-5 first">
							{{checkboxWithHidden 'serieVisible' this.disabled '' 'saveSerie'}}	
						</div>
						
						</form>				
					{{/data.series}}
				</ul>
			</div>
		<div>
  </fieldset>
</div> 