
<h1 id="site-title">System Photo's</h1>
{{#if_gt data.photoCount compare=0}}
	{{#each data.systemPhotos}}
		<div style="width:100%;">		
			<img src="{{this.dirname}}/{{this.basename}}" style="max-width:60em;display: block;margin-left: auto;margin-right: auto;border:5px ridge #8bc32b;">
			<p style="min-width:auto;text-align:center;" title="filename:{{this.basename}}">{{this.fileTitle}}</p>
		</div>
	{{/each}} 

{{else}}
<div style="text-align:center;">
no images (png,jpg,jpeg) found in "{{data.photoDir}}".<br><br>
filename == image title without "-"<br><br>
example filename:<br>
{{data.photoDir}}solar-panel-west-18-190Wp.png<br><br>
becomes image title;<br>
solar panel west 18 190Wp<br>
</div>
{{/if_gt}}