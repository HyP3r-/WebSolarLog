<form>
  <fieldset>
    <legend>Twitter</legend>
    <label for="Twitter">Twitter:</label><input type="text" name="Twitter" value="{{data.social.TwitterDisplayName}}" disabled="disabled"/>
    <span id="twitter">
	{{#if_eq data.social.TwitterAttached compare="1"}}
		<br />
		<div class="column span-6" >
			<a href="https://www.twitter.com/{{data.social.TwitterDisplayName}}" target="_blank" title="Go to my Twitter page"><img src="images/link.png" class="icon">We are connected</a>
		</div>
		<div class="column span-6">
			<a href="#social" id="sendTweet"><img src="images/email_go.png" class="icon">Send a test Tweet</a>
		</div>
		<div class="column span-14">
			<a href="#social" id="detachTwitter"><img src="images/link_delete.png" class="icon">Detach from my Twitter account</a>
		</div>
	{{else}}
	<a href="admin-server.php?s=attachTwitter&refURL={{data.refURL}}" target="">Attach to my Twitter</a>
	{{/if_eq}}
	</div>
    </span>
    <br />
  </fieldset>
</form>
<form>

<fieldset>
<a name="pvoutput"/>
<legend>WebSolarLog PVoutput team</legend>
PVoutput has some restrictions for joining a team;<br>
- The system must have at least 5 outputs<br>
- The system cannot join more than 10 teams<br>
<br>
<strong>Team status of devices</strong>:<br>
<div id="devices">Loading device data....</div>
<hr>
Go to PVoutput and see our team achievements:<br>
<a href="http://pvoutput.org/listteam.jsp?tid=602" target="_blank">Go to WebSolarLog [at] PVoutput.org</a>
<hr>

<font style="color:red;">Note:</font><br>
PVoutput is a free of charge non-profit organisation.<br>
The keep there organisation up and running by donations.<br>
The would really appriciate a donation from you (we already did :) ).<br>
<a href="http://pvoutput.org/donate.jsp" target="_blank">Yes, I want to donate to PVoutput</a>.
</form>