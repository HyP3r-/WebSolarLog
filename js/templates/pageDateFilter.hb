devices:
<select id="devicenum">
{{#each data.slimConfig.devices}}<option value="{{this.id}}">{{this.name}}</option>{{/each}}
</select>
{{lang.periode}}:<input type="text" id="datePickerPeriod" style="z-index: -1000;">