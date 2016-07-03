<!-- Piwik -->
<script src='http://{{data.piwikServerUrl}}/piwik.js' type='text/javascript'></script>
<script type="text/javascript">
// Give the browser time to load the piwik.js
setTimeout(function() { 
	try {
	var piwikTracker = Piwik.getTracker("http://{{data.piwikServerUrl}}/piwik.php", {{data.piwikSiteId}});
	piwikTracker.trackPageView();
	piwikTracker.enableLinkTracking();
	} catch( err ) {}
 }, 3000);
</script><noscript><p><img src="http://{{data.piwikServerUrl}}/piwik.php?idsite={{data.piwikSiteId}}" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tracking Code -->