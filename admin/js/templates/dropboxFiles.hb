<form>
  <input type="hidden" name="s" value="XXXXX" />
  <fieldset>
    <legend>Dropbox</legend>
    <div class="column span-40">
    We can use Dropbox for backup the stats of WSL.<br>
    Below you see the files that are in the dropbox app folder of you dropbox.
    	<div id="dropboxFileInfo"></div>
    	<div class="column span-2 first">##</div>
    	<div class="column span-10">Filename</div>
    	<div class="column span-7">Datetime</div>
		<div class="column span-3" style="text-align:right;">Size</div>
		<div class="column span-4 last">Actions</div>
		<div style="overflow-y: scroll; height:312px;overflow-x:hidden;" class="column span-28 last" id="dropboxFilesContainter">
		{{#each data.files}}

		{{#if_eq ../data.noData compare="0"}}
		<div class="column span-28" id="file{{id}}">
			<div class="column span-2 first">{{num}}</div>
			<div class="column span-10" id="path{{id}}">{{path}}</div>
			<div class="column span-7">{{client_mtime}}</div>
			<div class="column span-3" style="text-align:right;">{{size}} MB</div>
			<div class="column span-4 last">
				<a href="{{fullPath}}"><img src="images/page_white_put.png" id="download{{id}}" class="icon downloadFile" title="Download:{{path}}"/></a>&nbsp;|&nbsp; 
				<img src="images/page_white_delete.png" id="delete{{id}}" class="icon deleteFile" title="Permanent Delete:{{path}}"/>
			</div>
		</div>
		{{/if_eq}}
		{{#if_eq ../data.noData compare="1"}}

			<div class="column span-28" style="color:#f00;">{{noData}}</div>
			<div class="column span-28" style="font-size:10px;">{{noData2}}</div>

		</div>
		{{/if_eq}}
		
		{{/each}}
		</div>
<div class="column span-21 first">&nbsp;</div>
<div class="column span-4" style="text-align:right;border-top:1px solid #000;">{{data.totalBackupSize}} MB</div>
<br><br>
<div class="clear"></div>
<input type="hidden" value="0" id="requestActive" />
<div id="makeBackup" class="column span-5 first icon"><img src="images/lock_go.png"/>Make backup</div>
<div id="dropboxSync" class="column  span-6 icon" ><img src="images/arrow_refresh.png"/>Sync with dropbox</div>
<div id="detachDropbox" class="column  span-8 icon" ><img src="images/link_delete.png"/>Disconnect dropbox</div>
<br>
</div>
  </fieldset>
</form>