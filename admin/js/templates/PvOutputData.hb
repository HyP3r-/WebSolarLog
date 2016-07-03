 <div class="column span-36">
   Here you see the history records for "<strong>{{data.date}}</strong>" that needs to be sent to PVoutput.<br>
   Depending on the history interval of this device, you will see 1 record for every 4-15 min.<br>
   <br>
   <div class="column span-37"> 
   <div class="column span-30" id="pickerFilter" name="pickerFilter"></div>
    <div class="column span-7" style="font-weight:800;">Record timestamp</div>
	<div class="column span-5" style="font-weight:800;">Sendable record</div>
	<div class="column span-7" style="font-weight:800;">recieved state</div>
	<div class="column span-7" style="font-weight:800;">last try to sent</div>
	<div class="column span-2" style="font-weight:800;">state</div>
{{#if_gt data.recordCount compare=0}}   
{{#each data}}
	{{#each this}}
		 <div class="tr1 column span-31">{{PvOutputDataRow this}}</div>
	{{/each}}
{{/each}}
{{else}}
 <div class="column span-30">no records found for this day.</div>
{{/if_gt}}
</div></div>
<script>$(function() {
$("div.tr1:even").css('background-color','#ddd');
$("div.tr2:even").css('background-color','#ddd');
$("div.tr3:even").css('background-color','#ddd');
});</script>