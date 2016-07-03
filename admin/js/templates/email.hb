<form>
  <input type="hidden" name="s" value="save-email" />
  <fieldset>
    <legend>eMail</legend>
    <label for="emailTo">to:</label><input type="text" name="emailTo" value="{{data.emailTo}}" />{{infoTooltip title="To send emails to multiple recipients, seperate addresses with ; <br /><br />example:<br />test@test.com;example@example.com"}}<br />
    <label for="emailFromName">from name:</label><input type="text" name="emailFromName" value="{{data.emailFromName}}" /><br />
    <label for="emailFrom">from address:</label><input type="text" name="emailFrom" value="{{data.emailFrom}}" /><br />
    <label for="emailAlarms">Receive alarms:</label>
    {{checkboxWithHidden 'emailAlarms' data.emailAlarms}}<br />
    <label for="emailEvents">Receive events:</label>
    {{checkboxWithHidden 'emailEvents' data.emailEvents}}<br />
    <label for="emailReports">Receive reports:</label>
    {{checkboxWithHidden 'emailReports' data.emailReports}}<br />
    <button type="button" id="btnEmailSubmit">Save</button>
  </fieldset>
</form>
<form>
  <input type="hidden" name="s" value="save-smtp" />
  <fieldset>
    <legend>Outgoing mail server</legend>
    <label for="smtpServer">server:</label><input type="text" name="smtpServer" value="{{data.smtpServer}}" /><br />
    <label for="smtpPort">port:</label><input type="text" name="smtpPort" value="{{data.smtpPort}}" /><br />
    
    <label for="smtpSecurity">Security:</label>
    <select name="smtpSecurity">
      <option value="none" {{#if_eq data.smtpSecurity compare="none"}}selected=selected{{/if_eq}}>None</option>
      <option value="ssl" {{#if_eq data.smtpSecurity compare="ssl"}}selected=selected{{/if_eq}}>SSL</option>
      <option value="tls" {{#if_eq data.smtpSecurity compare="tls"}}selected=selected{{/if_eq}}>TLS</option>
    </select><br />
    
    <label for="smtpUser">Username:</label><input type="text" name="smtpUser" value="{{data.smtpUser}}" />(optional)<br />
    <label for="smtpPassword">Password:</label><input type="password" name="smtpPassword" value="{{data.smtpPassword}}" />(optional)<br />
    <button type="button" id="btnSmtpSubmit">Save</button>
  </fieldset>
</form>
<p>
By pressing the button below we will use the saved settings to send an email.<br />
So be sure to save first then use this button.<br />
<form>
    <input type="hidden" name="s" value="send-testemail" />
    <button type="button" id="btnEmailTest">Send an test email</button>
</form>
</p>