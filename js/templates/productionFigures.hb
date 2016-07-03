<div style="display:table-cell;">
<div id="columns" style="display:table-cell; width:100%;">
<div id="todayPosts">
<div class="columns">
<h3>Production figures</h3>
<div class="col posts">
<div class="column span-36">
<div class="column span-3 first">{{lang.month}}</div>
<div class="column span-5" style="text-align:right;">{{lang.expected}}</div>
<div class="column span-5" style="text-align:right;">{{lang.harvested}}</div>
<div class="column span-5" style="text-align:right;">{{lang.difference}}*</div>
<div class="column span-5" style="text-align:right;">{{lang.cum}} {{lang.expected}}</div>
<div class="column span-5" style="text-align:right;">{{lang.cum}} {{lang.harvested}}</div>
<div class="column span-5 last" style="text-align:right;">{{lang.cum}} {{lang.difference}}</div>
<div class="cl"></div>
{{#each data}}
{{#each this}}
<div class="column span-3 first">{{timestampDateFormat this.date format="MM-YYYY"}}</div>
<div class="column span-5" style="text-align:right;">{{this.exp}}</div>
<div class="column span-5" style="text-align:right;">{{this.har}}</div>
<div class="column span-5" style="text-align:right;">{{this.diff}}</div>
<div class="column span-5" style="text-align:right;">{{this.cumExp}}</div>
<div class="column span-5" style="text-align:right;">{{this.cumHar}}</div>
<div class="column span-5 last" style="text-align:right;">{{this.cumDiff}}</div>
{{/each}}{{/each}}
</div>
</div>
<div class="cl"></div>
<h3></h3>
*{{difference}}={{lang.harvested}}-{{lang.expected}}
</div>
</div>
</div>
</div>