<div class="widget">
<div style="height:190px;">
<br>
<h3>Weather</h3>
<div  class="column span-13">
{{#each data}}


<div class="cl"></div>
<div  class="column span-13">Temperature</div>
<div class="cl"></div>
<div class="column span-4 last"><a href="#">Current(°C):</a></div>
<div class="column span-4 last"><a href="#">Minimum(°C):</a></div>
<div class="column span-4 last"><a href="#">Maximum(°C):</a></div>
<div class="cl"></div>
<div class="column span-4 last" style="">{{this.data.temp}}</div>
<div class="column span-4 last" style="">{{this.data.temp_min}}</div>
<div class="column span-4 last" style="">{{this.data.temp_max}}</div>
<div class="cl"></div>
<div  class="column span-13"><br /></div>
<div  class="column span-13">Wind</div>
<div class="column span-4 last"><a href="#">Direction(°):</a></div>
<div class="column span-4 last"><a href="#">Speed(m/s):</a></div>
<div class="column span-4 last">&nbsp;</div>
<div class="cl"></div>
<div class="column span-4 last" style="">{{this.data.wind_direction}}</div>
<div class="column span-4 last" style="">{{this.data.wind_speed}}</div>
<div class="column span-4 last">&nbsp;</div>
<div class="cl"></div>
<div  class="column span-13"><br /></div>
<div  class="column span-13">Misc</div>
<div class="column span-4 last"><a href="#">pressure(hPa):</a></div>
<div class="column span-4 last"><a href="#">humidity(%):</a></div>
<div class="column span-4 last"><a href="#">clouds(%):</a></div>
<div class="cl"></div>
<div class="column span-4 last" style="">{{this.data.pressure}}</div>
<div class="column span-4 last" style="">{{this.data.humidity}}</div>
<div class="column span-4 last" style="">{{this.data.clouds}}</div>
<div class="cl"></div>
<div class="column span-3 first"><a href="#">Time:</a></div>
<div class="column span-3 first" style="text-align:right;">{{timestampDateFormat this.data.time format="HH:mm:ss"}}</div>
{{/each}}
</div>
</div>