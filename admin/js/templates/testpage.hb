<h1> page</h1>
<form>
<fieldset>
    <legend>Diagnostics Page:</legend>
We have ran some test for you, so you can check/debug your WebSolarLog installation<br>
<br>
Need help or do you have a question;<br>
<a href="https://groups.google.com/forum/#!forum/websolarlog" target="_blank">Visit our WebSolarLog support-group</a><br>
<br>
When you want to report a bug of have a feature request;<br>
<a href="http://tracker.websolarlog.com" target="_blank">http://tracker.websolarlog.com</a><br>
 
</fieldset>
</form>
<form>
<fieldset>
    <legend>WSL running state:</legend>
		<label>PID file:</label>{{#if data.test.pid.exists}}exists{{else}}doesn't exists{{/if}}<br />
		<label>WSL running state:</label>{{#if data.test.pid.WSLRunningState}}running{{else}}not running{{/if}}<br />
		<label>Currenttime:</label>{{timestampDateFormat data.test.currentTime format="DD-MM-YYYY HH:mm:ss "}}<br />
		<label>last change:</label>{{timestampDateFormat data.test.pid.ctime format="DD-MM-YYYY HH:mm:ss "}}<br />
<div class="cl"></div>
{{#if data.test.pid.WSLRunningState}}
<font style="color:#FFCC00">WebSolarLog is running.</font><br />Please run the following command on the prompt of your Linux system to start WebSolarLog:<br />
If you want to stop WebSolarLog run:<br>
<font style="color:#4B8902">sudo {{data.test.commands.stop}}</font><br>
If you want to restart(stop/start) WebSolarLog run:<br>
<font style="color:#4B8902">sudo {{data.test.commands.restart}}</font><br>
If you want to know the running state of WebSolarLog:<br>
<font style="color:#4B8902">sudo {{data.test.commands.status}}</font><br>
{{else}}
<font style="color:#FFCC00">WebSolarLog is not running.</font><br />Please run the following command on the prompt of your Linux system to start WebSolarLog:<br />
<font style="color:#4B8902">sudo {{data.test.commands.start}}</font><br>
or atleast check the running state with:<br>
<font style="color:#4B8902">sudo {{data.test.commands.status}}</font><br>
{{/if}}
<br>
</fieldset>
</form>
<form>
<fieldset>
    <legend>DB {{data.test.db.sqlEngine}}:</legend>
    	Let take a look at your <font style="color:#4B8902">{{data.test.db.sqlEngine}}</font> Database;<br>
    	<strong>DSN:</strong><br>
    	We use the following DSN to talk to the DB, please check if this looks good to you;
    	{{data.test.db.dsn}}<br>
    	
    	{{#if_eq data.test.db.sqlEngine compare="sqlite"}} 
    	<br>
    	<strong>Exists?</strong><br>
    	<label>Does the SQLite DB exists?</label><br>
    	{{#if data.test.db.exists}}
    	It looks like the DB exist<br /><font style="color:#4B8902">This is good.<br></font>
    	{{else}}
    	It looks like the DB doesn't exist<br /><font style="color:#FF0000">This is not good.<br></font>
    	{{/if}}
    	<br>
    	<strong>Is the DB changing?</strong><br>
		<label>Currenttime:</label>{{timestampDateFormat data.test.currentTime format="DD-MM-YYYY HH:mm:ss "}}<br />
		<label>last change:</label>{{timestampDateFormat data.test.db.ctime format="DD-MM-YYYY HH:mm:ss "}}<br />
		<label>DB changed(<{{data.test.db.margin}} sec):</label>{{#if data.test.db.dbChanged}}Within the {{data.test.db.margin}} sec.{{else}}Longer then {{data.test.db.margin}} sec. ago.{{/if}}<br />
		{{#if data.test.db.dbChanged}}
    	<font style="color:#4B8902">This is good.<br>
    	It looks like the WebSolarLog process is running.<br>
    	See above for the start/stop/restart/status commands.<br>
    	</font>
    	{{else}}
    	<font style="color:#FF0000">This is not good.<br>
    	Try to (re)start WebSolarLog.
    	WebSolarLog runs on a continuous process.<br>
    	Therefore we should see atleast every 10 seconds a Database change.<br>
    	See above for the start/stop/restart/status commands.<br>
    	</font>
    	{{/if}}
		<br>
		What are the DB permissions on the filesystem?<br>
		{{#each data.test.dbRights.rights}}
		<div class="column span-18">
		<div class="column span-3 first"><strong>{{@key}}</strong></div>
		<div class="column span-3">{{this.[0]}}</div>
		<div class="column span-3">{{this.[1].[1]}}</div>
		<div class="column span-3">{{this.[2].[1]}}</div>
		<div class="column span-3 last">{{this.[3].[1]}}</div>
		</div>
		{{/each}}
		<div class="cl"></div>
		{{#if_gteq data.test.dbRights.rights.owner.[0] compare="6"}}
			<font style="color:#4B8902">The Owner needs atleast Read and Write (>=6) permissions.<br>It looks like the webserver user has sufficient rights on the database.</font>		
		{{else}}
			<font style="color:#FF0000">It looks like the webserver user has <strong>insufficient rights</strong> on the database</font>
		{{/if_gteq}}
		<div>
		{{else}}
		<br>
		<font style="color:#FF0000">No tests available for this DB engine... coming soon...</font>
		{{/if_eq}}
		<br />
</fieldset>
</form>
<form>
	<button type="button" id="btnCheckDb">Check the database</button>
</form>

<form>
<fieldset>
    <legend>Logging:</legend>
<div id="extensions">
    <div>
    If you experience any problems with WebSolarLog, please check the WebSolarLog logfiles.<br>
    <br>
    Below you find the logfile locations;<br>
			{{#data.test.logs}}
				<strong>{{this.name}}</strong>:<br>
				{{this.location}}<br>
				<a href="{{this.url}}" target="_blank">{{this.url}}</a><br>
			{{/data.test.logs}}
	</div>
</div>
</fieldset>
</form>

<form>
<fieldset>
    <legend>Time settings:</legend>
<div id="timesettings">
    <div>
    WSL needs correct date/time and timezone settings:<br>
    <br>
    <strong>UCT timezone:</strong><br>
    date and time: {{data.test.time.dateTimeUTC.date}}<br>
    <br>
    <strong>Your system:</strong><br>
    Timezone: {{data.test.time.dateTimeLocation.timezone}}<br>
    date and time: {{data.test.time.dateTimeLocation.date}}<br>
    <br>
    offset between UTC and {{data.test.time.dateTimeLocation.timezone}}:<br>
    {{data.test.time.offset}} hour(s)
	</div>
</div>
</fieldset>
</form>

<form>
<fieldset>
    <legend>Check functions/extensions:</legend>
    Here we check if necessary functions and extensions are loaded and working.
<div id="extensions">
    <div>
    Check if extensions are loaded:<br>
		{{#each data.test.extensions}}
			- {{this.type}} "{{@key}}":
			{{#str_contains @key look_for="sqlite"}}
				{{#if this.status}}
					{{#if ../../../data.test.sqliteVersionMixed}}
						 <strong>
						 {{#if_eq this.type compare="extension"}}
						 	Loaded
						 {{else}}
						 	Exists
						 {{/if_eq}}
						 </strong><br><font style="color:#FF0000">It looks like the SQLite DB plugins are mixed.<br>We need SQLite3 and your system got SQLite.</font>
					{{else}}
						 <strong>
						 {{#if_eq this.type compare="extension"}}
						 	Loaded
						 {{else}}
						 	Exists
						 {{/if_eq}}</strong><br><font style="color:#4B8902">This looks good.</font>
					{{/if}}
				{{else}}
					{{#if ../../../data.test.sqliteVersionMixed}}
						<strong>
						{{#if_eq this.type compare="extension"}}
						 	NOT Loaded
						 {{else}}
						 	NOT Exists
						 {{/if_eq}}
						 </strong><br><font style="color:#FF0000">It looks like the SQLite DB plugins are mixed.<br>We need SQLite3 and your system got SQLite.</font>
					{{else}}
						 <strong>
						 {{#if_eq this.type compare="extension"}}
						 	NOT Loaded
						 {{else}}
						 	NOT Exists
						 {{/if_eq}}</strong><br><font style="color:#4B8902">This looks good.</font>
					{{/if}}
				{{/if}}
			{{else}}
				{{#if this.status}}
					<strong>
						{{#if_eq this.type compare="extension"}}
						 	Loaded
						 {{else}}
						 	Exists
						 {{/if_eq}}
						 </strong><br><font style="color:#4B8902">This looks good.</font>
				{{else}}
					<strong>						
					{{#if_eq this.type compare="extension"}}
						 	NOT Loaded
					{{else}}
						 	NOT Exists
					{{/if_eq}}
					</strong><br><font style="color:#FF0000">This looks NOT good.</font>
				{{/if}}
			{{/str_contains}}
			<hr>
		{{/each}}
	</div>
</div>
<br />
<div id="encryptiontest">
    Encrypting test
    {{#if data.test.encrypting}}
    	<strong>passed</strong><br>
    	<font style="color:#4B8902">This looks good.</font>
    {{else}}
    	<strong>failed</strong><br>
    	<font style="color:#FF0000">This looks NOT good.
    	{{#if_eq data.test.extensions.mcrypt_module_open.status compare=false}}
    	<br>This probably caused by the "mcrypt_module_open" function thats missing, please install it.
    	{{/if_eq}}</font>
    {{/if}}<br />
    
    <br>
</div>
<div id="sqlite">
	SQLite3:<Br>
	We need atleast version 3.7.11.<br>
    <label>You are on version:</label>{{data.test.sqliteVersion}}<br>
    {{#if data.test.sqliteVersionCheck}}
    	<font style="color:#4B8902">This is good.</font>
    {{else}}
    	<font style="color:#FF0000">Your SQLite version is to old and you may experiance unexpected behaviour.<br>Please update your SQLite version to atleast 3.7.11</font>
    {{/if}}
    <br><Br>
</div>
<div id="sqlite">
	PHP:<Br>
	We need atleast version 5.3.10<br>
    <label>You are on version:</label>{{data.test.phpVersion}}<br>
    {{#if data.test.phpVersionCheck}}
    	<font style="color:#4B8902">This is good.</font>
    {{else}}
    	<font style="color:#FF0000">Your PHP version is to old and you may experiance unexpected behaviour.<br>Please update your SQLite version to atleast 3.7.11</font>
    {{/if}}
</div>
</fieldset>
</form>
<form>
<fieldset>
    <legend>language:</legend>
    	Your browser supports the following languages:
    	<ul style="list-style-type: none;">
    	{{#each data.test.browserLanguages}}
    		<li>- <b>{{this}}</b></li>
    	{{/each}}
    	</ul>
    	WebSolarLog tries to set:<br>
    	locale: <b>{{data.test.setLanguage.locale}}</b><br>
    	domain: <b>{{data.test.setLanguage.domain}}</b><br>
    	WebSolarLog session language set to:<br>
    	session language: <b>{{data.test.sessionLanguage}}</b><br>
    </fieldset>
</form>
