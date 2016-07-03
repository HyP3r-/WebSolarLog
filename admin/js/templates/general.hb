<form>
  <input type="hidden" name="s" value="save-general" />
  <fieldset>
    <legend>Webpage</legend>
    <label for="title">title:</label><input type="text" name="title" value="{{data.title}}"/><br />
    <label for="subtitle">subtitle:</label><input type="text" name="subtitle" value="{{data.subtitle}}" /><br />
    <label for="url">url:</label><input type="text" name="url" value="{{data.url}}" /><br />
    <label for="location">location:</label><input type="text" name="location" value="{{data.location}}" /><br />
    <label for="latitude">latitude:</label><input type="text" name="latitude" value="{{data.latitude}}" /><br />
    <label for="longitude">longitude:</label><input type="text" name="longitude" value="{{data.longitude}}" /><a href="#" id="btnSetLatLong">}Set coordinates</a><br />
    <label for="currencySign">Currency Sign:</label>
    <select name="moneySign">
    	<option value="€">€</option>
    	<option value="$">$</option>
    	<option value="£">£</option>
    	<option value="元">元</option>
    	<option value="Kč">Kč</option>
    	<option value="kr">kr</option>
    	<option value="Ft">Ft</option>
    	<option value="₨">₨</option>
    	<option value="Rp">Rp</option>
    	<option value="₪">₪</option>
    	<option value="¥">¥</option>
    	<option value="J$">J$</option>
    	<option value="R$">R$</option>
    	<option value="Ls">Ls</option>
    	<option value="Lt">Lt</option>
    	<option value="RM">RM</option>
    	<option value="﷼">﷼</option>
    	<option value="B/.">B/.</option>
    	<option value="Ph"Ph></option>
    	<option value="le">le</option>
    	<option value="ру,;">ру,;</option>
    	<option value="₩">₩</option>
    	<option value="CHF">CHF</option>
    	<option value="฿">฿</option>
    	<option value="YTL">YTL</option>
    	<option value="Bs">Bs</option>
    	<option value="R">R</option>
    </select><br>
    <label for="gaugeMax">Gauge Max Type:</label>
    <select name="gaugeMaxType">
      <option value="panels" {{#if_eq data.gaugeMaxType compare="panels"}}selected=selected{{/if_eq}}>Static(panels)</option>
      <option value="average" {{#if_eq data.gaugeMaxType compare="average"}}selected=selected{{/if_eq}}>Dynamic(avg. power)</option>
    </select><br />
    <label for="timezone">timezone: </label>
    <select name="timezone">
    	{{#each data.timezones}}
    		<option value="{{this}}" {{#if_eq ../data.timezone compare=this}}selected=selected{{/if_eq}}>{{this}}</option>
    	{{/each}}
    </select><br />
    <label for="frontLivePoll">Frontend live poll to database:</label>
    <input type="text" size="5" name="frontendLiveInterval" id="frontendLiveInterval" value="{{data.frontendLiveInterval}}" />seconds
    <div id="sliderFrontendLiveInterval" class="span-28"></div><br>
    <button type="button" id="btnGeneralSubmit">Save</button>
  </fieldset>
</form> 
{{#if_gt data.meteringDevicePresent compare=0}}
  <fieldset>
  <input type="hidden" name="s" value="save-invoiceDate" />
  <legend>Invoice</legend>
  <form name="invoice">
  <input type="text" id="date" name="date" value="{{data.invoiceDate}}"/> (dd-mm-yyyy)<br>
  <button type="button" id="btnInvoiceData">Show invoice</button>
  </form>
  </fieldset>
  {{/if_gt}}