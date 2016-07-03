<div>We give NO guarantees for the figures, calculations, data etc. below. :) <br></div>
The data below is calculated over the period;<br>
<div class="column span-3">From:</div><div class="column span-5">{{data.invoiceStartDate}}</div><br>
<div class="column span-3">Till:</div><div class="column span-5">{{data.invoiceEndDate}}</div><br>
<br>
<div class="column span-4">Gas usage:</div>
<div class="column span-5" title="{{data.totals.gasUsageTTotal}} * {{data.moneySign}} {{data.costGas}} = {{data.totals.gasUsageTTotalCosts}}">{{toFixed value=data.totals.gasUsageTTotal fixed=3}} m3</div>
<br><br>
<div class="column span-4">Electric</div><br>
<!------------->
<!-- Table 1 -->
<!------------->
<div class="column span-4">High/Low</div><div class="column span-4">usage</div><div class="column span-1">&nbsp;</div><div class="column span-3">return</div><div class="column span-1">&nbsp;</div><div class="column span-3">total</div><br>
<div class="column span-4">High</div>
<div class="column span-4" title="{{data.totals.highUsageTTotal}} * {{data.moneySign}} {{data.costkwh}} = {{data.totals.highUsageTTotalCosts}}">{{data.totals.highUsageTTotal}}</div>
<div class="column span-1">-</div>
<div class="column span-3" title="{{data.totals.highReturnTTotal}} * {{data.moneySign}} {{data.costkwh}} = {{data.totals.highReturnTTotalCosts}}">{{data.totals.highReturnTTotal}}</div>
<div class="column span-1">=</div>
<div class="column span-3" title="{{data.totals.diffHigh}} * {{data.moneySign}} {{data.costkwh}} = {{data.totals.diffHighCosts}}">{{data.totals.diffHigh}}</div><br>

<div class="column span-4">Low</div>
<div class="column span-4" title="{{data.totals.lowUsageTTotal}} * {{data.moneySign}} {{data.costkwh}} = {{data.totals.lowUsageTTotalCosts}}">{{data.totals.lowUsageTTotal}}</div>
<div class="column span-1">-</div>
<div class="column span-3" title="{{data.totals.lowReturnTTotal}} * {{data.moneySign}} {{data.costkwh}} = {{data.totals.lowReturnTTotalCosts}}">{{data.totals.lowReturnTTotal}}</div>
<div class="column span-1">=</div>
<div class="column span-3" title="{{data.totals.diffLow}} * {{data.moneySign}} {{data.costkwh}} = {{data.totals.diffLowCosts}}">{{data.totals.diffLow}} +</div><br>

<div class="column span-4">&nbsp;</div>
<div class="column span-4">&nbsp;</div>
<div class="column span-1">&nbsp;</div>
<div class="column span-3">&nbsp;</div>
<div class="column span-1">&nbsp;</div>
<div class="column span-3"  title="{{data.totals.diffHighLowTotal}} * {{data.moneySign}} {{data.costkwh}} = {{data.totals.diffHighLowTotalCosts}}" style="border-top:1px solid #000000;">{{data.totals.diffHighLowTotal}}</div>
<br><br>
<!------------->
<!-- Table 2 -->
<!------------->
<div class="column span-4">Usage/Return</div><div class="column span-4">High</div><div class="column span-1">&nbsp;</div><div class="column span-3">Low</div><div class="column span-1">&nbsp;</div><div class="column span-3">total</div><br>
<div class="column span-4">Usage</div>
<div class="column span-4" title="{{data.totals.highUsageTTotal}} * {{data.moneySign}} {{data.costkwh}}= {{data.totals.highUsageTTotalCosts}}">{{data.totals.highUsageTTotal}}</div>
<div class="column span-1">+</div>
<div class="column span-3" title="{{data.totals.lowUsageTTotal}} * {{data.moneySign}} {{data.costkwh}}= {{data.totals.lowUsageTTotalCosts}}">{{data.totals.lowUsageTTotal}}</div>
<div class="column span-1">=</div>
<div class="column span-3" title="{{data.totals.usageTTotal}} * {{data.moneySign}} {{data.costkwh}}= {{data.totals.usageTTotalCosts}}">{{data.totals.usageTTotal}}</div><br>
<div class="column span-4">Return</div>
<div class="column span-4" title="{{data.totals.highUsageTTotal}} * {{data.moneySign}} {{data.costkwh}}= {{data.totals.highReturnTTotalCosts}}">{{data.totals.highReturnTTotal}}</div>
<div class="column span-1">+</div>
<div class="column span-3" title="{{data.totals.lowReturnTTotal}} * {{data.moneySign}} {{data.costkwh}}= {{data.totals.lowReturnTTotalCosts}}">{{data.totals.lowReturnTTotal}}</div>
<div class="column span-1">=</div>
<div class="column span-3" title="{{data.totals.returnTTotal}} * {{data.moneySign}} {{data.costkwh}}= {{data.totals.returnTTotalCosts}}">{{data.totals.returnTTotal}} -</div><br>

<div class="column span-4">&nbsp;</div>
<div class="column span-4">&nbsp;</div>
<div class="column span-1">&nbsp;</div>
<div class="column span-3">&nbsp;</div>
<div class="column span-1">&nbsp;</div>
<div class="column span-3" title="{{data.totals.diffReturnUsageTTotal}} * {{data.moneySign}} {{data.costkwh}}= {{data.totals.diffReturnUsageTTotalCosts}}" style="border-top:1px solid #000000;">{{data.totals.diffReturnUsageTTotal}}</div>
<br>
<div class="column span-28">
	<div class="column span-5">Time</div>
	<div class="column span-4">Gas</div>
	<div class="column span-3">low Return</div>
	<div class="column span-4">high Return</div>
	<div class="column span-3">low Usage</div>
	<div class="column span-4">high Usage</div>
</div>
<br>
{{#each data.days}}
<div class="column span-28" style="background-color:{{this.backgroundColor}}">
  <div class="column span-5" title="({{this.time}})">{{timestampDateFormat this.time format="DD-MM-YYYY "}}&nbsp;</div>
  <div class="column span-4" style="background-color:#9CB3FF;text-align:right;">{{toFixed value=this.gasUsageTDay fixed=3}}&nbsp;</div>
  <div class="column span-3" style="background-color:#70FF69;text-align:right;">{{toFixed value=this.lowReturnT fixed=3}}&nbsp;</div>
  <div class="column span-4" style="background-color:#70FF69;text-align:right;">{{toFixed value=this.highReturnT fixed=3}}&nbsp;</div>
  <div class="column span-3" style="background-color:#FF7D7D;text-align:right;">{{toFixed value=this.lowUsageT fixed=3}}&nbsp;</div>
  <div class="column span-4" style="background-color:#FF7D7D;text-align:right;">{{toFixed value=this.highUsageT fixed=3}}&nbsp;</div>
</div><br>
{{/each}}

<br>
<div class="column span-28">
	<div class="column span-5">Time</div>
	<div class="column span-4">Gas</div>
	<div class="column span-3">low Return</div>
	<div class="column span-4">high Return</div>
	<div class="column span-3">low Usage</div>
	<div class="column span-4">high Usage</div>
</div>
{{#each data.months}}
<div class="column span-28" style="background-color:{{this.backgroundColor}}">
<div class="column span-5" >{{@key}}&nbsp;</div>
<div class="column span-4" style="background-color:#9CB3FF;text-align:right;">{{toFixed value=this.gasUsageT fixed=3}}</div>
<div class="column span-4" style="background-color:#70FF69;text-align:right;">{{toFixed value=this.lowReturnT fixed=3}}</div>
<div class="column span-3" style="background-color:#70FF69;text-align:right;">{{toFixed value=this.highReturnT fixed=3}}</div>
<div class="column span-3" style="background-color:#FF7D7D;text-align:right;">{{toFixed value=this.lowUsageT fixed=3}}</div>
<div class="column span-4" style="background-color:#FF7D7D;text-align:right;">{{toFixed value=this.highUsageT fixed=3}}</div>
</div><br>
{{/each}}