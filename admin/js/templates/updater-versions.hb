<br /><br />
<h3>Info about your current version:</h3>
<br />
After an update WSL will perform a soft-restart.<br>
Therefore it could take 5-10 min. before all changes will take effect.<br>
<br>
		<div class="span span-30">

			<ul style="list-style-type: none;"><li>
				<ul>
				<li>Name:<br>{{data.currentVersionTitle}}</li>
				<li>Release date(revision):</li>
				<li style="list-style-type: none;">{{data.currentVersionReleaseTime}}({{data.currentVersionRevision}})</li>
				<li>You updated on:</li>
				<li style="list-style-type: none;">{{data.currentVersionUpdateTime}}</li>
				<li>Description:</li><li style="list-style-type: none;">{{data.currentVersionReleaseDescription}}</li></ul></ul>
				</li></ul></div>
<h3>We found the following available versions:</h3>
<form>
	{{updaterVersionsList data.versions}}
    <br />
    <p>When selecting the button below WebSolarLog will be updated to the version selected.</p>
    <br />
    <input type="hidden" name="s" value="updater-go" /> 
    <button type="button" id="btnUpdateSubmit">Update</button>
</form>
<div id="update-console"></div>