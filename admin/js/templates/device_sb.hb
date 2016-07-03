{{#data.inverters}}
<button type="button" id="btnInverter_{{this.id}}" class="inverter_select" style="width:100%;">{{this.name}}</button><br/>
{{/data.inverters}}
<br />
<hr>

<form>
	<strong>Create a new device</strong><br>
	Select device:
	<select name="createDevice" id="createDevice" style="width:100%;">
		{{#data.supportedDevices}}
			<option value="{{this.value}}_{{this.type}}"  class="inverter_select">{{this.name}}</option>
		{{/data.supportedDevices}}
	</select>
	<br><br>
	<button type="button" id="new_device" style="width:100%;">Create device</button><br/><br/>
</form>

WSL supports:<br>
<br>
Solar inverters:<br>
<div class="span span-16">
<ul>
	<li>MasterVolt (TESTING)</li>
	<li>Diehl (Ethernet)</li>
	<li>PowerOne (Aurora RS485)</li>
	<li>SMA (SMA-get RS485)</li>
	<li>SMA (SMA-spot BlueTooth)</li>
	<li>Soladin (Solget,TESTING)</li>
	<li>Omnik (TESTING)</li>
</ul>
<br>
Metering devices:<br>
<ul>
	<li>Dutch Smart Meter (P1 serial)</li>
	<li>Dutch Smart Meter (Remote IP)</li>
	<li>Ampy Smart Meter (Remote IP)</li>
</ul>
<br>
Online services:<br>
<ul>
	<li>OpenWeatherMap(www connection required)</li>
</ul></div>