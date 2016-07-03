<form>
  <input type="hidden" name="s" value="XXXXX" />
  <fieldset>
    <legend>Dropbox</legend>
    {{#if_eq data.available compare=false}}
    {{#if_eq data.sqlEngine compare=sqlite}}
    We need to connect Dropbox with WebSolarLog before we can store your data in the cloud.<br>
    Click the link below and follow the steps:<br> 
   <a href="admin-server.php?s=attachDropbox" id="linkAttachDropbox">Attach WebSolarLog to your Dropbox.</a>
   {{else}}
    Unfortunately we only support SQLite database backups.
    {{/if_eq}}
    {{/if_eq}}
    {{#if_eq data.authTokenAvailable compare=true}}
    Something is wrong...
    {{/if_eq}}
    <div id="backups"></div>
  </fieldset>
</form>