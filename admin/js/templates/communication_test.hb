<form id="frm_comm_test">
  <fieldset>
    <legend>Test this communication on a device</legend>
    <select id="deviceSelect" name="device" style="width:148px;">       	
   		<option value="-1">Select an device</option>
       	{{#devices}}
       		<option value="{{this.id}}">{{this.name}}</option>
    	{{/devices}}
    </select>
	<div id="device_info" style="display:none; margin-top: 10px;">
		<form>
			<input type="hidden" name="deviceId" id="device_id" />
			<input type="hidden" name="communicationId" value="{{communicationId}}" />
			<span style="width: 6em; display:inline-block; font-weight: bold;">name</span><input type="text" id="device_name" name="name" disabled="disabled"/><br />
			<span style="width: 6em; display:inline-block; font-weight: bold;">address</span><input type="text" id="device_address" name="address" disabled="disabled"/><br />
			<span style="width: 6em; display:inline-block; font-weight: bold;">device API</span><span id="device_api" /><br />
			<button type="button" id="btnTestCommunication">Start test</button>
		</form>
	</div>
  </fieldset>
</form>

<div id="test_results" style="margin-top: 10px;">
</div>
<div style="margin-top: 10px; margin-bottom: 10px">
Be aware that it can take a few minutes before testresults are shown.<br />
You do not need to keep waiting on this page. Just come back after a few minutes to see the results.<br />
If an device goes offline when the sun is down, then you will receive an failure also!.
</div>
<div style="margin-top: 10px;" id="lastTestInfo" >
	<span style="width: 6em; display:inline-block; font-weight: bold;">test time:</span><span id="lastTestTime"/><br />
	<span style="width: 6em; display:inline-block; font-weight: bold;">test result:</span><span id="lastTestResult"/><br />
	<span style="width: 6em; display:inline-block; font-weight: bold;">result data:</span><br />
	<pre id="lastTestData" style="border: 1px solid #4B8902; padding: 10px;"> <pre><br />
</div>

