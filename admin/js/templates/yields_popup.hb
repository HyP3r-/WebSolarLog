<div id="yieldsEditDialog" title="Edit history">
	<p>Change the new kWh in the desired value and press save.</p>
	<form>
	<input type="hidden" id="yield_energyID" value="" />
	<span style="width: 7em; display:inline-block; font-weight: bold;">current kWh</span><input type="text" size="6" id="yield_current_kwh" disabled="disabled"/><br />
	<span style="width: 7em; display:inline-block; font-weight: bold;">device kWh</span><input type="text" size="6" id="yield_device_kwh" disabled="disabled"/><br />
	<span style="width: 7em; display:inline-block; font-weight: bold;">new kWh</span><input type="text" size="6" id="yield_new_kwh" /><br />
	<br /><br />
	<button type="button" id="btn_yield_save">Save</button>
	</form>
</div>
<div id="yieldsAddDialog" title="Add history">
	<p>Change the Date and the kWh in the desired values and press save.</p>
	<form>
	<span style="width: 7em; display:inline-block; font-weight: bold;">Date</span><input type="text" size="10" id="yield_add_time" /><br />
	<span style="width: 7em; display:inline-block; font-weight: bold;">kWh</span><input type="text" size="10" id="yield_add_kwh" /><br />
	<br /><br />
	<button type="button" id="btn_yield_add">Save</button>
	</form>
</div>

<script>
$(function() {
	$( '#yieldsEditDialog, #yieldsAddDialog' ).dialog({
		autoOpen: false,
		modal: true,
		show: {
			effect: "blind",
			duration: 250
		},
		hide: {
			effect: "explode",
			duration: 250
		}
	});
	
	$('#yield_add_time').datepicker().datepicker( "option", "dateFormat", "yy-mm-dd" );
});
</script>