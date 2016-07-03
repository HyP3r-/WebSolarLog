<form>
  <input type="hidden" name="s" value="save-pvoutput" />
  <input type="hidden" name="id" value="{{data.inverterid}}" />  
  <fieldset>
    <legend>Communication</legend>
    <label for="pvoEnabled">Enabled:</label>
    {{checkboxWithHidden 'pvoEnabled' data.pvoEnabled}}<br />
    <label for="pvoApiKey">Api key:</label><input type="text" name="pvoApiKey" value="{{data.pvoApiKey}}" /><br />
    <label for="pvoSystemId">System id:</label><input type="text" name="pvoSystemId" value="{{data.pvoSystemId}}" /><br />
    <button type="button" id="btnPvOutputSubmit">Save</button>
  </fieldset>
</form> 	