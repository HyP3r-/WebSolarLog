//For browser that do not have an console like IE8
if (!window.console) console = {log: function() {}};

// Don't know why but slickgrid seems to think we dont have drag support
if (!jQuery.fn.drag) {
	jQuery.fn.drag = function(){};
}

$.ajaxSetup({
	cache: false,
	complete: function(xhr) {
		
		//always init tooltip after a ajax.complete 
		$( '.tooltip' ).tooltip({
			content: function() {
		        return $( this ).context.title;
			}
		});
		//see if we have a JSON and parse it.
		try {
			// catch the "pass" from the json
			var pass = $.parseJSON(xhr.responseText).pass;
			// set the title for pnotify
			var title = 'Security Warning!';
			// if pass == false (we check if there a default password)
			var passSecShown = false;
			//if we have a "pass"
			if(pass==true){
				//loop through all pnotify's to see if we already have a "pass" pnotify
				$('.ui-pnotify-title').each(function(index){
					// if we have one...
					if(($(this).text()==title) && (passSecShown == false)){
						passSecShown = true;
					}
				});
				// if we have no pnotify with the title
				if(passSecShown == false){
					$.pnotify({
		   	 	        title: title,
		   	 	        text: 'We detected the Default Password.<br>This is a big security issue. We advice you to change it into a strong password.',
		   	 	        nonblock: true,
		   	 	        hide: true,
		   	 	        closer: true,
		   	 	        sticker: false,
		   	 	        type:'error'
		   	 	    });
					// set the pnotify to 20 sec.
					$.pnotify.defaults.delay = 20000;
					update_timer_display();
				}
			}
		}catch (e) {
		    // not json
		}		
	}
});

var hash;
var hashId;
var get;
var shortcutFunction;


$(function()
{
	$("#forceGet").bind('click', function(){
		window.location.reload(true);
	});

    $.getJSON('admin-server.php?s=isLogin', function(data) {
        if (data.result === true) {
            init_menu();

            WSL.checkURL();
            // check if there is a function
            if(shortcutFunction){
            	// call the #xxxxxx function // example: '/admin/#backup' load the backup page.
            	runFunction('init_'+shortcutFunction);
            }else{
            	// else always load the general function
            	init_general(); // First admin item
            }
            
            checkUpgradeMessage();
        } else {
            $.ajax({
                url : 'js/templates/login.hb',
                dataType : 'text',
                success : function(source) {
                    var template = Handlebars.compile(source);
                    var html = template({
                        'data' : data
                    });
                    $('#content').html(html);
                    $('#loginForm').bind('submit', function (){
                    	checkCheckboxesHiddenFields();
                        var data = $(this).serialize();
                        $.post('admin-server.php', data, function(logindata){
                            if (logindata.result == true) {
                                init_menu();
                                init_general(); // First admin item
                                $.pnotify({
                                    title: 'Login',
                                    text: 'Succesfully logged in.',
                                    type: 'success'
                                });
                            } else {
                                $.pnotify({
                                    title: 'Login',
                                    text: 'Failed to log in. Please retry!',
                                    type: 'error'
                                });
                            }
                        });
                        return false; // prevent form submit
                    });
                }
            });
        }
    });
});
/*
 * Init var/array
 */

function checkUpgradeMessage() {
	WSL.connect.getJSON('../api.php/Config/upgradeMessage', function(data) {
		if (data.result == true) {
			WSL.notify.show_bar_top('info', 'Upgrade message', data.message);
		}
	});
}

var originalPerc=new Array(2,5,7,10,12,14,14,12,10,7,5,2);
var Perc=[];
var Month=new Array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');

/**
 *  Init PowerPreset value's
 */
function init_KWHcalc(inv_data){
	if(inv_data.inverter!=null){
	    var data = [];
	    data['inverterId'] = $('input[name="id"]').val();
	    data['month_perc'] = [];
	    var newPerc = [];
	    
	    // Merge month with percentages from live stream if not null or empty
	    $.each(Month, function(){
	        var perc = inv_data.inverter["expected"+this.toUpperCase()];
	        var month_perc = [];
	        month_perc['month'] = this;
	        month_perc['perc'] = perc;
	        if (typeof (perc) != 'undefined' && perc != null && perc != "" ) {
	            newPerc.push(perc);            
	        }
	        data['month_perc'].push(month_perc);
	    });
	
	    // Replace Perc if we have received an valid value for all months!
	    if (newPerc.length==12) {
	        Perc = newPerc;        
	    } else {
	        Perc = originalPerc;
	    }
	    
	    $.ajax({
	        url : 'js/templates/expectation.hb',
	        dataType : 'text',
	        success : function(source) {
	            var template = Handlebars.compile(source);
	            var html = template({
	                'data' : data
	            });
	
	            $('#expectations').html(html);
	            $("input[id$='KWH']").bind("keyup", function(){
	                object = this;
	                KWHcalc(object,Perc,Month);
	            });
	            $("#totalKWHProd").bind("keyup", function(){
	                object = this;
	                KWHcalc(object,Perc,Month);
	                $('input[name="expectedkwh"]').val($("#totalKWHProd").val());
	            });
	            
	            $("#totalKWHProd").bind("keyup", function(){
	                object = this;
	                KWHcalc(object,Perc,Month);
	                $('input[name="expectedkwh"]').val($("#totalKWHProd").val());
	            });
	            $('input[name="expectedkwh"]').bind("keyup", function(){
	            	$("#totalKWHProd").val( $('input[name="expectedkwh"]').val());
	            }),
	            
	            $("#totalKWHProd").val($('input[name="expectedkwh"]').val()).trigger("keyup");
	            
	            $('#btnExpectationSubmit').bind('click', function(){
	                var inverterId = $('input[name="id"]').val();
	                checkCheckboxesHiddenFields();
	                var data = $('#expectationFormId').serialize();
	                $.post('admin-server.php', data, function(){
	                    init_devices(inverterId);                        
	                    $.pnotify({
	                        title: 'Saved',
	                        text: 'You\'re changes have been saved.',
	                    });
	                });
	            });
	            
	            $('#btnExpectationDefault').bind('click', function(){
	                Perc = originalPerc;
	                $("#totalKWHProd").val($('input[name="expectedkwh"]').val()).trigger("keyup");
	            });
	            
	        }
	    });
	}
}

/**
 * Init menu buttons
 */
function init_menu() {
    $(".btnAdvanced").bind('click', function() { init_advanced();});
    $(".btnGeneral").bind('click', function() { init_general();});
    $(".btnDevices").bind('click', function() { init_devices(); });
    $(".btnGraphs").bind('click', function() { init_graphs(); });
    $(".btnCommunication").bind('click', function() { init_communication(); });
    $(".btnGrid").bind('click', function() { init_grid();});
    $(".btnEmail").bind('click', function() { init_email(); });
    $(".btnYields").bind('click', function(){ init_yields(); });
    $(".btnDiagnostics").bind('click', function() { init_diagnostics(); });
    $(".btnTariff").bind('click', function() { init_tariff(); });
    $(".btnSocial").bind('click', function() { init_social(); });
    $(".btnUpdate").bind('click', function() { init_update(); });
    $(".btnBackup").bind('click', function() { init_backup(); });
    $(".btnPlugwise").bind('click', function() { init_plugwise(); });
    $(".btnDataMaintenance").bind('click', function() { init_dataMaintenance(); });
}

function init_plugwise(){
	WSL.checkURL();
	setTitle("Plugwise");
	$('#sidebar').html("");

	$.getJSON('admin-server.php?s=getAllPlugs', function(data) {
        $.ajax({
        	async: true,
        	url : 'js/templates/plugwiseAllPlugs.hb',
            success : function(source) { 
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : data
                });
                $('#content').html(html);
                
                var getAllPlugs = window.setInterval(function(){
                	if ($('#site-title').text().toLowerCase().indexOf("plugwise") >= 0){
	                	$.getJSON('admin-server.php?s=getAllPlugs', function(data) {
	                		for (var key in data.plugs) {
	                		   var obj = data.plugs[key];
	                		   if ( $("#"+obj.applianceID+'-W').html() != (obj.currentPowerUsage+" W")){
	                			   $("#"+obj.applianceID+'-W').effect( "highlight" ,1000);
	                		   }
	                		   $("#"+obj.applianceID+'-W').html(obj.currentPowerUsage+' W');                			   
	                		}
	                	});
                	}else{
                		clearInterval(getAllPlugs);
                		var getAllPlugs = null;
                	}
                }, 7000);
            	
                /*
                 * Catch click to sync all plug-data(actual power usage, switch state) from Stretch with WSL-database
                 */
                $("#btnSyncPlugs").bind('click', function() {
                	var data = "s=syncPlugs";
                	$.post('admin-server.php', data, function(data){
                        if (data.result == true) {
                        	console.log('ready, true');
                            $.pnotify({
                                title: 'Login',
                                text: 'Succesfully logged in.',
                                type: 'success'
                            });
                        } else {
                            $.pnotify({
                                title: 'Login',
                                text: 'Failed to log in. Please retry!',
                                type: 'error'
                            });
                        }
                    });
            	});
            	
                $("div.editme").click(function() {
            		var id = $(this).attr('id');
            		if ($("#"+id).children('input').length == 0) {
            			var inputbox = "<input type='text' class='column span-3' id=\"input-"+id+"\" class='inputbox' value=\""+$("#"+id).text()+"\">";
            			$("#"+id).html(inputbox);
            			$("#input-"+id).focus();
            			
            			$("#input-"+id).keydown(function(e) {
            			    if (e.keyCode == 9) {  //tab pressed
            			    	$(this).blur();  	
		            			e.preventDefault(); // stops its action
            			    }
            			})
            			$("#input-"+id).blur(function() {
            				var value = $("#input-"+id).val();
            				$("#"+id).html(value);
            				if($("#"+id).html() == value){
            	                $.post('admin-server.php?s=plugwiseUpdatePlug',{id: id,name: value} , function(){
                                    $.pnotify({
                                        title: 'Plug name saved',
                                        text: 'Name "'+value+'" saved',
                                        type: 'success'
                                    });
            	                });            				
            				}
            			});
            			
            		}
            	});
                $("div.switchPlug").click(function() {
                var id = $(this).attr('id');
                var applianceID = $(this).attr('id').split('-')[0];
                var newPowerState = $(this).attr('id').split('-')[1];
                var oldPowerState = (newPowerState == 'on') ? 'off' : 'on';
                $('#'+id).html('Switching....');
               	$.get('admin-server.php?s=switchPowerState',{ applianceID: applianceID , newPowerState: newPowerState },function(data){
                        $.pnotify({
                            title: 'Plug name saved',
                            text: 'Switch power state to: "'+newPowerState+'"',
                            type: 'success'
                        });
                        $('#'+id).html('Switch' + ' ' + oldPowerState);
                        $('#'+id).attr('id',  applianceID + '-' + oldPowerState);
	                });
                });
            },
            dataType : 'text'
        })
	 });
}


function init_tariff(){
	WSL.checkURL();
    $('#sidebar').html("");
    setTitle("Tariff");
    var content = $('#content');
    content.html('<div id="c_general"></div><div id="c_communication"></div><div id="c_security"></div>'); // Clear old data
    $.getJSON('admin-server.php?s=get-tariffs', function(data) {
        $.ajax({
            url : 'js/templates/tariffs.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : data
                });
                $('#c_general', content).html(html);
                
            },
            dataType : 'text'
        });        
    });
	
}

function init_backup() {
	WSL.checkURL();
    $('#sidebar').html("");
    setTitle("Backup");
    $.getJSON('admin-server.php?s=dropbox', function(data) {
    	if (data.available){
    	    $.getJSON('admin-server.php?s=dropboxGetFiles', function(data) {
    	        $.ajax({
    	            url : 'js/templates/dropboxFiles.hb',
    	            success : function(source) { 
    	                var template = Handlebars.compile(source);
    	                var html = template({
    	                    'data' : data
    	                });
    	                $('#content').html(html);

    		            $('.deleteFile').bind('click', function(){deleteFiles(this);});
	                    $('#makeBackup').bind('click', function(){makeBackup();});
	                    $('#dropboxSync').bind('click', function(){dropboxSync();});
	                    $('#detachDropbox').bind('click', function(){detachDropbox();});
	                    
    	            },
    	            dataType : 'text'
    	        });
    	    });
    	}else{
            $.ajax({
                url : 'js/templates/dropbox.hb',
                success : function(source) {
                    var template = Handlebars.compile(source);
                    var html = template({
                        'data' : data
                    });
                    $('#content').html(html);
                },
                dataType : 'text'
            });
    	}
    });
}

var communicationTestTimerId = 0;
function init_communication() {
	var sidebar = $('#sidebar');
    var content = $('#content');
    shortcutfunction = "communication";
    
    WSL.checkURL();
    
    // Initialize the html holders
    content.html('<div id="pnl_communication"></div><div id="pnl_communication_test"></div>');
    sidebar.html('');
    setTitle("Communication");
    
    var load_edit = function (data) {
    	$('#pnl_communication').html(WSL.template.get('communication', {data: data}));
    	
    	$('#btnCommunicationSubmit').bind('click',function(){
    		var postdata = $(this).closest('form').serialize();
    		WSL.connect.postJSON("../api.php/Communication", postdata, function(){
    			init_communication();
    		});
    	});  
    	
    	$("input[name = 'btnCommunicationDelete']").bind('click', function(){  
        	$this = $(this);
    		delete_communication($this.val());
    	});
    	
    	if (data.id > 0) {
    		load_test(data.id);    		
    	}
    };
    
    var delete_communication = function(thisId){
    	$.ajax({
            type: "DELETE",
            url: "../api.php/Communication/"+thisId,
            success: function(response){
				if (response.result == true) {
					$.pnotify({
						title: 'Succes',
				        text: 'Item removed',
				        type: 'success'                                    
					});     
				} else {
					if(response.linked == true){
						$.pnotify({
					        title: 'Error',
					        text: 'Could not remove item because it is still linked to a device.',
					        type: 'error'
					    }); 	
					}else{
						$.pnotify({
					        title: 'Error',
					        text: 'Could not remove item.',
					        type: 'error'
					    });
					}
				}
				init_communication();
            }
    	});
    }
    
    
    var load_test= function(communicationId) {
    	WSL.connect.getJSON('../api.php/Device/ShortList/true', function(devices) {
    		var data = {communicationId: communicationId, devices: devices};
    		$('#pnl_communication_test').html(WSL.template.get('communication_test', data));
    		
    		$('#deviceSelect').bind('change', function(){
    			if ($(this).val() == -1) {
    				$('#device_info').hide();
    			} else {
    				WSL.connect.getJSON('../api.php/Device/' + $(this).val(), function(device) {
    					$('#device_id').val(device.id);
    					$('#device_name').val(device.name);
    					$('#device_address').val(device.comAddress);
    					$('#device_api').text(device.deviceApi);
    					$('#device_info').show();
    					
    					$('#btnTestCommunication').bind('click', function(){
    						// Do some great stuff
    						$('#test_results').text("Sending item to queue ...");
    						var postdata = $(this).closest('form').serialize();
    						WSL.connect.postJSON('../api.php/Communication/startTest', postdata, function(result) {
    							$('#test_results').text(result.results);
    						} );
    					});
    				});
    			}
    		});
    	});
    };
    
    // Load the screen
    var currentCommunicationId = 0;
    WSL.connect.getJSON('../api.php/Communication', 
    	function(data) {
	    	sidebar.html(WSL.template.get('communication_sb', {data: data}));
	    	var communicationDevices = $('.communication_select').length;
	    	if(communicationDevices==0){
	    		$("#communication_import").show();
	    	}
	    	$('#new_communication').bind('click', function(){
	    		load_edit({id:-1, name:'New'});
	    	});

	    	$('#communication_import').bind('click', function(){
	    		var modal_overlay;
	    		var text = '<form id="pnotify-confirm" name="pnotify-confirm">This will create an import for every device, please only use once!<br>';
	    		text += '<input type="submit" name="submit" value="Create Communication Devices">&nbsp&nbsp&nbsp&nbsp;&nbsp&nbsp&nbsp&nbsp;';
	    		text += '<button type="button" id="pnotify-cancel" id="pnotify-cancel">Cancel!</button></form>';

	    		var box = $.pnotify({
					title: "Import Advanced settigng to communication manager",
					text: text,
					type: "error",
					hide: false,
					closer: false,
					sticker: false,
					history: false,
					stack: false,
					before_open: function(pnotify) {
					    // Position this notice in the center of the screen.
					    pnotify.css({"width":400,"top": ($(window).height() / 2) - (pnotify.height() / 2),"left": ($(window).width() / 2) - (pnotify.width() / 2)});
					    // Make a modal screen overlay.
					    if (modal_overlay) modal_overlay.fadeIn("fast");
					    else modal_overlay = $("<div />", {
					        "class": "ui-widget-overlay","css": {"display": "none","position": "fixed","top": "0","bottom": "0","right": "0","left": "0"}
					    }).appendTo("body").fadeIn("fast");
					},
					before_close: function() {
					    modal_overlay.fadeOut("fast");
					    }
	    		});

	    		//confirmed;
	    		box.find('#pnotify-confirm').submit(function() {
	    	    	WSL.connect.getJSON('admin-server.php?s=importOldCommunicationSettings', function(data){
	    	    		init_communication();
	    	    	});	    		
    		    	box.pnotify_remove();
    		        return false;
    		    });
	    		
	    		// cancelled
	 	        box.find('#pnotify-cancel').bind('click',function() {
		        	$.pnotify({
		                title: 'Cancelled creation communication devices',
		                text: 'You cancelled the creation of communication devices',
		                icon: true,
		                width: $.pnotify.defaults.width,
		                hide: true,
		                closer: true,
		                sticker: true,
		                type: 'warning'
		            });
		        	box.pnotify_remove();
		            return false;
		        });
	    	});

	    	$('.communication_select').bind('click', function(){
                var id = $(this).attr('id').split("_")[1];
                currentCommunicationId = id;
                window.location.hash = shortcutFunction+"-"+id;
                WSL.connect.getJSON('../api.php/Communication/'+id, function(data){
                	load_edit(data);
                });
	    	});
    	}
    );
    
    // Auto refresh the test results
    if (communicationTestTimerId) clearInterval(communicationTestTimerId); // Make sure we don't load more then one timer
    communicationTestTimerId = window.setInterval(function(){
    	if (currentCommunicationId > 0) {
    		WSL.connect.getJSON('../api.php/Communication/'+currentCommunicationId, function(data){
    			if (data.lastTestTime != null) {
    				$('#lastTestTime').text(data.lastTestTimeFormatted);
    				$('#lastTestResult').text((data.lastTestResult == 1) ? "success" : "failure");
    				$('#lastTestData').text(data.lastTestData);    				
    			}
    		});
    	}
    }, 5000);
}

function detachDropbox(){
	$.getJSON('admin-server.php?s=detachDropbox', function(data) {
		
	});
}

function dropboxSync(notice){
    if($('#requestActive').val() == 0){
    	$('#requestActive').val(1);
		var SyncNotice = $.pnotify({
	        title: 'Dropbox',
	        text: 'Syncing files....',
	        nonblock: true,
	        hide: false,
	        closer: false,
	        sticker: false
	    });
	    $.getJSON('admin-server.php?s=dropboxSyncFiles', function(data) {
	        $.ajax({
	        	async: true,
	        	url : 'js/templates/dropboxFiles.hb',
	            success : function(source) { 
	                var template = Handlebars.compile(source);
	                var html = template({
	                    'data' : data
	                });
	                if(data.success){
		                $('#content').html(html);
			            $('.deleteFile').bind('click', function(){deleteFiles(this);});
		                $('#makeBackup').bind('click', function(){makeBackup();});
		                $('#dropboxSync').bind('click', function(){dropboxSync();});
		                
		                
		                $('#requestActive').val(0);
			   	       
		                var SyncNotice = $.pnotify({
		                	title: 'Dropbox',
			   	 	        text: 'Sync ready.',
			   	 	        nonblock: true,
			   	 	        hide: true,
			   	 	        closer: true,
			   	 	        sticker: false,
			   	 	        type:'success'
		                });
	                }else{
	                	$.pnotify({
			   	 	        title: 'Dropbox',
			   	 	        text: 'Something went wrong:<br>'+data.message+'<br><br>We deleted this token, so you need to re-autorize with Dropbox.',
			   	 	        nonblock: true,
			   	 	        hide: true,
			   	 	        closer: true,
			   	 	        sticker: false,
			   	 	        type:'error'
	                	});
	                	$('#requestActive').val(0);
	                	$.ajax({
	                		type: "POST",
	                		url: 'admin-server.php?s=detachDropbox',
	                		data: data,
	                		success: function(data){
	                			init_backup();
	                		},
	                		dataType: 'json'
	                	});
	                }
	            },
	            dataType : 'text'
	        }).done(function() { SyncNotice.pnotify_remove(); });
	    });
    }else{
		var SyncNotice = $.pnotify({
	        title: 'Dropbox',
	        text: 'There is a request running.',
	        nonblock: true,
	        hide: true,
	        closer: true,
	        sticker: false,
	        type:'error'
	    });
    }
}

function makeBackup(){
    if($('#requestActive').val() == 0){
    	$('#requestActive').val(1);
	    var BackupNotice = $.pnotify({
	        title: 'Dropbox',text: 'Backup is running....',nonblock: true,hide: false,closer: false,sticker: false
	    });
	    var data = '';
	    $.post('admin-server.php?s=dropboxMakeBackup',data , function(){
	    	$.getJSON('admin-server.php?s=dropboxGetFiles', data, function(data){
	    		
	            $.ajax({
	           	url : 'js/templates/dropboxFiles.hb',
	               success : function(source) { 
	                   var template = Handlebars.compile(source);
	                   var html = template({
	                       'data' : data
	                   });
	                   $('#content').html(html);
	   	               $('.deleteFile').bind('click', function(){deleteFiles(this);});
	   	               $('#makeBackup').bind('click', function(){makeBackup();});
	   	               $('#dropboxSync').bind('click', function(){dropboxSync();});
	   	               if (BackupNotice.pnotify_remove) BackupNotice.pnotify_remove();
			   	       var SyncNotice = $.pnotify({
			   	 	        title: 'Dropbox',
			   	 	        text: 'Backup ready.',
			   	 	        nonblock: true,
			   	 	        hide: true,
			   	 	        closer: true,
			   	 	        sticker: false,
			   	 	        type:'success'
			   	 	    });
	   	               $('#requestActive').val(0);
	               },
	               dataType : 'text'
	           });
	        });
	    }); 
    }else{
		var SyncNotice = $.pnotify({
	        title: 'Dropbox',
	        text: 'There is a request running.',
	        nonblock: true,
	        hide: true,
	        closer: true,
	        sticker: false,
	        type:'error'
	    });
    }
}

function deleteFiles(vars){

		var id = vars.id.replace("delete","");
		var fullPath = $("#path"+id).html();

		$("#file"+id).hide("blind", { direction: "vertical" }, 1000);
        var SyncNotice = $.pnotify({
            title: 'Dropbox',
            text: 'Deleting file....',
            nonblock: true,
            hide: false,
            closer: false,
            sticker: false
        });
        $.getJSON('admin-server.php?s=dropboxDeleteFile',{path: fullPath} , function(data,SyncNotice){
            if (SyncNotice.pnotify_remove){ 
            	SyncNotice.pnotify_remove();
            	}
            var SyncNotice = $.pnotify({
                title: 'Dropbox',
                text: data.message,
                nonblock: true,
                hide: false,
                closer: false,
                sticker: false
            });
        });
}

function init_advanced() {
	WSL.checkURL();
    $('#sidebar').html("");
    setTitle("Advanced");
    
    $.getJSON('admin-server.php?s=advanced', function(data) {
        $.ajax({
            url : 'js/templates/advanced.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : data
                });
                
                $('#content').html(html);
                
                
            	
                $( "#sliderCacheRate" ).slider({
                    min: 60,
                    max: 600,
                    step: 1,
                    slide: function( event, ui ) {
                      $( "#cacheInputRate" ).val( ui.value );
                    }
                  });
                  // setter
                

              	$( "#sliderCacheRate" ).slider( "option", "value", $( "#cacheInputRate" ).val() );

              	$( "#cacheInputRate" ).on('keyup',function(){
              		$( "#sliderCacheRate" ).slider( "option", "value", $(this).val() );
              	});
                
                $('#btnAdvancedSubmit').bind('click', function(){
                	checkCheckboxesHiddenFields();
                	var data = $(this).parent().parent().serialize();
                    $.post('admin-server.php', data, function(){
                        $.pnotify({
                            title: 'Saved',
                            text: 'You\'re changes have been saved.'
                        });
                    });
                });   
            },
            dataType : 'text'
        });
    });
}


function init_social(){
	WSL.checkURL();
    $('#sidebar').html("");
    setTitle("Social");
    
    var content = $('#content');
    content.html('<div id="c_social"></div>'); // Clear old data

    $.getJSON('admin-server.php?s=social', function(data,response) {
        $.ajax({
            url : 'js/templates/social.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                data['refURL']= document.URL;   
                var html = template({
                    'data' : data
                });
                $('#content').html(html);
                
                deviceStatuses();

                $('#sendTweet').bind('click', function(){
               	 	console.log('aaa');
                 	$.pnotify({
                 		title: 'Twitter',
                 		text: 'Sending Tweet'
                 	});
                	$.getJSON('admin-server.php?s=sendTweet', function(data) {
                	
	                	 if(data.tweetSend==1){
	                      	$.pnotify({
	                     		title: 'Twitter',
	                     		text: 'Tweet send! Check your Twitter :)'
	                     	});
	                	 }
	                	 if(data.tweetSend==0){
		                      $.pnotify({
		                     	title: 'Twitter',
		                     	text: 'Something went wrong:<br>'+data.message+'',
		                     	type: 'error'
		                    });
	                	 }
                     });
                });

                $('#detachTwitter').bind('click', function(){
                	console.log('bbb');
                 	$.pnotify({
                 		title: 'Twitter',
                 		text: 'Disconnect twitter'
                 	});
                	 $.getJSON('admin-server.php?s=detachTwitter', function(data) {
	                	 if(data.result==1){
	                      	$.pnotify({
	                     		title: 'Twitter',
	                     		text: "We disconnected WSL from you're account."
	                     	});
	                	 }
	                	 if(data.result==0){
		                      $.pnotify({
		                     	title: 'Twitter',
		                     	text: 'Something went wrong:<br>'+data.message+'',
		                     	type: 'error'
		                    });
	                	 }
                     });
                	 init_social();
                });

                $('#attachTwitter').bind('click', function(){
                	console.log('ccc');
                 	$.pnotify({
                 		title: 'Twitter',
                 		text: 'attach Twitter'
                 	});
                	 $.getJSON('admin-server.php?s=attachTwitter', function(data) {
	                	 if(data.result==1){
	                      	$.pnotify({
	                     		title: 'Twitter',
	                     		text: "We connected WSL from you're account."
	                     	});
	                	 }
	                	 if(data.result==0){
		                      $.pnotify({
		                     	title: 'Twitter',
		                     	text: 'Something went wrong:<br>'+data.message+'',
		                     	type: 'error'
		                    });
	                	 }
                     });
                });                
            },
            dataType : 'text'
        });
    });
}

function deviceStatuses(){
    
    $.getJSON('admin-server.php?s=getTeamStatus', function(data,response) {
        $.ajax({
            url : 'js/templates/pvoutputdevices.hb',
            success : function(source) {
            	
                var template = Handlebars.compile(source);
                var html = template({ 'data' : data,'lang':data.lang });
                $('#devices').html(html);

                
                $('[id^="deviceTeamState"]').bind('click', function(){
                	$this = $(this);
                	var get = $this.attr("id").split('-');
                	
                	if(get[2]=='0'){	
                		$.post("admin-server.php?s=leavePVoTeam", { id:get[1]},
                				  function(data){
    				     	$.pnotify({
    				     		title: 'Leaving  PVoutput team',
    				     		text: data.team.response,
                                type: 'error'
    				     	});                				   
                		});
                		
				     	$.pnotify({
				     		title: 'Leaving  PVoutput team',
				     		text: 'We are trying to remove you from the PVoutput WebSolarLog team'
				     	});
				     	deviceStatuses();
                	}else if(get[2]=='1'){
                		$.post('admin-server.php?s=joinPVoTeam', { id:get[1]})
                		.done(function(data) {
    				     	$.pnotify({
    				     		title: 'Joining  PVoutput team',
    				     		text: data.team.response,
                                type: 'success'
    				     	}); 
                		});	              
				     	$.pnotify({
				     		title: 'Joining  PVoutput team',
				     		text: 'We are trying to add you to the PVoutput WebSolarLog team'
				     	});				     	
				     	deviceStatuses();
                	}else{
                		
                	}
			    });
                
            },
            dataType : 'text'
         });
    });
    
}

function init_invoice(data){
	WSL.checkURL();
	setTitle("Invoice");
	window.location.hash = '#invoice';
	
	$('#sidebar').remove();
    var content = $('#content');
    content.html('<div id="c_general"><h1> Invoice</h1><form><fieldset><div id="json" name="summary">Loading Data...</div></fieldset></div>').css("width",850); // Clear old data
    WSL.connect.postJSON('admin-server.php', data, function(result) {
    $.getJSON('admin-server.php?s=invoiceInfo', function(data) {
        $.ajax({
            url : 'js/templates/invoice.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : data
                });
                $('#json', content).html(html);
            }
        })
    }); }); 
    
}

function init_general() {
	WSL.checkURL();
    $('#sidebar').html("");
    setTitle("General");
    
    var content = $('#content');
    content.html('<div id="c_general"></div><div id="c_communication"></div><div id="c_security"></div>'); // Clear old data
    $.getJSON('admin-server.php?s=general', function(data) {
        $.ajax({
            url : 'js/templates/general.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : data
                });
                $('#c_general', content).html(html);
	 			
                $('#btnInvoiceData').bind('click', function(){
    				checkCheckboxesHiddenFields();
    				var data = $(this).parent().parent().serialize();
    				init_invoice(data);
                });
                // prevent comma in latitude value
                $("input[name=latitude]").bind('keyup',function(){
                	$("input[name=latitude]").val($("input[name=latitude]").val().replace(/,/g,"."));
                });
                // prevent comma in longitude value
                $("input[name=longitude]").bind('keyup',function(){
                	$("input[name=longitude]").val($("input[name=longitude]").val().replace(/,/g,"."));
                });
				
                $( "#sliderFrontendLiveInterval" ).slider({
                    min: 1,
                    max: 60,
                    step: 1,
                    slide: function( event, ui ) {
                      $( "#frontendLiveInterval" ).val( ui.value );
                    }
                  });
                  // setter
                

              	$( "#sliderFrontendLiveInterval" ).slider( "option", "value", $( "#frontendLiveInterval" ).val() );

              	$( "#frontendLiveInterval" ).on('keyup',function(){
              		$( "#sliderFrontendLiveInterval" ).slider( "option", "value", $(this).val() );
              	});
                
                $('#btnSetLatLong').bind('click',function(){
                	$('#content').append('<div id="mapsDialog"></div>');
					var lat = $("input[name=latitude]").val();
					var long = $("input[name=longitude]").val();
	               	 $.ajax({
	         	 		url : 'js/templates/gmaps.hb',
	         	 		success : function(source) {
	         	 			var template = Handlebars.compile(source);
	                        var html = template({
	                        	'lat' : lat,
	                            'long': long
	                        });
	         	 			$('#mapsDialog').html(html);
	         	 			$('#btnGeneralMapsOk').bind('click', function(){
	         	 				$("input[name=latitude]").val($('#mapsLat').val());
	        					$("input[name=longitude]").val($('#mapsLong').val());
	        					$("#btnGeneralSubmit").trigger("click");
	        					$("#dialog-modal").dialog('close'); 
	                        });
	         	 		},
	         	 		dataType : 'text'
	         	 	});
                });
                $('#btnGeneralSubmit').bind('click', function(){
                	checkCheckboxesHiddenFields();
                    var data = $(this).parent().parent().serialize();
                    $.post('admin-server.php', data, function(result){
                    	if (WSL.isSuccess(result)) {
                    		$.pnotify({
                    			title: 'Saved',
                    			text: 'You\'re changes have been saved.'
                    		});
                    	}
                    });
                });

                // We don't want to first show the below block, so load it after the communication data
                $.ajax({
            		url : 'js/templates/security.hb',
            		success : function(source) {
            			var template = Handlebars.compile(source);
            			var html = template();
            			$('#c_security', content).html(html);
            			
            			$('#btnSecuritySubmit').bind('click', function(){
            				checkCheckboxesHiddenFields();
            				var data = $(this).parent().parent().serialize();
            				$.post('admin-server.php', data, function(result){
            					$.pnotify({
            						title: result.title,
            						text: result.text
            					});						
            				});
            			});
            		},
            		dataType : 'text' 
                });
            },
            dataType : 'text'
        });        
    });

}


function checkCheckboxesHiddenFields(){
	
	$('input:hidden').each(function () {
		 	if($('input:checkbox[name='+$(this).attr('name')+']:checked').length){
		 		$('input:hidden[name='+$(this).attr('name')+']').remove();
		 	}
	});
}


function init_devices(selected_inverterId) {
	setTitle("Devices");
	$.getJSON('admin-server.php?s=inverters', function(data) {
        $.ajax({
            url : 'js/templates/device_sb.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : data
                });
                $('#sidebar').html(html);

                if (selected_inverterId) {
                    load_device(selected_inverterId);
                } else {
                    $('#content').html("<br /><h2>Choose or create an inverter on the right side --></h2>");                    
                }
                // get hash...
                WSL.checkURL();
                
                if(hashId){
                	load_device(hashId);
                }
                
                $('.inverter_select').bind("click",function(){
                    var button = $(this);
                    var inverterId = button.attr('id').split("_")[1];
                    window.location.hash = shortcutFunction+"-"+inverterId;

                    load_device(inverterId);
                });
                
                $('#new_device').bind("click",function(){
                	var createDevice = $("select[id='createDevice']",$(this).closest('form')).val();
                	var deviceApi = createDevice.split("_")[0];
                	var deviceType = createDevice.split("_")[1];
                	load_device(-1,deviceApi,deviceType);
                });
                
            },
            dataType : 'text'
        });        
    });
}


function init_graphs(selected_graphId) {
	setTitle("Graphs");
	WSL.connect.getJSON('../api.php/Graph/Graphs', function(graphs) {
        $.ajax({
            url : 'js/templates/graph_sb.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : graphs
                });
                $('#sidebar').html(html);
                $('#resetGraphs').bind("click",function(){
					$.pnotify({
						title: 'Resetting Graph(s)',
				        text: 'We are resetting the graphs...',
				        type: 'info'                                    
					});
                	WSL.connect.getJSON('admin-server.php?s=resetGraph', function(result) {
    					$.pnotify({
    						title: 'Graphs reset',
    				        text: 'Graphs are back to normal.',
    				        type: 'success'                                    
    					});     
                	});
                });
                
                if (selected_graphId) {
                    load_graph(selected_graphId);
                } else {
                    $('#content').html("<br /><h2>Choose an graph on the right side --></h2>");                    
                }
                // get hash...
                WSL.checkURL();
                
                if(hashId){
                	load_graph(hashId);
                }
                
                $('.graph_select').bind("click",function(){
                    var button = $(this);
                    var graphId = button.attr('id').split("_")[1];
                    window.location.hash = shortcutFunction+"-"+graphId;
                    //console.log(graphId);
                    load_graph(graphId);
                });
                
            },
            dataType : 'text'
        });        
    });
}



function showAlertOverlay($this,inverterId,typeName) {
	
	var thisId = $this.val();
	if(typeName == 'Panel'){
		var typeText = 'Do you really want to delete this '+typeName+' ?<br />This action could result in lost of data.';
	}else if(typeName == 'Device'){
		var typeText = 'Do you really want to delete this '+typeName+' ?<br /><br /><br />Caution:<br /><br />This action also removes child object such as Panels.';
	}
	var text = '<form id="pnotify-confirm" name="pnotify-confirm">Do you really want to delete this '+typeName+' ?<br>This action could result in lost of data.';
	text += '<input type="submit" name="submit" value="Delete '+typeName+'">&nbsp&nbsp&nbsp&nbsp;&nbsp&nbsp&nbsp&nbsp;';
	text += '<button type="button" id="pnotify-cancel" id="pnotify-cancel">Cancel!</button></form>';
    var modal_overlay;
    info_box = $.pnotify({
        title: "Delete "+typeName,
        text: text,
        type: "error",
        hide: false,
        closer: false,
        sticker: false,
        history: false,
        stack: false,
        before_open: function(pnotify) {
            // Position this notice in the center of the screen.
            pnotify.css({"top": ($(window).height() / 2) - (pnotify.height() / 2),"left": ($(window).width() / 2) - (pnotify.width() / 2)});
            // Make a modal screen overlay.
            if (modal_overlay) modal_overlay.fadeIn("fast");
            else modal_overlay = $("<div />", {
                "class": "ui-widget-overlay",
                "css": {"display": "none","position": "fixed","top": "0","bottom": "0","right": "0","left": "0"}
            }).appendTo("body").fadeIn("fast");
        },
        before_close: function() {
            modal_overlay.fadeOut("fast");
        }
    });
    
    info_box.find('#pnotify-confirm').submit(function() {
    	$.ajax({
            type: "DELETE",
            url: "../api.php/"+typeName+"/"+thisId,
            success: function(response){
				if (response == true) {
					$.pnotify({
						title: 'Succes',
				        text: typeName+' removed!',
				        type: 'success'                                    
					});     
					if(typeName == 'Panel'){
						load_device(inverterId);
					}else{
						init_devices();
						window.location.hash = '#devices';
					}
				} else {
					$.pnotify({
				        title: 'Error',
				        text: typeName+' not removed...',
				        type: 'error'
				    });      
				}
            }
            });
    	info_box.pnotify_remove();
        return false;
    });
    
    info_box.find('#pnotify-cancel').bind('click',function() {
    	$.pnotify({
            title: 'Cancelled panel deletion',
            text: 'You cancelled the deletion of the panel',
            icon: true,
            width: $.pnotify.defaults.width,
            hide: true,
            closer: true,
            sticker: true,
            type: 'warning'
        });
    	info_box.pnotify_remove();
        return false;
    });
}

function load_device(deviceId,deviceApi,deviceType) {
	$('#content').html('Loading...');
	WSL.connect.getJSON('admin-server.php?s=inverter&id='+deviceId, function(inv_data) {
		if(deviceApi){
			inv_data.inverter.deviceApi = deviceApi; 
		}
		if(deviceType){
			inv_data.inverter.type = deviceType; 
		}
		
		$('#content').html(WSL.template.get('device', { 'inverterId' : deviceId, 'data' : inv_data }));

		$('#pvoutputDataDate').val(new moment().format("DD-MM-YYYY"));
		
		// hide ALL elements with (sub)class 'all'
        $("#content").find("[class*='all']").hide();

        // display only the field for this device type
        if(deviceId<0){
        	$('.create_new').show();
        }else{
        	//console.log(inv_data.inverter.type);
        	if(inv_data.inverter.type){
        		$('.'+inv_data.inverter.type).show();
        	}else{
        		$('.all').show();
        	}
        }              
    	
        if(inv_data.inverter.id > 0 && inv_data.inverter.panels.length==0 && inv_data.inverter.type == 'production'){
        	$.pnotify({ title: 'No System Panels', text: 'Please add one or more panels. We need panels for some calculations.'});
        	WSL.scrollTo({element : $("#new_panels").closest('form'),time : '', offset : -70});
        }
        
        WSL.connect.getJSON('../api.php/Communication', function(data){
        	$('#communicationId').html("");
        	$.each(data, function(){
				$('#communicationId').append($('<option>', { value : this.id }).text(this.name));
    		});
    		$('#communicationId').val(inv_data.inverter.communicationId);
        });
        
        $('#btnDeviceSubmit').bind('click', function(){
        	$('#btnDeviceSubmit').attr("disabled", "disabled");
        	
        	// remove disabled attr so POST will process it.
        	$('[disabled="disabled"]').each(function(){$(this).removeAttr('disabled');});
        	checkCheckboxesHiddenFields();
        	var data = $(this).closest('form').serialize();
        	WSL.connect.postJSON('admin-server.php', data, function(result) {
                init_devices(result.id);
                $.pnotify({ title: 'Saved', text: 'You\'re changes have been saved.'});
                $('#btnDeviceSubmit').removeAttr("disabled");
                window.location.hash = '#devices-'+result.id;
            }, function($resultError){$('#btnDeviceSubmit').removeAttr("disabled");});
        });
        
        $('#buttonPVoutputData').bind('click',function(){
        	var deviceId = $("input[name=id]").val();
            window.location.hash = "PVOutputData-"+deviceId;
            var date = $("#pvoutputDataDate").val();
            
            console.log("get date"+date);
            
            if(moment(date,"DD-MM-YYYY").isValid() == false){
            	date = new moment().format("DD-MM-YYYY");
            }
            console.log('query date');
            init_PVOutputData(date,deviceId);
        });
        
        $( "#sliderLiveRate" ).slider({
            min: 2,
            max: 60,
            step: 1,
            slide: function( event, ui ) {
              $( "#refreshTime" ).val( ui.value );
            }
          });
          // setter

      	$( "#sliderLiveRate" ).slider( "option", "value", $( "#refreshTime" ).val() );

      	$( "#refreshTime" ).on('keyup',function(){
      		$( "#sliderLiveRate" ).slider( "option", "value", $(this).val() );
      	});

        $( "#sliderHistoryRate" ).slider({
            min: 60,
            max: 3600,
            step: 1,
            slide: function( event, ui ) {
              $( "#historyRate" ).val( ui.value );
            }
          });
        // setter
      	$( "#sliderHistoryRate" ).slider( "option", "value", $( "#historyRate" ).val() );
      	
      	$( "#historyRate" ).on('keyup',function(){
      		$( "#sliderHistoryRate" ).slider( "option", "value", $(this).val() );
      	});
        
        var handle_panel_submit = function() {
        	checkCheckboxesHiddenFields();
            var data = $(this).closest('form').serialize();
            WSL.connect.postJSON('admin-server.php', data, function(result) {
                $.pnotify({
                    title: 'Saved',
                    text: 'You\'re changes have been saved.'
                });
                load_device(deviceId);
            });                                         
        };
        
        $('.panel_submit').bind('click', handle_panel_submit);

        $('#btnNewPanel').bind('click', function(){
        	WSL.connect.getJSON('admin-server.php?s=panel&id=-1&inverterId='+deviceId, function(data) {
        		$('#new_panels').html(WSL.template.get('panel', { 'data' : data }));
            	$('.panel_submit').unbind('click');
            	$('.panel_submit').bind('click', handle_panel_submit);
            	
            });
        });
        
        init_KWHcalc(inv_data);
        $("input[name = 'removePanel']").bind('click', function(){                        
            if ($(this).is(":checked")){
            	$this = $(this);
            	showAlertOverlay($this,deviceId,'Panel');
            }
    	});
        $("input[name = 'removeDevice']").bind('click', function(){                        
            if ($(this).is(":checked")){
            	$this = $(this);
            	showAlertOverlay($this,deviceId,'Device');
            }
    	});

    });
}

function init_PVOutputData(date,deviceId){
	setTitle("PVOutputData");
	WSL.checkURL();
	$('#content').html('Loading...');
	if(deviceId === undefined && hashId != ''){
		deviceId = hashId;
	}
	console.log(date);
	if(moment(date,"DD-MM-YYYY").isValid() == false || date === undefined){
		console.log('reset date in pvoutput');
		date = new moment().format("DD-MM-YYYY");
	}
	//console.log(date+" "+deviceId);
	WSL.connect.getJSON('../api.php/PvOutput/'+date+'/'+deviceId, function(PvOutputData) {
		$.ajax({
            url : 'js/templates/PvOutputData.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : PvOutputData
                });
                $('#content').html(html);
                $.getJSON('admin-server.php?s=getPeriodFilter&type=all', function(data) {
            		$.ajax({
            			url : 'js/templates/datePeriodFilter.hb',
            			beforeSend : function(xhr) {
            				if (getWindowsState() == false) {
            					ajaxAbort(xhr);
            				}
            			},
            			success : function(source) {
            				var template = Handlebars.compile(source);

            				var html = template({
            					'data' : data,
            					'lang' : data.lang
            				});
            				$("#pickerFilter").html(html);
            				
            				$("#datepicker").datepicker({
            					onSelect : function(date) {
            						init_PVOutputData(date,deviceId);
            					}
            				});
            				$('#devicenum option').each(function(){
            					if($(this).val() == deviceId){
            						$(this).attr("selected","selected");
            					}
            				});        					
            				
            				
            				$("#datepicker").datepicker("option", "dateFormat","dd-mm-yy");
            				$("#datepicker").datepicker('setDate', date);

            				// fix for Graph Tooltip
            				$("#datepicker").css('z-index', 0);
            				// fix for Graph Tooltip

            				var devicenum = $('#devicenum').val();

            				$('#next').unbind('click');
            				$('#previous').unbind('click');
            				$('#pickerPeriod').unbind('click');
            				$('#devicenum').unbind('click');
            				$('#pickerPeriod').hide();
            				$('#devicenum').click(function() {
            					var picker = $("#datepicker");
            					var date = new Date(picker.datepicker('getDate'));
            					picker.datepicker('setDate', date);
            					deviceId = $(this).val();
            					init_PVOutputData(date,deviceId);
            				});

            				$('#next').click(function() {
            					var picker = $("#datepicker");
            					var date = new Date(picker.datepicker('getDate'));
            					var splitDate = $('#datepicker').val().split('-');
            					if ($('#pickerPeriod').val() == 'Today') {
            						date.setDate(date.getDate() + 1);
            					} else if ($('#pickerPeriod').val() == 'Week') {
            						date.setDate(date.getDate() + 7);
            					} else if ($('#pickerPeriod').val() == 'Month') {
            						var value = splitDate[1];
            						date.setMonth(value);
            					} else if ($('#pickerPeriod').val() == 'Year') {
            						var value = parseInt(splitDate[2]) + 1;
            						date.setFullYear(value);
            					}
            					picker.datepicker('setDate', date);
            					var splitDate = $('#datepicker').val().split('-');
            					var date = splitDate[0]+ '-'+ splitDate[1]+ '-'+ splitDate[2];
            					deviceId = $('#devicenum').val();
            					init_PVOutputData(date,deviceId);
            				});

            				$('#previous').click(function() {
            					var picker = $("#datepicker");
            					var date = new Date(picker.datepicker('getDate'));
            					var splitDate = $('#datepicker').val().split('-');
            					if ($('#pickerPeriod').val() == 'Today') {
            						date.setDate(date.getDate() - 1);
            					} else if ($('#pickerPeriod').val() == 'Week') {
            						date.setDate(date.getDate() - 7);
            					} else if ($('#pickerPeriod').val() == 'Month') {
            						var value = splitDate[1] - 2;
            						date.setMonth(value);
            					} else if ($('#pickerPeriod').val() == 'Year') {
            						var value = parseInt(splitDate[2]) - 1;
            						date.setFullYear(value);
            					}
            					picker.datepicker('setDate', date);
            					
            					var splitDate = $('#datepicker').val().split('-');
            					var date = splitDate[0]+ '-'+ splitDate[1]+ '-'+ splitDate[2];
            					deviceId = $('#devicenum').val();
            					init_PVOutputData(date,deviceId);
            				});
            			},
            			dataType : 'text'
            		});
            	});
               
            }
		});
	});
}

function load_graph(graphId) {
	WSL.connect.getJSON('../api.php/Graph/'+graphId, function(graph) {
        $.ajax({
            url : 'js/templates/graph.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : graph
                });
                $('#content').html(html);
                

                $('.deleteAxe').on('click',function(){
                	$.ajax({
                        type: "DELETE",
                        //url: "../api.php/Graph/axe/"+$(this).attr('id'),
                        url: "../api.php/Graph/axe/9",
                        success: function(response){
            				if (response.type == 'axe'){
            					if(response.success == true) {
			    					$.pnotify({
			    						title: 'Succes',
			    				        text: 'Axe removed!',
			    				        type: 'success'                                    
			    					});     
			    					init_graphs(graphId);
			    					window.location.hash = '#graphs';
			    				} else {
			    					$.pnotify({
			    				        title: 'Error',
			    				        text: 'Axe not removed...',
			    				        type: 'error'
			    				    });      
			    				}
            				}
                        }
                        });
                });
                
                $(".saveSerie").bind('click',function(){
                	var postdata = $(this).closest('form').serialize();
            		WSL.connect.postJSON(
        				"../api.php/Graph/saveSerie", 
        				postdata, 
        				function(result){
        					if(result == true) {
        						$.pnotify({
        							title: 'Succes',
		    				        text: 'Serie saved!',
		    				        type: 'success'                                    
		    					});
		    					$.pnotify.defaults.delay = 1000;
		    					WSL.scrollTo({element : '#series',time : '', offset : 0});
		    				} else {
		    					$.pnotify({
		    				        title: 'Error',
		    				        text: 'We could not save the serie...',
		    				        type: 'error'
		    				    });      
		    				}
        				});
                });
                
                $(".saveAxe").bind('click',function(e){
                	e.preventDefault();
                	var postdata = $(this).closest('form').serialize();
            		WSL.connect.postJSON(
        				"../api.php/Graph/saveAxe", 
        				postdata, 
        				function(result){
        					if(result == true) {
        						$.pnotify({
        							title: 'Succes',
		    				        text: 'Axe saved!',
		    				        type: 'success'                                    
		    					});
		    					$.pnotify.defaults.delay = 1000;
		    					WSL.scrollTo({element : '#axes',time : '', offset : 0});
		    				} else {
		    					$.pnotify({
		    				        title: 'Error',
		    				        text: 'We could not save the axe...',
		    				        type: 'error'
		    				    });      
		    				}
        				});
                });

            },
            dataType : 'text'
        });        
    });
}



function init_grid() {
    alert("grid");
}

function init_email() {
    $('#sidebar').html("");
    var content = $('#content');
    setTitle("eMail");
    
    WSL.checkURL();
    content.html('<div id="c_mail"></div><div id="c_smtp"></div>');
    $.getJSON('admin-server.php?s=email', function(data) {
        $.ajax({
            url : 'js/templates/email.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : data
                });
                $('#c_mail', content).html(html);
                
                $('#btnEmailSubmit').bind('click', function(){
                	checkCheckboxesHiddenFields();
                    var data = $(this).parent().parent().serialize();
                    $.post('admin-server.php', data, function(){
                        $.pnotify({
                            title: 'Saved',
                            text: 'You\'re changes have been saved.'
                        });
                    });
                });

                $('#btnSmtpSubmit').bind('click', function(){
                	checkCheckboxesHiddenFields();
                    var data = $(this).parent().parent().serialize();
                    $.post('admin-server.php', data, function(){
                        $.pnotify({
                            title: 'Saved',
                            text: 'You\'re changes have been saved.'
                        });                        
                    });
                });
                
                $('#btnEmailTest').bind('click', function() {
                	checkCheckboxesHiddenFields();
                    var senddata = $(this).parent().serialize();
                    $.post('admin-server.php', senddata, function(data){
                        if (data.result == true) {
                            $.pnotify({
                                title: 'Succes',
                                text: 'Email was send, check your inbox.',
                                type: 'success'                                    
                            });                                  
                        } else {
                            $.pnotify({
                                title: 'Error',
                                text: 'Email was not send, check the settings.<br />' + data.message,
                                type: 'error'
                            });      
                        }
                    });
                });
            },
            dataType : 'text'
        });        
    });
}

var deviceId = 1;
function init_yields() {
    $('#sidebar').html("");
    $('#content').html('<div id="yields_content"></div><div id="yields_popup"></div>');
    
    
    var content = $('#yields_content');
    setTitle("Yields");
    WSL.checkURL();

    $('#yields_popup').html(WSL.template.get('yields_popup', {}));
    
    var yieldsData;
    WSL.connect.getJSON('admin-server.php?s=yield_getEnergyList&deviceId='+deviceId, function(result) {
    	yieldsData = result.data; // Save the data for later use
    	
    	$('#sidebar').html(WSL.template.get('yields_sb', {data: yieldsData}));
    	
    	// Populate devices
    	WSL.connect.getJSON('../api.php/Device/ShortList/true', function(data){
    		$(data).each(function(){
    			if (this.type == "production") {
    				$('#yield_inverter_select').append($('<option>', { value : this.id }).text(this.name));
    			}
    		});
    		$('#yield_inverter_select').val(deviceId);
    	});
    	
    	$('#yield_inverter_select').bind('change', function(){
    		deviceId = $(this).val();
    		init_yields();
    	});
    	
    	$('.btnYieldMonth').bind('click', function(){
    		var btnYieldMonth = $(this);
    		var year = btnYieldMonth.closest("ul").attr('data-year-id');
    		var month = btnYieldMonth.attr('data-month-id');
    		
    		var monthData = yieldsData[year][month];
    		
    		content.html(WSL.template.get('yields', {year: year, month: month, data: monthData}));
    		
    		$('.btnYieldDayEdit').bind('click', function(){
    			var click = $(this);
    			var day = $(this).attr('data-day');
    			var energyId = $(this).attr('data-energy-id');
    			
    			currentKWH = (yieldsData[year][month][day]['energy'] === undefined) ? 0 : yieldsData[year][month][day]['energy']['KWH'] ; 
    			deviceKWH = (yieldsData[year][month][day]['deviceHistory'] === undefined) ? 0 :  yieldsData[year][month][day]['deviceHistory']['amount'] ;
    			
    			newKWH = (currentKWH > deviceKWH) ? currentKWH : deviceKWH;
    			
    			
    			$('#yield_energyID').val(energyId);
    			$('#yield_current_kwh').val(currentKWH);
    			$('#yield_device_kwh').val(deviceKWH);
    			$('#yield_new_kwh').val(newKWH);
    			
    			$('#yieldsEditDialog').dialog( "open" );

    			$('#btn_yield_save').unbind('click');
    			$('#btn_yield_save').bind('click', function(){
    				// disable save button
    				$('#btn_yield_save').attr('disabled', true);
    				
                    var notify = $.pnotify({
                            title: 'Saving... please wait',
                            text: 'Trying to save your changes.',
                            type: 'warning'
                        });   				
                    
    				saveData =	{ 	"energyId" : energyId,
    								"deviceHistoryId" : ( yieldsData[year][month][day]['deviceHistory'] === undefined) ? 0 : yieldsData[year][month][day]['deviceHistory']['id'],
    								"newKWH" : $('#yield_new_kwh').val()
    							};
    				
    				WSL.connect.postJSON('admin-server.php?s=yield_saveEnergy', saveData, function(result){
    					if (result.success) {
    						if (yieldsData[year][month][day]['energy'] === undefined) {
    							yieldsData[year][month][day]['energy'] = [];
    						}
    						yieldsData[year][month][day]['energy']['KWH'] = $('#yield_new_kwh').val();
    						btnYieldMonth.trigger('click');
    						
    						//remove the notification
    						notify.pnotify_remove();
    						
    						$.pnotify({
                                title: 'Success',
                                text: 'You\'re changes have been saved.',
                            	type: 'success'
                            });
    						$('#btn_yield_save').attr('disabled', false);
    						$('#yieldsEditDialog').dialog( "close" );
    					}else{
    						//something went wrong...
    						
    						//remove the notification
    						notify.pnotify_remove();
    						//enable save button
    						$('#btn_yield_save').attr('disabled', false);
    	    				
    						//display an error message
    						$.pnotify({
                                title: 'Error',
                                text: 'We could not save your settings.',
                            	type: 'error'
                            });
    						
    					}
    				});
    			});
    		});
        	$('.btnYieldDayAdd').bind('click',content,function(){
        		$('#yieldsAddDialog').dialog( "open" );
        		$('#btn_yield_add').unbind('click');
        		$('#btn_yield_add').bind('click', function(){
        			saveData =	{ "time" : $('#yield_add_time').val(),
    						"deviceId" : deviceId,
    						"newKWH" : $('#yield_add_kwh').val()
    					};
    				WSL.connect.postJSON('admin-server.php?s=yield_addEnergy', saveData, function(result){
    					WSL.connect.getJSON('admin-server.php?s=yield_getEnergyList&deviceId='+deviceId, function(result) {
    				    	var monthData = result.data[year][month];
    				    	content.html(WSL.template.get('yields', {year: year, month: month, data: monthData}));
    				    });
    				});
    				$('#yieldsAddDialog').dialog( "close" );
        		});
        	});
    	});
    });
}


function init_diagnostics() {
	WSL.checkURL();
	$('#sidebar').html("");
	setTitle("Diagnostics");
	
    $.getJSON('admin-server.php?s=test', function(data) {
        $.ajax({
            url : 'js/templates/testpage.hb',
            success : function(source) {
                var template = Handlebars.compile(source);
                var html = template({
                    'data' : data
                });
                $('#content').html(html);
                $('#btnCheckDb').bind('click', function() {
                	$.getJSON('../api.php/Janitor/DbCheck');
                    $.pnotify({
                        title: 'Running some DB checks',
                        text: 'We are checking the DB on some things.',
                        type: 'info'
                    }); 
                    $.pnotify.defaults.delay = 2000;
                }); 
            },
            dataType : 'text'
        });        
    });
        
}

function init_update(experimental,beta,scrollTo) {
    if (typeof experimental === 'undefined' ) {
        experimental = false;
    }
    
    setTitle("Update");
    
    WSL.connect.getJSON('admin-server.php?s=updater-getversions&experimental=' + experimental+ '&beta=' + beta, function(data) {
        if (data.result === false) {
            $.ajax({
                url : 'js/templates/updater-problems.hb',
                success : function(source) {
                    var template = Handlebars.compile(source);
                    var html = template({ 'data' : data });
                    $('#content').html(html);
                },
                dataType : 'text'
            });
        } else {

        	
            $.ajax({
                url : 'js/templates/updater-experimental.hb',
                success : function(source) {
                    var template = Handlebars.compile(source);
                    var html = template({'experimental' : experimental , 'beta' : beta , 'chkNewTrunk' : data.chkNewTrunk});
                    $('#sidebar').html(html);
                    
                    $('#chkExperimental').bind('click', function(){
                    	var experimental = $('#chkExperimental').is(':checked');
                    	var beta = $('#chkBeta').is(':checked');
                    	if($(this).is(':checked')){
                    		scrollTo = '#trunk';
                    	}else{
                    		scrollTo = '#navigation';
                    	}
                    	init_update(experimental,beta,scrollTo);
                    });
                    $('#chkBeta').bind('click', function(){
                    	var experimental = $('#chkExperimental').is(':checked');
                    	var beta = $('#chkBeta').is(':checked');
                    	if($(this).is(':checked')){
                    		scrollTo = '#beta';
                    	}else{
                    		scrollTo = '#navigation';
                    	}
                    	init_update(experimental,beta,scrollTo);
                    });
                    
                    $("input[name = 'chkNewTrunk']").bind('click', function(){                        
                    	if ($("input[name = 'chkNewTrunk']").is(":checked")){
                    		checkNewTrunk = true;
                    		type = 'success';
                    		checkNewTrunkText = 'We notify you if there is a trunk update.';
                    	}else{
                    		checkNewTrunkText = 'We <b>won\'t</b> notify you on a trunk update.';
                    		type = 'warning';
                    		checkNewTrunk= false;
                    	}
                        $.post('admin-server.php', {'s' : 'save-checkNewTrunk', chkNewTrunk: checkNewTrunk }, function(result){
                            if (result.result === true) {
                            	var title = 'Trunk notifier';
                                $.pnotify({
                                    title: title,
                                    text: checkNewTrunkText,
                                    type: type,
                                    nonblock: true
                                });
                    			
                                if(checkNewTrunk){
	                        		$.getJSON('admin-server.php?s=current-trunk-version', function(data) {
	                        			if(data.trunkNotifier){
	                                    $.pnotify({
	                                        title: 'Trunk notifier',
	                                        text: 'We already found a Trunk update!<br><br><font color="red">Please keep in mind that Trunk releases are not supported!</font>',
	                                        type: 'warning'
	                                    });
	                        			}
	                        		});
                                }
                            } else {
                                $.pnotify({
                                    title: 'Trunk notifier',
                                    text: 'Something went wrong:<br />' + result.error,
                                    type: 'error'
                                });
                            }
                        });
                    });
                },
                dataType : 'text'
            });
        	
            $.ajax({
                url : 'js/templates/updater-versions.hb',
                success : function(source) {
                    var template = Handlebars.compile(source);
                    var html = template({ 'data' : data });
                    $('#content').html(html);
                    
                    if(!scrollTo){
                    	//console.log('aaaa');
                    	WSL.scrollTo({element : '#navigation',time : '', offset : 0});
                    }else{
                    	//console.log('vvv'+scrollTo)
                    	WSL.scrollTo({element : scrollTo,time : '', offset : 0});
                    }
                    
                    $('#btnUpdateSubmit').attr('disabled', true);

                    // bind to radio button of version selection
                    $('input[name="version"]').bind('click',function(){
                    	//if clicked, check if button is checked
                    	if($('input[name="version"]:checked')){
                    		// undo disabling 
                    		$('#btnUpdateSubmit').attr('disabled', false);
                    	}
                    });
                    
                    $('#btnUpdateSubmit').bind('click', function(){
                    	
                    	// scroll to button..
                    	WSL.scrollTo({element : '#btnUpdateSubmit',time : '', offset : 0});
                        var button = $(this);
                        button.attr('disabled', true);
                        var updateNotice = $.pnotify({
                            title: 'Update',
                            text: 'Busy with updating, please wait for this message to dissapear.',
                            nonblock: true,
                            hide: false,
                            closer: false,
                            sticker: false
                        });
                        
                        startUpdaterMonitor(updateNotice, button);
                        checkCheckboxesHiddenFields();
                        var data = $(this).parent().serialize();
                        
                        WSL.connect.postJSON('admin-server.php', data, function(updateresult){
                            if (updateresult.result === true) {
                                $.pnotify({
                                    title: 'Update',
                                    text: 'The update is ready.',
                                    type: 'success'
                                });
                                
                                // WSL does some kind of "soft-restart"
                                $.pnotify({
                                    title: 'WebSolarLog performs a soft-restart',
                                    text: 'It could take 5-10 min. before all changes are visible.<br>Please be patient.',
                                    type: 'info'
                                });
                                
                                // warn user to clean his browser cache to prevent stange behavior
                                window.setTimeout($.pnotify({
                                    title: 'Clean browser cache',
                                    text: 'WSL heavy use Javascript and to prevent stange behavior we advice to clean/remove the local browser cache.<br>Mostly this could be found in the settings of the browser.',
                                    type: 'warning'
                                }),1500);
                                
                            	var experimental = $('#chkExperimental').is(':checked');
                            	var beta = $('#chkBeta').is(':checked');
                            	init_update(experimental,beta);
                            } else {
                                $.pnotify({
                                    title: 'Update',
                                    text: 'The update failed. <br />' + updateresult.error,
                                    type: 'error'
                                });
                            }
                            if (updateNotice.pnotify_remove) updateNotice.pnotify_remove();
                            button.attr('disabled', false);
                        });
                    });
                },
                dataType : 'text'
            });
        	if(data.chkNewTrunk==true){
        		$.getJSON('admin-server.php?s=current-trunk-version', function(data) {
        			var title = 'Trunk notifier';
        			//prevent double notifiers
        			if(data.trunkNotifier && checkForDoubleNotifier(title)==false){
	                    $.pnotify({
	                        title: title,
	                        text: 'There is a new Trunk release.<br><br><font color="red">Please keep in mind that Trunk releases are not supported!</font>',
	                        type: 'warning',
	                        nonblock: true,
	                    });
        			}
        		});
        	}
        }
    });
}

function checkForDoubleNotifier(title){
	//loop through all pnotify's to see if we already have a "pass" pnotify
	var passSecShown = false;
	$('.ui-pnotify-title').each(function(index){
		// if we have one...
		if(($(this).text()==title) && (passSecShown == false)){
			passSecShown = true;
		}
	});
	return passSecShown;
}


function startUpdaterMonitor(updateNotice, button) {
	$('#content').append('<div id="updaterMonitor"></div>');
	
	$.ajax({
         url : 'js/templates/updater-status.hb',
         success : function(source) {
             var template = Handlebars.compile(source);
             var refreshIntervalId = 0;
             
             var monitor = function() {
            	 $.getJSON("../tmp/update.json", function(data){
            		 var html = template({'data' : data });
            		 $('#updaterMonitor').html(html);
            		 
            		 if (data.state !== 'busy') {
            			 clearInterval(refreshIntervalId);
            			 if (updateNotice.pnotify_remove) updateNotice.pnotify_remove();
            			 button.attr('disabled', false);
            		 }
            	 });
             };
             
             refreshIntervalId = setInterval(monitor, 1500); // 1,5 seconds
         },
         dataType : 'text'
     });
}

function init_dataMaintenance() {
	$('#site-title').text("Database Maintenance");
	$('#page-title').text("Use with care");
	$('#content').append('<div id="dbtable" style="height: 400px; border: 1px solid red"></div>')
	
	$.getJSON('admin-server.php?s=dbm_getTables', function(data) {
		$.ajax({
	         url : 'js/templates/dbm_tables.hb',
	         success : function(source) {
	             var template = Handlebars.compile(source);
        		 var html = template({'data' : data });
        		 $('#updaterMonitor').html(html);
        		 $('#sidebar').html(html);
        		 $('button').bind('click', function(){$('#page-title').text($(this).attr('id'));});
        		 $('#page-title').text("Use with care");
        		 
        		 
        		 // Get data and display in grid
        		 
        		  var grid;
        		  var columns = [
        		    {id: "title", name: "Title", field: "title"},
        		    {id: "duration", name: "Duration", field: "duration"},
        		    {id: "%", name: "% Complete", field: "percentComplete"},
        		    {id: "start", name: "Start", field: "start"},
        		    {id: "finish", name: "Finish", field: "finish"},
        		    {id: "effort-driven", name: "Effort Driven", field: "effortDriven"}
        		  ];

        		  var options = {
        		    enableCellNavigation: true,
        		    enableColumnReorder: false
        		  };

        		  $(function () {
        		    var data = [];
        		    for (var i = 0; i < 500; i++) {
        		      data[i] = {
        		        title: "Task " + i,
        		        duration: "5 days",
        		        percentComplete: Math.round(Math.random() * 100),
        		        start: "01/01/2009",
        		        finish: "01/05/2009",
        		        effortDriven: (i % 5 == 0)
        		      };
        		    }

        		    grid = new Slick.Grid("#dbtable", data, columns, options);
        		  })
	         },
	         dataType : 'text'
	     });
	});
}


function KWHcalc(object, Perc, Month){
	if($("#totalKWHProd").val()==0){
		$("#totalKWHProd").val(1000);
	}
	if ($("#totalKWHProd").val()>=12){
		if($(object).attr('id') == 'totalKWHProd'){
			var totalProd = $("#totalKWHProd").val();
			$(Perc).each(function(index) {
					var kwh = totalProd * Perc[index]/100;
					$("#"+Month[index]+"PER").val(Math.round(Perc[index]*10)/10);
					$("#"+Month[index]+"KWH").val(Math.round(kwh*1)/1);
					setPercentBar(Month[index],kwh,totalProd);
			});
		}else{
			sumKWHtotal();
			var totalProd = $("#totalKWHProd").val();
			$(Perc).each(function(index) {
					var kwh = $("#"+Month[index]+"KWH").val();
					var monthPER= Math.round(((kwh/totalProd)*100)*100)/100;
					$("#"+Month[index]+"PER").val(monthPER);
					setPercentBar(Month[index],kwh,totalProd);
			});
		}
	}
}

function sumKWHtotal(){
	var sum = $("input[id$='KWH']").sum();
	$("#totalKWHProd").val(sum);
}

function setPercentBar(month,KWH,KWHTotal) {
	var monthBarHeight = $("p[class=monthBAR]").height();
	var monthPER= Math.round(((KWH/KWHTotal)*100)*100)/100;
	var top = monthBarHeight-monthBarHeight/100*monthPER*4;
	var height =monthBarHeight/100*monthPER*4;

	if (height > monthBarHeight){height = monthBarHeight;top = 0;}
	
	$("img[id="+month+"BAR]").css('top', top);
	$("img[id="+month+"BAR]").css('height',height);
}


function runFunction(name, arguments){
    var fn = window[name];
    if(typeof fn !== 'function'){
        return;
    }
    fn.apply(window, arguments);
}

function setTitle(title) {
	var baseTitle = "WSL :: Configuration :: ";
	$('#site-title').text(baseTitle + title);
}