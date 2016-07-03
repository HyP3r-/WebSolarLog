<br>
<br>
<br>
<br>

<font color="#ff0000;">Recently we made a high impact change on the way WSL is configured to communicate with external devices.<br>
Please see Admin::Communication and this forum post for more info:</font><br>
<a href="https://groups.google.com/d/msg/websolarlog/OFzkDKLSvcM/YKnpGmxGo_4J" target="_blank">
WebSolarLog Group:<br>New communication manager is now active</a>
<br>
<br>
<br>
<br>
<br>
Only click the button below if you understand the Beta/Trunk risks!<br>
================================
<button id="understand">I understand and want to &#x00A;see the update options!</button>
<br>
<br>

<div id="showUpdateOption">
<p>By selecting the experimantal versions, you will be able to choose unstable releases.
So only use this if you know what you are doing.</p>
<br/>
<input type="checkbox" id="chkBeta" value="1" {{#if beta}}checked='checked'{{/if}}/>&nbsp;<b>Show Beta versions<b><br />
<span style="color:#ff0000">Not supported!</span>
<br/><br/>
<input type="checkbox" id="chkExperimental" value="1" {{#if experimental}}checked='checked'{{/if}}/>&nbsp;<b>Show Trunk versions<b><br />
<span style="color:#ff0000">Not supported!</span>
<br/><br/>
<hr>
{{checkboxWithHidden 'chkNewTrunk' chkNewTrunk}}&nbsp;<b>Notify me on new trunk releases:<b>

<script type="text/javascript">
$('#showUpdateOption').hide();
$( "#understand" ).on('click',function(){
$('#showUpdateOption').show();
});
</script>