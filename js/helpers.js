/* Handlebars Helpers - Dan Harper (http://github.com/danharper) */

/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details. */

/**
 *  Following lines make Handlebars helper function to work with all
 *  three such as Direct web, RequireJS AMD and Node JS.
 *  This concepts derived from UMD.
 *  @courtesy - https://github.com/umdjs/umd/blob/master/returnExports.js
 */

(function (root, factory) {
    if (typeof exports === 'object') {
        // Node. Does not work with strict CommonJS, but
        // only CommonJS-like enviroments that support module.exports,
        // like Node.
        module.exports = factory(require('handlebars'));
    } else if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['handlebars'], factory);
    } else {
        // Browser globals (root is window)
        root.returnExports = factory(root.Handlebars);
    }
}(this, function (Handlebars) {
    /**
     * If Equals
     * if_eq this compare=that
     */
    Handlebars.registerHelper('if_eq', function(context, options) {
        if (context == options.hash.compare)
            return options.fn(this);
        return options.inverse(this);
    });

    /**
     * Unless Equals
     * unless_eq this compare=that
     */
    Handlebars.registerHelper('unless_eq', function(context, options) {
        if (context == options.hash.compare)
            return options.inverse(this);
        return options.fn(this);
    });


    /**
     * If Greater Than
     * if_gt this compare=that
     */
    Handlebars.registerHelper('if_gt', function(context, options) {
    	if(options.hash.compare){
    		options.hash.compare= options.hash.compare.replace(",",".");
    	}
    	
        if (context > options.hash.compare)
            return options.fn(this);
        return options.inverse(this);
    });

    /**
     * Unless Greater Than
     * unless_gt this compare=that
     */
    Handlebars.registerHelper('unless_gt', function(context, options) {
        if (context > options.hash.compare)
            return options.inverse(this);
        return options.fn(this);
    });


    /**
     * If Less Than
     * if_lt this compare=that
     */
    Handlebars.registerHelper('if_lt', function(context, options) {
    	options.hash.compare= options.hash.compare.replace(",",".");
        if (context < options.hash.compare)
            return options.fn(this);
        return options.inverse(this);
    });

    /**
     * Unless Less Than
     * unless_lt this compare=that
     */
    Handlebars.registerHelper('unless_lt', function(context, options) {
        if (context < options.hash.compare)
            return options.inverse(this);
        return options.fn(this);
    });


    /**
     * If Greater Than or Equal To
     * if_gteq this compare=that
     */
    Handlebars.registerHelper('if_gteq', function(context, options) {
        if (context >= options.hash.compare)
            return options.fn(this);
        return options.inverse(this);
    });

    /**
     * Unless Greater Than or Equal To
     * unless_gteq this compare=that
     */
    Handlebars.registerHelper('unless_gteq', function(context, options) {
        if (context >= options.hash.compare)
            return options.inverse(this);
        return options.fn(this);
    });


    /**
     * If Less Than or Equal To
     * if_lteq this compare=that
     */
    Handlebars.registerHelper('if_lteq', function(context, options) {
        if (context <= options.hash.compare)
            return options.fn(this);
        return options.inverse(this);
    });

    /**
     * Unless Less Than or Equal To
     * unless_lteq this compare=that
     */
    Handlebars.registerHelper('unless_lteq', function(context, options) {
        if (context <= options.hash.compare)
            return options.inverse(this);
        return options.fn(this);
    });
    
    
    /**
     * String contains
     * str_contains this look_for=that
     */
    Handlebars.registerHelper('str_contains', function(context, options) {
    	if(context.indexOf(options.hash.look_for))
                return options.inverse(this);
        return options.fn(this);
    });
    
    /**
     * Convert new line (\n\r) to <br>
     * from http://phpjs.org/functions/nl2br:480
     */
    Handlebars.registerHelper('nl2br', function(text) {
        var nl2br = (text + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br>' + '$2');
        return new Handlebars.SafeString(nl2br);
    });

	//  format an ISO date using Moment.js
	//  http://momentjs.com/
	//  moment syntax example: moment(Date("2011-07-18T15:50:52")).format("MMMM YYYY")
	//  usage: {{timestampDateFormat creation_date format="MMMM YYYY"}}
	Handlebars.registerHelper('timestampDateFormat', function(context, block) {
		var context = context * 1000;
		if (window.moment && context && moment(context).isValid()) {
			var f = block.hash.format || "MMM Do, YYYY";
			return moment(context).format(f);
		}else{
			return context;   //  moment plugin is not available, context does not have a truthy value, or context is not a valid date
		}
	});
	
	/**
	 * Convert Month number into month name 1 = januari .. 12 = december.  
	 */
	var monthNames = ['januari', 'februari', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
	Handlebars.registerHelper('monthName', function(context, block) {
		return monthNames[context-1];
	});
	
	
	/**
	 * returns a checkbox with an companion hidden text field. 
	 */
	Handlebars.registerHelper("checkboxWithHidden", function(name, state, value,classAttr) {
		if(state==1 || state==true || state == 'checked'|| state == 'true'){
			var checked = 'checked';
		}else{
			var checked = '';
		}
		if (typeof value === 'object' || value == '') {
			value = 1;
		}
		if (typeof classAttr === 'object' || classAttr == '') {
			classAttr = '';
		}
		return new Handlebars.SafeString("<input type=\"hidden\" name=\""+name+"\" value=\"0\"/>" +
				"<input type=\"checkbox\" "+checked+"  name=\""+name+"\" class=\""+classAttr+"\" value=\""+value+"\"/>");
	});
    

	Handlebars.registerHelper("infoTooltip", function(context,block) {
		return new Handlebars.SafeString("<span><img src=\"images/information.png\" class=\"tooltip positioning\" title=\""+context.hash.title+"\"/></span>");
	});
    
	

	/**
	 * Capitalizes the first letter of a string.
	 */
	Handlebars.registerHelper("capitalizer", function(context,block) {
		//console.log(context.hash.title);
		if(context.hash.title != ''){
		if(typeof(context.hash.title) != 'undefined'){
			if(context.hash.title != null ){
				return new Handlebars.SafeString(WSL.capitalize(context.hash.title));
			}
		}
		}else{
			return context.hash.title;
		}
		
	});

	/**
	 * 
	 */
	Handlebars.registerHelper("humanIndexKey", function(context,block) {
		return new Handlebars.SafeString(parseInt(context.hash.int+1));
	});

	/**
	 * Returns a div with release info for the updater
	 */
	Handlebars.registerHelper("updaterVersionsList", function(context,block) {
		var list = '<div class="span span-30">';
		$.each( context, function( key, type ) {
			list += '<div id="'+key+'"></div><ul style="list-style-type: none;"><li>'+WSL.capitalize(key)+"";
			$.each( type, function( key, value ) {
				list += '<ul><li style="list-style-type: none;"><input type="radio" name="version" value="'+value.name+'*'+value.revision+'*'+value.timestamp+'*'+value.description+'"><span style="color:'+value.displayColor+'">&nbsp;&nbsp;'+value.displayName+'</span></input>';
				list += '<ul><li>Release date(revision):</li>';
				list += '<li style="list-style-type: none;">'+moment(value.timestamp*1000).format('DD-MM-YYYY HH:mm:ss')+'('+value.revision+')</li>';
				list += '<li>Description:</li><li style="list-style-type: none;">'+value.description+'</li></ul></ul>';
			});
			list += '</li></ul></ul>';
		});
		
		list += '</div>';
    	return new Handlebars.SafeString(list);
    });
	
	Handlebars.registerHelper("toFixed", function (context,block,fixed){
		var value = parseFloat(context.hash.value).toFixed(context.hash.fixed).replace(".",",");
		return new Handlebars.SafeString(value);
	})
	
	
	Handlebars.registerHelper("PvOutputDataRow", function (context){
		// we want to send this record to pvoutputSend;
		var row = null;
		var img = null;
		var sendStatus = null;
		var time = null;
		var timeSend = null;
		var result = null;

		//we wanted to send it
		if(context.pvoutputSend == 1){
			var sendStatus = "true";
			var time = moment(context.time*1000).format("DD-MM-YY HH:mm:ss");
			
			// PVoutput recieved it!
			if(context.pvoutput == 1){
				var img = "<img src=\"images/accept.png\" title=\"pvoutputsend="+context.pvoutputSend +";pvoutput="+context.pvoutput+";timesend="+context.pvoutputSendTime+";\">";
				if(context.pvoutputSendTime>0){
					var timeSend = moment(context.pvoutputSendTime*1000).format("DD-MM-YY HH:mm:ss");
				}else{
					var timeSend = "unknown";
				}
				var result = "recieved by PVo";
				
			}else if(context.pvoutput == 0){
				var img = "<img src=\"images/exclamation.png\" class=\"tooltip\" title=\"pvoutputsend="+context.pvoutputSend+";pvoutput="+context.pvoutput+";timesend="+context.pvoutputSendTime+";\">";
				if(context.pvoutputSendTime>0){
					var timeSend = moment(context.pvoutputSendTime*1000).format("DD-MM-YY HH:mm:ss");
				}else{
					var timeSend = "unknown";
				}
				var result = "NOT recieved by PVo";
			}else{
				var img = "<img src=\"images/exclamation.png\" class=\"tooltip\" title=\"pvoutputsend="+context.pvoutputSend+";pvoutput="+context.pvoutput+";timesend="+context.pvoutputSendTime+";\">";
				if(context.pvoutputSendTime>0){
					var timeSend = moment(context.pvoutputSendTime*1000).format("DD-MM-YY HH:mm:ss");
				}else{
					var timeSend = "unknown";
				}
				var result = "unknown";
			}
		}
		
		// we do not want to send this record to pvoutputSend
		if(context.pvoutputSend == 0 || context.pvoutputSend === undefined){
			var sendStatus = "Not a sendable record.";
			var time = moment(context.time*1000).format("DD-MM-YY HH:mm:ss");
			if(context.pvoutputSendTime>0){
				var timeSend = moment(context.pvoutputSendTime*1000).format("DD-MM-YY HH:mm:ss");
			}else{
				var timeSend = '<span title="sendTime not yet implemented">unknown</span>';
			}
			
			// it is recieved!
			if(context.pvoutput == 1){
				var img = "<img src=\"images/exclamation.png\" class=\"tooltip\" title=\"pvoutputsend="+context.pvoutputSend+";pvoutput="+context.pvoutput+";timesend="+context.pvoutputSendTime+";\">";
				var result = "PVoutput recieved a non sendable record";
			}else if(context.pvoutput == 0){
				var img = "<img src=\"images/accept.png\" class=\"tooltip\" title=\"pvoutputsend="+context.pvoutputSend+";pvoutput="+context.pvoutput+";timesend="+context.pvoutputSendTime+";\">";
				var result = "Non sendable record which is not send :)";
			}
		}
		
		if(context.pvoutputErrorMessage != 0){
			var info = "<img src=\"images/information.png\" class=\"tooltip\" title=\""+context.pvoutputErrorMessage+"\"/>";
		}else{
			var info = "";
		}
		
		var row = ''+
		'<div class="column span-7">'+time+'</div>'+
		'<div class="column span-5">'+sendStatus+'</div>'+
		'<div class="column span-7">'+result+'</div>'+
		'<div class="column span-7">'+timeSend+'</div>'+
		'<div class="column span-2">'+img+''+info+'</div>';
		return new Handlebars.SafeString(row);
	})
	
}));