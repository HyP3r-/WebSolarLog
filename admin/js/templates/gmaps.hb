    <script type="text/javascript">
      function initialize() {
      	middleWorld = new google.maps.LatLng ({{lat}},{{long}});
        var mapOptions = {zoom: 9,center: middleWorld,mapTypeId: google.maps.MapTypeId.ROADMAP};
        if (map == undefined){
        	var map = new google.maps.Map(document.getElementById('mapcanvas'),mapOptions);
  			marker = new google.maps.Marker ({position: middleWorld,
  		
			labelContent: "Selected by you",labelAnchor: new google.maps.Point(22, 0),
			labelClass: "labels", // the CSS class for the label
			labelStyle: {opacity: 0.75}});
       
	   		marker.setMap (map);
   			marker.setDraggable (true);


    		var pinColor = "00ff00";
    		var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor,new google.maps.Size(21, 34),new google.maps.Point(0,0),new google.maps.Point(10, 34));
			var pinShadow = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_shadow",new google.maps.Size(40, 37),new google.maps.Point(0, 0),new google.maps.Point(12, 35));
	
			shadowMarker = new google.maps.Marker({position: new google.maps.LatLng(0,0), map: map,icon: pinImage,shadow: pinShadow,});
	    	shadowMarker.setPosition(middleWorld);
		
			google.maps.event.addListener(marker, "dragend", function(event) {
				var point = marker.getPosition();
				//alert(point.lat());
				map.panTo(point);
				$('#hiddenLat').val(point.lat());
				$('#hiddenLong').val(point.lng());
				var round = $("#roundLatLong").val();
				$('#mapsLat').val(Math.round(point.lat()*round)/round);
				$('#mapsLong').val(Math.round(point.lng()*round)/round);
				var newLatLng = new google.maps.LatLng($('#mapsLat').val(),$('#mapsLong').val());
				shadowMarker.setPosition(newLatLng);
        	});
      	}
      	}
      	function loadScript(){
       		var script = document.createElement('script');
       		script.type = 'text/javascript';
       		script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyAmj_7dgtvs5xiYyKCFMe-zQ3RN84Nexjc&sensor=false&callback=initialize';
   	    	document.body.appendChild(script);
		}
      
	$(document).ready(function() {
	loadScript();
			$( "#dialog-modal" ).dialog({
					height: 520,width: 400,modal: true,
					close: 
						function( event, ui ) {
							// on close: destroy google Object, remove google maps scripts, destroy dialog, remove mapsDials (maps container).
							// everything is cleaned up now :) 
							google = null;
							$('script[src*="maps"]').remove();
							$("#dialog-modal").dialog( "destroy" );
							$("#mapsDialog").remove();
						}
				});
		});
		$("#roundLatLong").bind("change", function(){
			var round = $("#roundLatLong").val();
			$('#mapsLat').val(Math.round($('#hiddenLat').val()*round)/round);
			$('#mapsLong').val(Math.round($('#hiddenLong').val()*round)/round);
			var newLatLng = new google.maps.LatLng($('#mapsLat').val(),$('#mapsLong').val());
			shadowMarker.setPosition(newLatLng);
		});
	</script>
	
<div id="dialog-modal" title="Set location for sun calculations">
<input type="hidden" value="{{lat}}" id="hiddenLat"/>
<input type="hidden" value="{{long}}" id="hiddenLong"/>
    <div class="column span-15">
		<div class="column span-3 first">latitude</div>
		<div class="column span-3"><input disabled="disabled" type="text" value="{{lat}}" id="mapsLat" style="width:130px;"/></div>
		<div class="column span-8 last">&nbsp;</div>
		<div class="column span-3 first">longitude</div>
		<div class="column span-3"><input disabled="disabled" type="text" value="{{long}}" id="mapsLong" style="width:130px;"/></div>
		<div class="column span-8 last">&nbsp;</div>
		<div class="column span-15">
			<div class="column span-3 first">round:</div>
			<div class="column span-4">
				<select id="roundLatLong"><option value="1">0</option><option value="10">1</option><option value="100">2</option><option value="1000">3</option><option value="10000">4</option><option value="100000">5</option><option value="1000000">6</option><option value="10000000">7</option><option value="100000000" selected>8</option></select>
			</div>
			<div class="column span-7 last">
				<button type="button" id="btnGeneralMapsOk">Save</button>
			</div>
		<div class="column span-15">
		Green marker; shown on the public page.<br>
		Red marker;the place you selected as you're home.<br>
		NOTE: Only the green marker will be saved.
		</div>	
		</div>
	</div>	
    <div id="mapcanvas"></div>
</div>
