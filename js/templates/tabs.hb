<!-- Slider --><div id="tabs" class="tabs-bottom" style="height:395px;width:917px">
<ul>{{#data.tabs}}<li class="ui-corner-bottom"><a href="#tabs-{{this.position}}" id="tab-{{this.position}}" name="tab-{{this.graphName}}">{{this.translation}}</a></li>{{/data.tabs}}</ul>
<div id="pickerFilter" style="height:20px;"></div>
{{#data.tabs}}
<div id="tabs-{{this.position}}" class="mainTabContainer"  style="height:330px;width:880px">
	<div id="graph{{this.graphName}}">
		<div id="graph{{this.graphName}}Content" style="height:340px; width:925px;" >
		</div>
	</div>
</div>
{{/data.tabs}}
</div><!-- END Slider -->