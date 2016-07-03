<div id="right" style="height:170px;">
	<img src="images/counter.png" class="icon"><b>&nbsp;{{data.langCOUNTER}}</b><br/>
	<hr class="hr_info" />{{data.langTOTALPROD}} {{data.valueKWHP}} kWh<br />
	<img src='images/leaf.png' width='16' height='16' border='0' /> {{data.valueCO2}} {{data.valueCO2v}} CO<sub>2</sub>&nbsp;<img src='images/info10.png' width='10' height='10' border='0' title='{{data.langECOLOGICALINFOB}}' />
</div>
<div id="left" style="height:170px;">
	<img src="images/brightness.png" class="icon"><b>&nbsp;{{data.langPLANTINFO}}
	</b><br />
	<hr class="hr_info" />
	<a href="plantdetails.php"><imgsrc="images/zoom.png" width="32" height="32" border="0"> </a>
	{{data.langLOCATION}} {{data.valueLOCATION}}<br />
	{{data.langPLANTPOWER}}: {{data.valuePLANT_POWER}} W<br />
</div>
<div id="right" style="height:280px;">
	<img src="images/calendar-day.png"class="icon" /><b>&nbsp;{{data.langEVENTS}}</b><br />
	<hr class="hr_info" />
	<div id="events" class="events">
		{{#data.valueEvents}}
		{{this}}<br />
		{{/data.valueEvents}}
	</div>
</div>
<div id="left" style="height:280px;">
	<img src='images/monitor.png' class="icon" /><b>&nbsp;{{data.langINVERTERINFO}}</b>
	<img src='images/info10.png' width='10' height='10' border='0' title="{{data.langINVERTERINFOB}} {{data.valueUpdtd}}" /><br />
	<hr class="hr_info" />
	<ul>
	{{#data.valueInverter}}
	<li>{{this}}</li>
	{{/data.valueInverter}}
	</ul>
</div>
<div id="right" style="height:270px;">
	<img src='images/link.png' class="icon"/><b>&nbsp;PVoutput</b><br />
	<hr class="hr_info" />
	<br />
	<script src='http://pvoutput.org/widget/inc.jsp'></script>
	<script src='http://pvoutput.org/widget/graph.jsp?sid={{data.valueSYSID}}&w=200&h=80&n=1&d=1&t=1&c=1'></script>
</div>
<div id="left" style="height:270px;">
<img src='images/Empty.png' class="icon"/><b>&nbsp;Empty</b><br />
<hr class="hr_info" />
	empty... system info?
</div>
