<br/>
<form id="frm_comm_edit">
  <input type="hidden" name="s" value="save-communication" />
  <input type="hidden" name="id" value="{{data.id}}" />
  <fieldset>
    <legend>Communication: {{data.name}}</legend>
    <label for="name">name:</label><input type="text" class="commTextField" name="name" value="{{data.name}}" /><br />
    <label for="uri">uri:</label><input type="text" class="commTextField" name="uri" value="{{data.uri}}" /><br />
    <label for="port">port:</label><input type="text" class="commTextField" name="port" value="{{data.port}}" /><br />
    <label for="timeout">timeout:</label><input type="text" class="commTextField" name="timeout" value="{{data.timeout}}" /><br />
    <label for="optional">optional:</label><input type="text" class="commTextField" name="optional" value="{{data.optional}}" /><br />
    
        <div class="span-20 first">
        	 <button type="button" id="btnCommunicationSubmit">Save</button>
        </div>
        {{#if_gt data.id compare=0}}
        <div class="span-6 last">
        	{{checkboxWithHidden 'btnCommunicationDelete' '' data.id}}&nbsp;remove<br />
        </div>
        {{/if_gt}}
  </fieldset>
</form> 