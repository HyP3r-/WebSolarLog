<form id="expectationFormId"><fieldset>
   <legend>System Expectation</legend>
	<div>
        <input type="hidden" name="s" value="save_expectation" />
        <input type="hidden" name="id" value="{{data.inverterId}}" />
        Below you can enter the kWh predition through a year.<Br>
        <br>
        What is the kWh predicted for this system?<br>
        kWh a year:<input name="totalProdKWH" class="monthProd" id="totalKWHProd" size="10" value=""><br>
        <br>
        Here you can enter the kWh prediction for each month.<br>
        Please enter the month values in kWh, the percentage values will be calculated for you.<Br>
        When you enter a month value, the bar height of that month will change.<Br>
        <div id="expectation">    
            <div class="expectation_row">
                <div class="expectation_cell monthName">&nbsp;</div>
                {{#data.month_perc}}    
                    <div id="month{{this.month}}" class="expectation_cell  monthName">{{this.month}}</div>
                {{/data.month_perc}}
            </div>    
    
            <div class="expectation_row">
                <div class="expectation_cell  monthProd">kWh</div>
                {{#data.month_perc}}    
                    <div class="expectation_cell  monthProd"><input id="{{this.month}}KWH" value=""></div>
                {{/data.month_perc}}    
            </div>
            <div class="expectation_row">
                <div class="expectation_cell monthProd">%</div>
                {{#data.month_perc}}
                <div class="expectation_cell  monthProd"><input class="monthProd" name="{{this.month}}PER" id="{{this.month}}PER" readonly="true" value="{{this.perc}}"></div>
                {{/data.month_perc}}
            </div>
        </div>
	    <br>
	    <div id="expectation_graph">
	    	<span style="font-weight: bold;">Graph</span><br>
	    	This is a graphical display of the aboves figures.
	    	 <div style="clear: both"></div>
	    	{{#data.month_perc}}
	    	<p class="monthBAR">
	    		<img class="monthIMG" id="{{this.month}}BAR" src=""/>
	    	</p>
	    	{{/data.month_perc}}            
	    	<div style="clear: both"></div>
	    	{{#data.month_perc}}
	    		<p class="monthBAR_label">
	    			{{this.month}}
	    		</p>
		    {{/data.month_perc}} 
	       <div style="clear: both"></div>
	        <button type="button" id="btnExpectationSubmit">save</button>
	        <button type="button" id="btnExpectationDefault">default</button>
	        <div style="cl"></div>
	            
		</div>
	</fieldset>
</form>  