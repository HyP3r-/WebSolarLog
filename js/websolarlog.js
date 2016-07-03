var beforeLoad = (new Date()).getTime();
window.onload = pageLoadingTime;
var windowState = true;
// Only for developer purposes
var ajax = $.ajaxSetup({
	cache : true
});

// For browser that do not have an console like IE8
if (!window.console)
	console = {
		log : function() {
		}
	};

// activate stickyNavigation bar
$(function() {
	WSL.stickyNavigation();
	// WSL.init_languages("languageSelect");

	// Check if there are upgrade notices
	if (isFront) {
		WSL.api.upgradeMessageShow(function(data) {
			if (data.result == true) {
			WSL.notify.show_bar_top('info', 'Upgrade message','There is an upgrade message, please check them at the admin section');
			}
		});
	}
});

Date.prototype.yyyymmdd = function() {
    var yyyy = this.getFullYear().toString();
    var mm = (this.getMonth() + 1).toString(); // getMonth() is zero-based
    var dd = this.getDate().toString();
    return yyyy + (mm[1] ? mm : "0" + mm[0]) + (dd[1] ? dd : "0" + dd[0]); // padding
};

/**
 * 
 * @param theArray
 * @param goal
 * @returns {Number}
 */
function getClosest(theArray, goal) {
	var closestTimestamp = null;
	var closestValue = null;
	var counter = 0;
	var arrReturn = [];

	$.each(theArray,function(index, value) {
		if (closestTimestamp == null
				|| Math.abs(this[0] - goal) < Math.abs(closestTimestamp - goal)) {
			counter = index;
			closestTimestamp = this[0];
			closestValue = this[1];

		}
	});
	arrReturn.push(counter, closestTimestamp, closestValue);

	return arrReturn;
}

function setGraphTitle(graphTitle, element) {
	$(graphTitle).insertAfter(element + ' .jqplot-grid-canvas');
}

$(window).blur(function() {
	// set time on blur
	window.blurTime = (new Date()).getTime();
	if ($('#stayLiveOnBlur').is(':checked')) {
		windowState = true;
	} else {
		windowState = false;
	}

});
$(window).focus(function() {
	// set current time
	currentTime = (new Date()).getTime();
	// calculate time sinds page load.
	secondesInBlur = (currentTime - window.blurTime) / 1000;
	// The page was for XXX seconds in Blur, we need te reload the whole page...

	if (WSL.connect.settings.useRunOnBlur === true && secondesInBlur > 300) {
		location.reload(true);
	}
	windowState = true;
});
function getWindowsState() {
	return windowState;
}
function ajaxAbort(xhr) {
	// set current time
	currentTime = (new Date()).getTime();
	// calculate time sinds page load.
	secondes = (currentTime - window.beforeLoad) / 1000;
	// when the page is loaded for more then XX sec. we abort ajaxCalls
	// We do this to prevent partial loaded pages
	if (secondes > 30) {
		// the page is loaded more then XX sec ago, abort this call....
		xhr.abort();
	}
}

function generatePanelClearSky(result, roofOrientation, roofPitch, panelPower,date) {
	var gl_date = dateConverter(date, "year+''+month+''+day");
	var longitude = result.slimConfig.long;
	var latitude = result.slimConfig.lat;
	var pv_az = roofOrientation;
	var pv_roof = roofPitch;
	var pv_temp_coeff = -0.48;
	var skydome_coeff = 1;
	var Wp_panels = panelPower;
	var timezone = result.timezoneOffset;
	init_astrocalc(gl_date, longitude, latitude, pv_az, pv_roof, pv_temp_coeff, timezone);


	// timezone correction
	now=new Date();
	ofst=now.getTimezoneOffset()/60;
	correctTimeZoneDiffs = 24-(ofst + result.timezoneOffset);
	
	var coeff = 1000 * 60 * 5;
	var sunrise = new Date(((result.sunInfo.sunrise - 1000)-(correctTimeZoneDiffs*3600)) * 1000); // or use any other date
	
	var sunriseRounded = Math.round(sunrise.getTime() / coeff) * coeff;

	var sunset = new Date((result.sunInfo.sunset-(correctTimeZoneDiffs*3600)) * 1000); // or use any other date
	var sunsetRounded = (Math.round(sunset.getTime() / coeff) * coeff) + coeff * 6;
	
	var maxPower = [];
	var totalPower = 0;
	for ( var i = sunriseRounded; i <= sunsetRounded; i = i + coeff) {
		maxPowerTime = [];
		currentTime = new Date(i);
		if (currentTime.getMinutes() < 10) {
			var minutes = 0 + "" + currentTime.getMinutes();
		} else {
			var minutes = currentTime.getMinutes();
		}
		if (currentTime.getHours() < 10) {
			var hours = 0 + "" + currentTime.getHours();
		} else {
			var hours = currentTime.getHours();
		}
		var coor = azimuthhight(timeStringToFloat(hours + ':' + minutes));
		maxPowerTime.push(i, Math.round(coor.tot_en / 1000 * Wp_panels));
		totalPower = totalPower + Math.round((coor.tot_en / 1000 * Wp_panels) / 12);
		maxPower.push(maxPowerTime);
	}

	totalKWhkWp = Math.round((totalPower / Wp_panels) * 100) / 100;
	totalPower = Math.round((totalPower / 1000) * 100) / 100;

	var result = [];
	result.push(maxPowerTime);
	result.push(maxPower);
	result.push(totalKWhkWp);
	result.push(totalPower);
	return result;
}

function analyticsJSCodeBlock() {
	$.getJSON('server.php?method=analyticsSettings', function(data) {
		if (data.googleSuccess) {
			$.ajax({
				url : 'js/templates/GoogleAnalyticsJSCodeBlock.hb',
				beforeSend : function(xhr) {
					if (getWindowsState() == false) {
						ajaxAbort(xhr);
					}
				},
				success : function(source) {
					var template = Handlebars.compile(source);
					var html = template({
						'data' : data
					});
					$("body").append(html);
				},
				dataType : 'text'
			});
		}
		if (data.piwikSuccess) {
			$.ajax({
				url : 'js/templates/PiwikAnalyticsJSCodeBlock.hb',
				beforeSend : function(xhr) {
					if (getWindowsState() == false) {
						ajaxAbort(xhr);
					}
				},
				success : function(source) {
					var template = Handlebars.compile(source);
					var html = template({
						'data' : data
					});
					$("body").append(html);
				},
				dataType : 'text'
			});
		}
	});
}

function ajaxReady() {
	$('#contentLoading').remove();
	$('#reqLoading').hide();
}

function ajaxStart() {
	$('#reqLoading').show();
	$('.ui-tooltip').remove();
}

jQuery.fn.center = function(left, top, position) {
	$this = $(this);
	var w = $($this.parent());

	if (left == '') {
		left = 0;
	} else {
		left = Math.abs(((w.width() - this.outerWidth()) / 2) + w.scrollLeft());
	}

	if (top == '') {
		top = 0;
	} else {
		top = Math.abs(((w.height() - this.outerHeight()) / 2) + w.scrollTop());
	}

	if (typeof (position) === 'undefined') {
		position = 'absolute';
	} else {
		position = '';
	}
	this.css({'position' : position,'top' : top,'left' : left});

	return this;
}

function modLegenda(plot) {
	// bind to the data highlighting event to make custom tooltip:
	$('#detailsGraph').bind('jqplotDataMouseOver',function(ev, seriesIndex, pointIndex, data) {
		var i = 0;
		$('td.jqplot-table-legend').each(
			function() {
				if (seriesIndex == i) {
					bold = ['<b><font color="red">','</font></b>' ];
				} else {
					bold = [ '', '' ];
				}
				if (typeof handle.data[i] !== 'undefined') {
					$('#tooltipValue-' + i).html(bold[0]+ handle.data[i][pointIndex][1]+ bold[1]);
				} else {
					$('#tooltipValue-' + i).text('-');
				}
				i = i + 1;
			})
	});

	$('#detailsGraph').bind('jqplotDataUnhighlight', function() {
		var i = 0;
		$('td.jqplot-table-legend').each(function() {
			$this = $(this);
			// walkthrough array with hidden lines
			if ($this.text().length > 0) {
				$('#tooltipValue-' + i).text('-');
				i = i + 1;
			}

		});
	});
	// $('table.jqplot-table-legend').attr('class','jqplot-table-legend-custom');
	$('table.jqplot-table-legend').wrapInner('<div class="column span-21 first" id="legendWrappers" style="margin:0px;">');
	// walkthrough legenda
	var i = 0;
	var newLegend = '';
	$('td.jqplot-table-legend').each(function() {
		$this = $(this);
		// walkthrough array with hidden lines
		if ($this.text().length > 0) {
			newLegend += '<div class="column span-5 first" id="' + i
					+ '" style="margin:0px;">' + $this.text()
					+ '<br><span id="tooltipValue-' + i
					+ '">-</span></div>';
			i = i + 1;
		}

	});
	var margin = (i / 5) * 15;
	$('#legendWrappers').after('<div class="column span-21 last">' + newLegend+ '<br><div style="clear:both;"></div></div>');
	$("#detailsGraph").css('margin-bottom', margin);
	$('#detailsSwitches').css('margin-top', '50px');
	$('#detailsSwitches').hide();
	$('table.jqplot-table-legend').css('left', 0);
	$('table.jqplot-table-legend').css('top', 310);
	$('table.jqplot-table-legend').css('width', 850);
}

function pageLoadingTime() {
	afterLoad = (new Date()).getTime();
	secondes = (afterLoad - beforeLoad) / 1000;
	document.getElementById("JSloadingtime").innerHTML = secondes;
}

function is_array(input) {
	return typeof (input) == 'object' && (input instanceof Array);
}

var graphDay = null;
var dataDay = [];
var dataYesterday = [];
var dataLastDays = [];
var alreadyFetched = [];

var currentGraphHandler;
var todayTimerHandler;

function tooltipTodayContentEditor(str, seriesIndex, pointIndex, plot, series) {
	var returned = "";
	seriesCount = plot.series.length - 1;

	$.each(plot.series,	function(index, value) {
		eachSerieIndex = index;
		if (index != seriesIndex) {
			ClosestPointIndex = getClosest(plot.series[eachSerieIndex].data,plot.series[seriesIndex].data[pointIndex][0]);
			closestTimeDiff = plot.series[seriesIndex].data[pointIndex][0]- plot.series[eachSerieIndex].data[ClosestPointIndex[0]][0];
			(closestTimeDiff < -200000) ? displayLow = false : displayLow = true;
			(closestTimeDiff > 200000) ? displayHigh = false : displayHigh = true;

			if (displayHigh == true && displayLow == true && plot.series[eachSerieIndex].show == true) {
				returned += tooltipTodayContentEditorLine(
						plot.series[eachSerieIndex].label,"   "+ plot.series[eachSerieIndex].data[ClosestPointIndex[0]][1],false);
			}
		} else {
			ClosestPointIndex = getClosest(plot.series[eachSerieIndex].data,plot.series[seriesIndex].data[pointIndex][0]);
			returned += tooltipTodayContentEditorLine(plot.series[eachSerieIndex].label,"   "+ plot.series[eachSerieIndex].data[ClosestPointIndex[0]][1],true);
		}
	});
	var time = timeConverter(plot.series[seriesIndex].data[pointIndex][0]," hour+':'+min ;");
	returned += tooltipTodayContentEditorLine("Time", "   " + time, true);
	return returned;
}

function tooltipPeriodContentEditor(str, seriesIndex, pointIndex, plot, series) {
	var returned = "";
	returned += tooltipDefaultLine("Day ", plot.series[0].data[pointIndex][1],"kWh", (seriesIndex == 0));
	returned += tooltipDefaultLine("Month Cum.",plot.series[1].data[pointIndex][1], "kWh", (seriesIndex == 1));
	var time = timeConverter(plot.series[seriesIndex].data[pointIndex][0]," day+' '+month_name ;");
	returned += tooltipTodayContentEditorLine("Time", "   " + time, true);
	return returned;
}

function tooltipProductionContentEditor(str, seriesIndex, pointIndex, plot, series) {
	var returned = "";
	var diff_add = '';
	var yearDiff_add = "";
	var diff = plot.series[0].data[pointIndex][1] - plot.series[1].data[pointIndex][1];

	var yearDiff = plot.series[3].data[pointIndex][1] - plot.series[2].data[pointIndex][1];
	(diff > 0) ? diff_add = '+' : diff_add = diff_add;
	(yearDiff > 0) ? yearDiff_add = '+' : yearDiff_add = yearDiff_add;
	returned += tooltipProductionContentEditorLine("Harvested", plot.series[0].data[pointIndex][1], "kWh", (seriesIndex == 0));
	returned += tooltipProductionContentEditorLine("Expected", plot.series[1].data[pointIndex][1], "kWh", (seriesIndex == 1));
	returned += tooltipProductionContentEditorLine("This month", diff_add + "" + diff, "kWh", false);
	returned += tooltipProductionContentEditorLine("Cum. Harvested", plot.series[3].data[pointIndex][1], "kWh", (seriesIndex == 3));
	returned += tooltipProductionContentEditorLine("Cum. Expected", plot.series[2].data[pointIndex][1], "kWh", (seriesIndex == 2));
	returned += tooltipProductionContentEditorLine("This year", yearDiff_add + "" + yearDiff, "kWh", false);
	return returned;
}

if (!String.prototype.trim) {
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g, '');
	};
}

function tooltipCompareEditor(str, seriesIndex, pointIndex, plot, series) {
	var returned = "";

	if ($.isArray(plot.series[1].data[pointIndex])) {
		returned += tooltipCompareEditorLine(plot.series[1].label, plot.series[1].data[pointIndex][1], "kWh", (seriesIndex == 1));
		returned += tooltipCompareEditorLine("date", timeConverter(plot.series[1].data[pointIndex][0], " day+'-'+month+'-'+year ;"), " ", (seriesIndex == 1));
	} else {
		returned += tooltipCompareEditorLine("Expected", "No data available for " + plot.series[1].data[pointIndex][2], " ", (seriesIndex == 0));
	}

	if ($.isArray(plot.series[0].data[pointIndex])) {
		returned += tooltipCompareEditorLine(plot.series[0].label,plot.series[0].data[pointIndex][1], "kWh", (seriesIndex == 0));
		returned += tooltipCompareEditorLine("date", timeConverter( plot.series[0].data[pointIndex][0]," day+'-'+month+'-'+year ;", 'date'), " ", (seriesIndex == 0));
	} else {
		returned += tooltipCompareEditorLine("Harvested","No data available for " + plot.series[0].data[pointIndex][2]," ", (seriesIndex == 1));
	}
	return returned;
}

function tooltipDetailsContentEditor(str, seriesIndex, pointIndex, plot, series) {
	var returned = "";
	return returned;
}

function tooltipTodayContentEditorLine(label, value, sign, isBold) {
	return tooltipDefaultLine(label, value, sign, isBold);
}

function tooltipProductionContentEditorLine(label, value, sign, isBold) {
	return tooltipDefaultLine(label, value, sign, isBold);
}

function tooltipCompareEditorLine(label, value, sign, isBold) {
	return tooltipDefaultLine(label, value, sign, isBold);
}

function tooltipDefaultLine(label, value, isBold) {
	bold = (isBold) ? [ '<b>', '</b>' ] : bold = [ '', '' ];
	line = bold[0] + '<span class="jqplot_hl_label">' + label + ":" + "</span>"
			+ '<span class="jqplot_hl_value">' + value + "</span>" + bold[1] + "<br />";
	return line;
}

function timeConverter(timestamp, format) {
	var a = new Date(timestamp);
	var months = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];
	var year = a.getFullYear();
	var month_name = months[a.getMonth()];
	var month = a.getMonth() + 1;
	var day = a.getDate();
	var hour = twoDigits(a.getHours());
	var min = twoDigits(a.getMinutes());
	var sec = twoDigits(a.getSeconds());
	var time = eval(format);

	return time;
}

function dateConverter(date, format) {
	var a = date.split('-');
	var year = a[2];
	var month = a[1];
	var day = a[0];
	var time = eval(format);
	return time;
}

function timeStringToFloat(time) {
	var hoursMinutes = time.split(/[.:]/);
	var hours = parseInt(hoursMinutes[0], 10);
	var minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;
	return hours + minutes / 60;
}

function twoDigits(value) {
	if (value < 10) {
		return '0' + value;
	}
	return value;
}

function handleGraphs(request, devicenum) {
	// set inverter
	devicenum = $('#devicenum').val();
	// get activated Tab;
	var tabSelected = $('#tabs').tabs('option', 'active');

	// set type to Today
	var tab = 'Today';
	// set date
	var date = $('#datepicker').val();

	(tabSelected == 0) ? tab = 'Today' : tab = tab;
	(tabSelected == 1) ? tab = 'Yesterday' : tab = tab;
	(tabSelected == 2) ? tab = 'Month' : tab = tab;
	(tabSelected == 3) ? tab = 'Year' : tab = tab;
	var period = tab;

	if (currentGraphHandler) {
		currentGraphHandler.destroy();
		$("#graph" + tab + "Content").html('<div id="loading" style="width:1px;height:20px;">loading...</div>');
		$("#loading").center();
	}
	if (todayTimerHandler) {
		window.clearInterval(todayTimerHandler);
	}

	if (request == 'picker') {
		
		$('#lastCall').val('picker');
		period = $('#pickerPeriod').val();
		if (period == "Today") {
			WSL.createDayGraph(devicenum, "Today", tab, date, currentGraphHandler, function(handler) {
				currentGraphHandler = handler;
				$("#loading").remove();
				$("#graphReady").val('true').trigger('change');
			});
		} else {
			WSL.createPeriodGraph(devicenum, period, 1, date, "graph" + tab + "Content", function(handler) {
				currentGraphHandler = handler;
				$("#loading").remove();
				$("#graphReady").val('true').trigger('change');
			});
		}
	} else {
		$('#lastCall').val('normal');
		if (tab == "Today" || tab == "Yesterday") {
			if (tab == "Yesterday") {
				date = moment().subtract('days', 1).format('DD-MM-YYYY');
			}
			WSL.createDayGraph(devicenum, "Today", tab, date,
					currentGraphHandler, function(handler) {
						currentGraphHandler = handler;
						$("#loading").remove();
						$("#graphReady").val('true').trigger('change');
					});
		} else {
			WSL.createPeriodGraph(devicenum, period, 1, date, "graph" + tab + "Content", function(handler) {
				currentGraphHandler = handler;
				$("#loading").remove();
				$("#graphReady").val('true').trigger('change');
			});
		}
	}
	// Refresh only the Today tab
	if (tab == "Today" && $('#lastCall').val() == 'normal') {
		todayTimerHandler = window.setInterval(function() {
			WSL.createDayGraph(devicenum, "Today", tab, date,
					currentGraphHandler, function(handler) {
						currentGraphHandler = handler;
						$("#loading").remove();
						$("#graphReady").val('true').trigger('change');
					});
		}, 90000); // every minute
	}
}

function populateTabs() {
	$.getJSON('server.php?method=getPeriodFilter&type=all', function(data) {
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
						handleGraphs('picker', devicenum);
					}
				});
				$("#datepicker").datepicker("option", "dateFormat","dd-mm-yy");

				$("#datepicker").datepicker('setDate', new Date());

				// fix for Graph Tooltip
				$("#datepicker").css('z-index', 0);
				// fix for Graph Tooltip

				var devicenum = $('#devicenum').val();

				$('#next').unbind('click');
				$('#previous').unbind('click');
				$('#pickerPeriod').unbind('click');
				$('#devicenum').unbind('click');

				$('#devicenum').click(function() {
					var picker = $("#datepicker");
					var date = new Date(picker.datepicker('getDate'));
					picker.datepicker('setDate', date);
					handleGraphs('picker', devicenum);
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
					handleGraphs('picker', devicenum);
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
					handleGraphs('picker', devicenum);
				});
				handleGraphs('standard', devicenum);
			},
			dataType : 'text'
		});
	});
}

var gaugeGP;
var gaugeIP;
var gaugeEFF;
var graphProductionSettings = new Object();

var graphProductionSettings = function() {
	return {
		series : [ {
			label : 'Harvested(kWh)',
			yaxis : 'yaxis',
			pointLabels : {
				show : false
			},
			renderer : $.jqplot.BarRenderer,
			rendererOptions : {
				barPadding : 5, // number of pixels between adjacent bars in the same group (same category or bin).
				barMargin : 5, // number of pixels between adjacent groups of bars.
				barDirection : 'vertical', // vertical or horizontal.
				barWidth : 15, // width of the bars. null to calculate automatically.
				shadowOffset : 2, // offset from the bar edge to stroke the shadow.
				shadowDepth : 5, // nuber of strokes to make for the shadow.
				shadowAlpha : 0.2, // transparency of the shadow.
			},
			min : 0,
		}, {
			label : 'Expected(kWh)',
			yaxis : 'y2axis',
			pointLabels : {
				show : false
			},
			renderer : $.jqplot.BarRenderer,
			rendererOptions : {
				barPadding : 5, // number of pixels between adjacent bars in the same group (same category or bin).
				barMargin : 5, // number of pixels between adjacent groups of bars.
				barDirection : 'vertical', // vertical or horizontal.
				barWidth : 15, // width of the bars. null to calculate automatically.
				shadowOffset : 2, // offset from thebar edge tostroke theshadow.
				shadowDepth : 5, // nuber of strokesto make for theshadow.
				shadowAlpha : 0.2, // transparency of the shadow.
			},
			min : 0,
		}, {
			label : 'Cum. Expected(kWh)',
			yaxis : 'y3axis',
			renderer : $.jqplot.LineRenderer,
			pointLabels : {
				show : false
			}
		}, {
			label : 'Cum. Harvested(kWh)',
			yaxis : 'y4axis',
			renderer : $.jqplot.LineRenderer,
			pointLabels : {
				show : false
			}
		}, 
		],
		legend : {
			show : true,
			location : 'nw', // compass direction, nw, n, ne, e, se, s, sw, w.
			xoffset : 12, // pixel offset of the legend box from the x (or x2) axis.
			yoffset : 12, // pixel offset of the legend box from the y (or y2) axis.
		},
		axes : {
			xaxis : {
				show : true, // wether or not to renderer the axis. Determined automatically.
				pad : 1, // a factor multiplied by the data range on the axis to give the axis range so that data points don't fall on the edges of the axis.
				ticks : [], // a 1D [val1, val2, ...],or 2D [[val, label], [val, label], ...] array of ticks to use. Computed automatically.
				renderer : $.jqplot.CategoryAxisRenderer, // renderer to use to draw the axis,
				labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
				tickRenderer : $.jqplot.CanvasAxisTickRenderer,
				rendererOptions : {}, // options to pass to the renderer. LinearAxisRenderer has no options,
				tickOptions : {
					mark : 'outside', // Where to put the tick mark on the axis'outside', 'inside' or 'cross',
					showMark : true,
					showGridline : true, // wether to draw a gridline (across the whole grid) at this tick,
					markSize : 4, // length the tick will extend beyond the grid in pixels. For 'cross', length will be added above and below the grid boundary,
					show : true, // wether to show the tick (mark and label),
					showLabel : true, // wether to show the text label at the tick,
					formatString : '', // format string to use with the axis tick formatter
				},
				showTicks : true, // wether or not to show the tick labels,
				showTickMarks : true, // wether or not to show the tick marks
			},
			yaxis : {
				label : 'Harvested(kWh)',
				min : 0,
				labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
			},
			y2axis : {
				label : 'Expected(kWh)',
				min : 0,
				labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
			},
			y3axis : {
				label : 'Cum. Expected(kWh)',
				min : 0,
				labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
			},
			y4axis : {
				label : 'Cum. Harvested(kWh)',
				min : 0,
				labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
			},
		},
		highlighter : {
			tooltipContentEditor : tooltipProductionContentEditor,
			show : true,
			tooltipLocation : 'n'
		},
		cursor : {
			show : false
		},
	};
	//return graphProductionOptions;
};

// WSL class
var WSL = {
	api : {},
	connect : {},
	template : {},
	scrollTo : {},
	stickyNavigation : {},
	capitalize : {},
	init_nextRelease : function(divId) {
		$(divId).html("<br/><br/><H1>WSL::NextRelease();</h1>");
	},
	init_PageTodayHistoryValues : function(divId) {
		ajaxStart();
		// Retrieve the error events
		WSL.api.getHistoryValues(function(data) {
			$.ajax({
				url : 'js/templates/historyValues.hb',
				beforeSend : function(xhr) {
					if (getWindowsState() == false) {
						ajaxAbort(xhr);
					}
				},
				success : function(source) {
					var template = Handlebars.compile(source);
					var html = template({
						'data' : data.dayData.history
					});
					$('#historyContainer').html(html);
					$("#todayHistoryAcc").accordion({
						collapsible : true
					});
					ajaxReady();
				},
				dataType : 'text'
			});
		});
	},

	init_PageIndexLiveValues : function(divId) {
		// initialize languages selector on the given div
		ajaxStart();

		if (getWindowsState() == false) {
			WSL.api.getPageIndexBlurLiveValues(function(data) {
				document.title = '(' + data.sumInverters.totalSystemACP + ' W) WebSolarLog';
			});
		} else {
			WSL.api.getPageIndexLiveValues(function(data) {
				GP = data.maxGauges / 10;
				gaugeGPOptions = {
					title : data.lang.ACPower,
					grid : {
						background : '#FFF'
					},
					seriesDefaults : {
						renderer : $.jqplot.MeterGaugeRenderer,
						rendererOptions : {
							min : 0,
							max : GP * 10,
							padding : 0,
							intervals : [ GP, GP * 2, GP * 3, GP * 4, GP * 5, GP * 6, GP * 7, GP * 8, GP * 9, GP * 10 ],
							intervalColors : [ '#F9FFFB', '#EAFFEF', '#CAFFD8',
									'#B5FFC8', '#A3FEBA', '#8BFEA8', '#72FE95',
									'#4BFE78', '#0AFE47', '#01F33E' ]
						}
					}
				};
				IP = data.maxGauges / 10;
				gaugeIPOptions = {
					title : data.lang.DCPower,
					grid : {
						background : '#FFF'
					},
					seriesDefaults : {
						renderer : $.jqplot.MeterGaugeRenderer,
						rendererOptions : {
							min : 0,
							max : IP * 10,
							padding : 0,
							intervals : [ IP, IP * 2, IP * 3, IP * 4, IP * 5, IP * 6, IP * 7, IP * 8, IP * 9, IP * 10 ],
							intervalColors : [ '#F9FFFB', '#EAFFEF', '#CAFFD8',
									'#B5FFC8', '#A3FEBA', '#8BFEA8', '#72FE95',
									'#4BFE78', '#0AFE47', '#01F33E' ]
						}
					}
				};
				EFF = 100 / 10;
				gaugeEFFOptions = {
					title : data.lang.Efficiency,
					grid : {
						background : '#FFF'
					},
					seriesDefaults : {
						renderer : $.jqplot.MeterGaugeRenderer,
						rendererOptions : {
							min : 0,
							max : EFF * 10,
							padding : 0,
							intervals : [ EFF, EFF * 2, EFF * 3, EFF * 4, EFF * 5, EFF * 6, EFF * 7, EFF * 8, EFF * 9, EFF * 10 ],
							intervalColors : [ '#F9FFFB', '#EAFFEF', '#CAFFD8',
									'#B5FFC8', '#A3FEBA', '#8BFEA8', '#72FE95',
									'#4BFE78', '#0AFE47', '#01F33E' ]
						}
					}
				};
				delete data;

				$(divId).html(WSL.template.get('liveInverters', {
					'data' : data,
					'lang' : data.lang
				}));
				if (gaugeGP) {
					gaugeGP.destroy();
				}
				if (gaugeEFF) {
					gaugeEFF.destroy();
				}
				if (gaugeIP) {
					gaugeIP.destroy();
				}

				$('#gaugeGP').empty();
				gaugeGP = $.jqplot('gaugeGP', [ [ 0.1 ] ], gaugeGPOptions);
				gaugeGP.series[0].data = [ [ 'W', data.sumInverters.totalSystemACP ] ];
				gaugeGP.series[0].label = data.sumInverters.totalSystemACP;
				document.title = '(' + data.sumInverters.totalSystemACP + ' W) WebSolarLog';
				gaugeGP.replot();

				$('#gaugeIP').empty();
				gaugeIP = $.jqplot('gaugeIP', [ [ 0.1 ] ], gaugeIPOptions);
				gaugeIP.series[0].data = [ [ 'W', data.sumInverters.totalSystemIP ] ];
				gaugeIP.series[0].label = data.sumInverters.totalSystemIP;
				gaugeIP.replot();

				$('#gaugeEFF').empty();
				gaugeEFF = $.jqplot('gaugeEFF', [ [ 0.1 ] ], gaugeEFFOptions);
				gaugeEFF.series[0].data = [ [ 'W', data.sumInverters.EFF ] ];
				gaugeEFF.series[0].label = data.sumInverters.EFF + ' %';
				gaugeEFF.replot();

				ajaxReady();
			});
		}
	},

	init_misc : function(devicenum, divId) {
		// Retrieve the error events
		ajaxStart();
		WSL.api.getMisc(devicenum, function(data) {
			$.ajax({
				url : 'js/templates/misc.hb',
				beforeSend : function(xhr) {
					if (getWindowsState() == false) {
						ajaxAbort(xhr);
					}
				},
				success : function(source) {
					var template = Handlebars.compile(source);
					var html = template({
						'lang' : data.lang,
						'data' : data
					});
					$(divId).html(html);
					$(".accordion").accordion({
						collapsible : true
					});
					ajaxReady();
				},
				dataType : 'text'
			});
		});
	},

	init_plantInfo : function(devicenum, divId) {
		ajaxStart();
		// Retrieve the error events
		WSL.api.getPlantInfo(devicenum, function(data) {
			if (data.plantInfo.success) {
				$.ajax({
					url : 'js/templates/plantinfo.hb',
					beforeSend : function(xhr) {
						if (getWindowsState() == false) {
							ajaxAbort(xhr);
						}
					},
					success : function(source) {
						var template = Handlebars.compile(source);
						var html = template({
							'data' : data.plantInfo
						});
						$(divId).html(html);
						ajaxReady();
					},
					dataType : 'text'
				});
			}
		});
	},

	init_menu : function(divId) {
		ajaxStart();
		WSL.api.getMenu(function(data) {
			$.ajax({
				url : 'js/templates/menu.hb',
				beforeSend : function(xhr) {
					if (getWindowsState() == false) {
						ajaxAbort(xhr);
					}
				},
				success : function(source) {
					var template = Handlebars.compile(source);
					var html = template({
						'data' : data
					});
					$(divId).html(html);
					ajaxReady();
				},
				dataType : 'text'
			});
		});
	},
	init_languages : function(divId) {
		ajaxStart();
		// initialize languages selector on the given div
		WSL.api.getLanguages(function(data) {
			$.ajax({
				url : 'js/templates/languageselect.hb',
				beforeSend : function(xhr) {
					if (getWindowsState() == false) {
						ajaxAbort(xhr);
					}
				},
				success : function(source) {
					var template = Handlebars.compile(source);
					var html = template({
						'data' : data
					});
					$('#' + divId).html(html);
					ajaxReady();
				},
				dataType : 'text'
			});
		});
	},

	init_mainSummary : function(divId) {
		var currentTime = new Date();
		date = (currentTime.getDate()) + "-" + (currentTime.getMonth() + 1) + "-" + currentTime.getFullYear();
		WSL.api.mainSummary(date, function(data) {
			$.ajax({
				url : 'js/templates/mainSummary.hb',
				beforeSend : function(xhr) {
					if (getWindowsState() == false) {
						ajaxAbort(xhr);
					}
				},
				success : function(source) {
					var template = Handlebars.compile(source);
					var html = template({
						'data' : data
					});
					$(html).prependTo(divId);


					// timezone correction
					now=new Date();
					ofst=now.getTimezoneOffset()/60;
					correctTimeZoneDiffs = ofst + data.timezoneOffset;
					// /timezone correction
					
					
					var cId = data.total.weather.conditionId;
					var sunDown = data.total.sunDown;

					if (cId >= 200 && cId <= 250) {
						var conditionImage = 'images/weather/11d.png';
					} else if (cId >= 300 && cId <= 350) {
						var conditionImage = 'images/weather/09d.png';
					} else if (cId >= 500 && cId <= 550) {
						var conditionImage = 'images/weather/10d.png';
					} else if (cId >= 600 && cId <= 650) {
						var conditionImage = 'images/weather/13d.png';
					} else if (cId >= 700 && cId <= 750) {
						var conditionImage = 'images/weather/50d.png';
					} else if (cId >= 800 && cId <= 850) {
						if (sunDown == true) {
							var string = 'n';
						} else {
							var string = 'd';
						}
						if (cId == 800) {
							var conditionImage = 'images/weather/01' + string
									+ '.png';
						} else if (cId == 801) {
							var conditionImage = 'images/weather/02' + string
									+ '.png';
						} else if (cId == 802) {
							var conditionImage = 'images/weather/03' + string
									+ '.png';
						} else if (cId == 803) {
							var conditionImage = 'images/weather/04' + string
									+ '.png';
						} else if (cId == 804) {
							var conditionImage = 'images/weather/04' + string
									+ '.png';
						}

					} else if (cId >= 900 && cId <= 950) {
						if (cId == 900) {
							var conditionText = 'tornado';
						} else if (cId == 901) {
							var conditionText = 'tropical storm';
						} else if (cId == 902) {
							var conditionText = 'hurricane';
						} else if (cId == 903) {
							var conditionText = 'cold';
						} else if (cId == 904) {
							var conditionText = 'hot';
						} else if (cId == 905) {
							var conditionText = 'windy';
						} else if (cId == 906) {
							var conditionText = 'hail';
						}
					}else{
						conditionImage = 'images/weather/smile.png';
					}

					function loadImages(sources, callback) {
						var images = {};
						var loadedImages = 0;
						var numImages = 0;
						// get num of sources
						for ( var src in sources) {
							numImages++;
						}
						for ( var src in sources) {
							images[src] = new Image();
							images[src].onload = function() {
								if (++loadedImages >= numImages) {
									callback(images);
								}
							};
							images[src].src = sources[src];
						}
					}
					var canvas = document.getElementById('layer1');
					var context = canvas.getContext('2d');

					var sources = {
						arrow : 'images/arrow.png',
						condition : conditionImage,
					};

					loadImages(sources, function(images) {
						var angle = data.total.weather.windDirection;
						var windSpeed = data.total.weather.wind_speed;

						var TO_RADIANS = Math.PI / 180;
						var canvas = document.getElementById('layer1');
						var context = canvas.getContext('2d');
						canvas.width = 400;
						canvas.height = 73;

						var x = 20;
						var y = 20;
						var width = 40;
						var height = 40;

						context.save();
						// move the origin to 50, 35
						context.arc(39, 39, 22, 0, 2 * Math.PI, false);
						context.fillStyle = '#eee';
						context.fill();
						context.lineWidth = 1;
						context.strokeStyle = '#003300';
						context.stroke();

						context.translate(19, 19);
						// now move across and down half the
						// width and height of the image (which is 128 x 128)
						context.translate(20, 20);

						// rotate around this point
						context.rotate(angle * TO_RADIANS);

						// then draw the image back and up
						context.drawImage(images.arrow, -20, -20, width,height);

						// save the context's co-ordinate system before we screw with it
						context.restore();

						context.save();
						if (conditionImage) {
							context.drawImage(images.condition, 140, -9);
						} else {
							context.fillText(windSpeed + 'm/s', 77, 27, 30);
						}

						context.fillText('Wind Speed:', 77, 10, 50);
						context.fillText(windSpeed + 'm/s', 77, 25, 34);

						context.fillText('Clouds:', 77, 42, 30);
						context.fillText(data.total.weather.clouds + '%', 77,55, 30);

						context.fillText('Last hour:', 160, 42, 60);
						context.fillText(data.total.weather.rain1h + 'mm', 160,55, 30);

						context.fillText('Temp', 120, 42, 75);
						context.fillText(data.total.weather.currentTemp + '°', 120, 55, 60);
						
						
						
						context.fillText('Sun', 213, 42, 75);
						
						// sun rise :)
						var date = new Date((data.total.weather.sunrise+(correctTimeZoneDiffs * 3600))*1000);
						context.fillText('rise:' , 213, 51, 60);
						context.fillText(("0"+date.getHours()).slice(-2)+':'+ ("0"+date.getMinutes()).slice(-2) , 243, 51, 60);
						
						// sun transit :|
						var date = new Date((data.total.weather.transit+(correctTimeZoneDiffs * 3600))*1000);
						context.fillText('transit:' , 213, 62, 60);
						context.fillText(("0"+date.getHours()).slice(-2)+':'+ ("0"+date.getMinutes()).slice(-2), 243, 62, 60);
						
						// sun set :( 
						var date = new Date((data.total.weather.sunset+(correctTimeZoneDiffs * 3600))*1000);
						context.fillText('set:' , 213, 73, 60);
						context.fillText(("0"+date.getHours()).slice(-2)+':'+ ("0"+date.getMinutes()).slice(-2) , 243, 73, 60);
						
						
						// no idea with what it is...
						/*{
							{
								data.total.weather.currentTemp
							}
						}*/

						
						
						context.font = '11pt Calibri';
						context.fillText('N', 35, 13, 10);
						context.fillText('S', 36, 73, 10);
						context.fillText('E', 65, 42, 10);
						context.fillText('W', 3, 42, 10);
						context.restore();
					});

					ajaxReady();
				},
				dataType : 'text'
			});
		});
	},

	init_tabs : function(page, tabIndex, divId, success) {
		// initialize languages selector on the given div
		WSL.api.getTabs(page,function(data) {
			$.ajax({
				url : 'js/templates/tabs.hb',
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
					if ($("#mainSummary").length > 0) {
						$("#mainSummary").after(html);
					} else {
						$(html).prependTo(divId);
					}

					$('#tabs').tabs({
						active : tabIndex,
						create : function(event, ui) {
							// populate the tabs:
							populateTabs();
						},
						activate : function(event, ui) {
							// populate the tabs:
							populateTabs();
						}
					});
					// fix the classes
					$(".tabs-bottom .ui-tabs-nav, .tabs-bottom .ui-tabs-nav > *").removeClass("ui-corner-all ui-corner-top").addClass("ui-corner-bottom");

					// move the nav to the bottom
					$(".tabs-bottom .ui-tabs-nav").appendTo(".tabs-bottom");

					success.call();
				},
				dataType : 'text',
			});
		});
		return true;
	},

	init_LiveValues : function() {
		/*
		 * This seems to be unused
		 */
		WSL.api.live(function(data) {
			$.each(data, function() {
				if (this.type == "production") {
					// console.log("production");
				}
				if (this.type == "metering") {
					// console.log("metering");
				}
				if (this.type == "weather") {
					// console.log("weather");
					$('#weather').html(this.data.temp + ' &deg;C');
				}
			});
		});
	},

	init_PageLiveValues : function(divId) {
		WSL.api.init_PageLiveValues(function(data) {
			$(divId).html(WSL.template.get("liveValues", {
				'data' : '',
				'lang' : data.lang
			}));
		});
	},

	init_PageIndexTotalValues : function(sideBar) {
		WSL.api.getPageIndexTotalValues(function(data) {
			$(sideBar).html(WSL.template.get("totalValues", {
				'data' : data.IndexValues,
				'lang' : data.lang
			}));
		});
	},

	init_PageTodayValues : function(todayValues, success) {
		ajaxStart();
		// initialize languages selector on the given div
		WSL.api.getPageTodayValues(function(data) {
			$(todayValues).html(WSL.template.get('todayValues', {
				'data' : data.dayData.data,
				'lang' : data.lang
			}));
			success.call();
			ajaxReady();
		});
	},

	init_PageMonthValues : function(monthValues, periodList) {
		ajaxStart();

		if ($('#datePickerPeriod').val()) {
			var completeDate = "01-" + $('#datePickerPeriod').val();
		} else {
			var completeDate = $('#datePickerPeriod').val();
		}

		var pickerDate = $('#datePickerPeriod').val();
		if (typeof completeDate == 'undefined') {
			var currentTime = new Date();
			completeDate = "01-" + (currentTime.getMonth() + 1) + "-"
					+ currentTime.getFullYear();
		}

		// initialize languages selector on the given div
		WSL.api.getPageMonthValues(completeDate,function(data) {
				$.ajax({
					url : 'js/templates/monthValues.hb',
					beforeSend : function(xhr) {
						if (getWindowsState() == false) {
							ajaxAbort(xhr, '');
						}
					},
					success : function(source) {
						var template = Handlebars.compile(source);
						var html = template({
							'data' : data,
							'lang' : data.lang
						});
						$(monthValues).html(html);
						
						$('#pickerPeriod option').each(function(){
							if($(this).val() == "Month"){
								$(this).attr("selected","selected");
							}
						});
						$(function() {
							$(".accordion").accordion({
								collapsible : true
							});
							$(".accordion").accordion({
								collapsible : true
							});
						});

						// $('#pageMonthDateFilter').html(html);
						if (!pickerDate) {
							$("#datePickerPeriod").datepicker({
								dateFormat : 'mm-yy',
								changeMonth : true,
								changeYear : true,
								onClose : function(dateText,inst) {
									var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
									var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
									// newDate(year,month,day) W3schools
									$(this).val($.datepicker.formatDate('mm-yy',new Date(year,month,1)));
									WSL.init_PageMonthValues("#columns","#periodList"); // Initial loadfast
								}
							});
							// new Date(year, month, day) //
							// W3schools
							$("#datePickerPeriod").datepicker('setDate',new Date());
						} else {
							$("#datePickerPeriod").datepicker({
								dateFormat : 'mm-yy',
								changeMonth : true,
								changeYear : true,
								onClose : function(dateText,inst) {
									var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
									var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();// new Date(year,month, day)// W3schools
									$(this).val($.datepicker.formatDate('mm-yy',new Date(year,month,1)));
									WSL.init_PageMonthValues("#columns","#periodList"); // Initial load fast
								}
							});
							pickerDate = pickerDate.split('-'); // 01-2012 (month-year)
							pickerDate[0] = pickerDate[0] - 1; //new Date(year, month, day)// W3schools
							$("#datePickerPeriod").datepicker('setDate',new Date(pickerDate[1],pickerDate[0],1));
						}
						$("#datePickerPeriod").focus(function() {
							$(".ui-datepicker-calendar").hide();
							$("#ui-datepicker-div").position({my : "center top",at : "center bottom",of : $(this)});
						});
					},
					dataType : 'text',
				});
			});
		ajaxReady();
	},
	init_systemPhotos : function(divId){
		WSL.api.systemPhotos(function(data) {
			$.ajax({
				url : 'js/templates/systemPhotos.hb',
				beforeSend : function(xhr) {
					if (getWindowsState() == false) {
						ajaxAbort(xhr);
					}
				},
				success : function(source) {
					var template = Handlebars.compile(source);
					var html = template({
						'data' : data
					});
					$(divId).before(html);
					
				}
			})
		});
	},
	init_summaryPage : function(divId) {
		var html = '<div id="mainSummary" style="margin-bottom:5px;"></div>';
		$(html).prependTo(divId);
		var currentTime = new Date();
		date = (currentTime.getDate()) + "-" + (currentTime.getMonth() + 1) + "-" + currentTime.getFullYear();
		WSL.api.summaryPage(date, function(data) {
			$.ajax({
				url : 'js/templates/summaryPage.hb',
				beforeSend : function(xhr) {
					if (getWindowsState() == false) {
						ajaxAbort(xhr);
					}
				},
				success : function(source) {
					var template = Handlebars.compile(source);
					var html = template({
						'data' : data
					});
					$(html).prependTo(divId);

					var cId = data.total.weather.conditionId;
					var sunDown = data.total.sunDown;

					if (cId >= 200 && cId <= 250) {
						var conditionImage = 'images/weather/11d.png';
					} else if (cId >= 300 && cId <= 350) {
						var conditionImage = 'images/weather/09d.png';
					} else if (cId >= 500 && cId <= 550) {
						var conditionImage = 'images/weather/10d.png';
					} else if (cId >= 600 && cId <= 650) {
						var conditionImage = 'images/weather/13d.png';
					} else if (cId >= 700 && cId <= 750) {
						var conditionImage = 'images/weather/50d.png';
					} else if (cId >= 800 && cId <= 850) {
						if (sunDown == true) {
							var string = 'n';
						} else {
							var string = 'd';
						}
						if (cId == 800) {
							var conditionImage = 'images/weather/01' + string
									+ '.png';
						} else if (cId == 801) {
							var conditionImage = 'images/weather/02' + string
									+ '.png';
						} else if (cId == 802) {
							var conditionImage = 'images/weather/03' + string
									+ '.png';
						} else if (cId == 803) {
							var conditionImage = 'images/weather/04' + string
									+ '.png';
						} else if (cId == 804) {
							var conditionImage = 'images/weather/04' + string
									+ '.png';
						}

					} else if (cId >= 900 && cId <= 950) {

						if (cId == 900) {
							var conditionText = 'tornado';
						} else if (cId == 901) {
							var conditionText = 'tropical storm';
						} else if (cId == 902) {
							var conditionText = 'hurricane';
						} else if (cId == 903) {
							var conditionText = 'cold';
						} else if (cId == 904) {
							var conditionText = 'hot';
						} else if (cId == 904) {
							var conditionText = 'windy';
						} else if (cId == 904) {
							var conditionText = 'hail';
						}

					}

					function loadImages(sources, callback) {
						var images = {};
						var loadedImages = 0;
						var numImages = 0;
						// get num of sources
						for ( var src in sources) {
							numImages++;
						}
						for ( var src in sources) {
							images[src] = new Image();
							images[src].onload = function() {
								if (++loadedImages >= numImages) {
									callback(images);
								}
							};
							images[src].src = sources[src];
						}
					}
					var canvas = document.getElementById('layer1');
					var context = canvas.getContext('2d');

					var sources = {
						arrow : 'images/arrow.png',
						condition : conditionImage,
					};

					loadImages(sources, function(images) {
						var angle = data.total.weather.windDirection;
						var windSpeed = data.total.weather.wind_speed;

						var TO_RADIANS = Math.PI / 180;
						var canvas = document.getElementById('layer1');
						var context = canvas.getContext('2d');
						canvas.width = 270;
						canvas.height = 90;

						var x = 20;
						var y = 20;
						var width = 40;
						var height = 40;

						context.save();
						// move the origin to 50, 35
						context.arc(39, 39, 22, 0, 2 * Math.PI, false);
						context.fillStyle = '#eee';
						context.fill();
						context.lineWidth = 1;
						context.strokeStyle = '#003300';
						context.stroke();

						context.translate(19, 19);
						// now move across and down half the
						// width and height of the image (which is 128 x 128)
						context.translate(20, 20);

						// rotate around this point
						context.rotate(angle * TO_RADIANS);

						// then draw the image back and up
						context.drawImage(images.arrow, -20, -20, width,height);

						// save the context's co-ordinate system before
						// we screw with it
						context.restore();

						context.save();
						if (conditionImage) {
							context.drawImage(images.condition, 130, -12);
						} else {
							context.fillText(windSpeed + 'm/s', 77, 27, 30);
						}
						context.fillText('Wind Speed:', 77, 10, 50);
						context.fillText(windSpeed + 'm/s', 77, 25, 30);

						context.fillText('Clouds:', 77, 42, 30);
						context.fillText(data.total.weather.clouds + '%', 77,55, 30);

						context.fillText('Last hour:', 77, 72, 60);
						context.fillText(data.total.weather.rain1h + 'mm', 77,85, 30);

						context.fillText('Temp cur/avg:', 140, 42, 75);
						context.fillText(data.total.weather.currentTemp + '°/'+ data.total.weather.avgTemp + '°',140, 55, 60);
						context.fillText('Temp min/max:', 140, 72, 75);
						context.fillText(data.total.weather.minTemp + '°/'+ data.total.weather.maxTemp + '°',140, 85, 60);
						
						
						context.fillText('Sun', 213, 42, 75);
						var date = new Date(data.total.weather.sunrise*1000);
						context.fillText('rise:' , 213, 51, 60);
						context.fillText(("0"+date.getHours()).slice(-2)+':'+ ("0"+date.getMinutes()).slice(-2) , 243, 51, 60);
						var date = new Date(data.total.weather.transit*1000);
						context.fillText('transit:' , 213, 62, 60);
						context.fillText(("0"+date.getHours()).slice(-2)+':'+ ("0"+date.getMinutes()).slice(-2), 243, 62, 60);
						context.fillText('set:' , 213, 73, 60);
						var date = new Date(data.total.weather.sunset*1000);
						context.fillText(("0"+date.getHours()).slice(-2)+':'+ ("0"+date.getMinutes()).slice(-2) , 243, 73, 60);
						
						
						
						// no idea what this is...
						/*
						{
							{
								data.total.weather.currentTemp
							}
						}*/

						
						
						context.font = '11pt Calibri';
						context.fillText('N', 35, 13, 10);
						context.fillText('S', 36, 73, 10);
						context.fillText('E', 65, 42, 10);
						context.fillText('W', 3, 42, 10);
						context.restore();
					});
					ajaxReady();
				},
				dataType : 'text'
			});
		});
	},
	
	init_PageYearValues : function(yearValues, periodList) {
		ajaxStart();

		if ($('#datePickerPeriod').val()) {
			var completeDate = "01-01-" + $('#datePickerPeriod').val();
		} else {
			var completeDate = $('#datePickerPeriod').val();
		}
		
		var pickerDate = $('#datePickerPeriod').val();
		if (typeof completeDate == 'undefined') {
			var currentTime = new Date();
			completeDate = "01-01-" + currentTime.getFullYear();
		}

		// initialize languages selector on the given div
		WSL.api.getPageYearValues(completeDate,function(data) {
				$.ajax({
					url : 'js/templates/yearValues.hb',
					beforeSend : function(xhr) {
						if (getWindowsState() == false) {
							ajaxAbort(xhr, '');
						}
					},
					success : function(source) {
						var template = Handlebars
								.compile(source);
						var html = template({
							'data' : data,
							'lang' : data.lang
						});
						$(yearValues).html(html);
						
						$('#pickerPeriod option').each(function(){
							if($(this).val() == "Year"){
								$(this).attr("selected","selected");
							}
						});
						
						$(function() {
							$(".accordion").accordion({
								collapsible : true
							});
							$(".accordion").accordion({
								collapsible : true
							});
						});
						$.getJSON('server.php?method=getPeriodFilter&type=all',function(PeriodFilter) {
							$.ajax({
								url : 'js/templates/pageDateFilter.hb',
								beforeSend : function(xhr) {
									if (getWindowsState() == false) {
										ajaxAbort(xhr,'');
									}
								},
								success : function(source) {
									var template = Handlebars.compile(source);
									var html = template({
										'data' : PeriodFilter,
										'lang' : PeriodFilter.lang
									});
									$('#pageYearDateFilter').html(html);
									if (!pickerDate) {
										$("#datePickerPeriod").datepicker({
											dateFormat : 'yy',
											changeMonth : false,
											changeYear : true,
											onClose : function(dateText,inst) {
												var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val(); //new Date(year,month, day) // W3schools
												$(this).val($.datepicker.formatDate('yy',new Date(year,1,1)));
												WSL.init_PageYearValues("#columns","#periodList"); // Initial load fast
											}
										});
										$("#datePickerPeriod").datepicker('setDate',new Date());
									} else {
										$("#datePickerPeriod").datepicker({
											dateFormat : 'yy',
											changeMonth : false,
											changeYear : true,
											onClose : function(dateText,inst) {
												var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();//newDate(year,month,day)
												$(this).val($.datepicker.formatDate('yy',new Date(year,1,1)));
												WSL.init_PageYearValues("#columns","#periodList"); // Initial loadfast
											}
										});
										// newDate(year,month,day)
										$("#datePickerPeriod").datepicker('setDate',new Date(pickerDate,1,1));
									}
									$("#datePickerPeriod").focus(function() {
										$(".ui-datepicker-calendar").hide();
										$(".ui-datepicker-month").hide();
										$(".ui-icon-circle-triangle-w").hide();
										$(".ui-icon-circle-triangle-e").hide();
										$("#ui-datepicker-div").position({my : "center top",at : "center bottom",of : $(this)});
									});
								},
								dataType : 'text',
							});
						});
					},
					dataType : 'text',
				});
			});
		ajaxReady();
	},

	createDayGraph : function(devicenum, getDay, tab, date, currentHandler,fnFinish, graph) {
		ajaxStart();
		$("#graphReady").val('false');
		var graphOptions = {
			seriesDefaults : {
				rendererOptions : {
					smooth : true
				},
				showMarker : false,
				autoscale : true
			},
			series : [],
			/*
			 * legend : { show : true, location : "nw", renderer :
			 * $.jqplot.EnhancedLegendRenderer, rendererOptions : { seriesToggle :
			 * 'normal', numberColumns : 1, disableIEFading : false } },
			 */
			axesDefaults : {
				syncTicks : true,
				useSeriesColor : true,
				labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
				autoscale : true,

			},
			axes : {},
			highlighter : {
				tooltipContentEditor : tooltipTodayContentEditor,
				show : true,
				tooltipLocation : 'n',
				fadeTooltip : true,
				tooltipFadeSpeed : 500,
				tooltipAxes : 'both',
			},
			cursor : {
				zoom : true,
				show : true,
				showTooltip : false,
				style : 'default'
			}
		};
		$.jqplot.DateTickFormatter = function(format, val) {
			// for some reason, format isn't being passed through properly, so
			// just going to hard code for purpose of this jsfiddle
			val = (new Date(val)).getTime();
			format = '%H:%M';
			return $.jsDate.strftime(val, format);
		};
		$.ajax({url : "api.php/Graph/daily/" + getDay + "/" + date + "/" + devicenum + "/frontend",
				beforeSend : function(xhr) {
					if (getWindowsState() == false) {
						ajaxAbort(xhr, '');
					}
				},
				method : 'GET',
				dataType : 'json',
				success : function(result) {
					// add a custom tick formatter, so that you don't have
					// to include the entire date renderer library.

					var clearSky = [];
					var clearSkyGenerated = [];
					var plantTotalPower = 0;
					var config = [];
					var clearSkyDeviceNames = [];
					config = result.slimConfig;
					if (config.devices) {
						for ( var i = 0; i < config.devices.length; i++) {
							for ( var ii = 0; ii < config.devices[i].panels.length; ii++) {
								var lat = config.lat;
								var long = config.long;
								var totalWp = config.devices[i].panels[ii].totalWp;
								var roofPitch = config.devices[i].panels[ii].roofPitch;
								var roofOrientation = config.devices[i].panels[ii].roofOrientation;
								var clearSkyGenerated = generatePanelClearSky(result, roofOrientation, roofPitch,totalWp, date);
								var plantTotalPower = plantTotalPower+ clearSkyGenerated[3];
								clearSkyDeviceNames.push('C.S. '+config.devices[i].panels[ii].description+' (W)');
								clearSky.push(clearSkyGenerated[1]);
							}
						}
					}

					var sums = {}; // will keep a map of number => sum
					// for each input array (insert as many as you like)
					clearSky.forEach(function(array) {
						// for each pair in that array
						array.forEach(function(pair) {
							// increase the appropriate sum
							sums[pair[0]] = pair[1] + (sums[pair[0]] || 0);
						});
					});

					// now transform the object sums back into an array ofpairs
					var results = [];
					for ( var key in sums) {
						results.push([ parseInt(key), sums[key] ]);
					}
					clearSkyDeviceNames.push('C.S. Total (W)');
					clearSky.push(results);
					
					seriesData = [];
					var clearSkySeriesObject = {};
					var json = [];
					var newJsonSeries = [];
					
					
					// timezone correction
					now=new Date();
					ofst=now.getTimezoneOffset()/60;
					correctTimeZoneDiffs = 24-(ofst + result.timezoneOffset);
					
					if (result) {
						if (result) {
							
							seriesDataDummy = [];
							for (series in result.series) {
								result.series[series] = $.parseJSON(result.series[series].json);
								// fix for if there is no seriesData.
								// Add 1 datapoint for each serie.
								json.push([new Date().getTime(),0 ]);
								seriesDataDummy.push(json);
							}
							for (line in result.dataPoints) {

								var json = [];

								for (values in result.dataPoints[line]) {
									//console.log(new Date(result.dataPoints[line][values][0]*1000));
									//console.log(new Date((result.dataPoints[line][values][0] - (correctTimeZoneDiffs*3600)) * 1000));
									json.push([
												(result.dataPoints[line][values][0] - (correctTimeZoneDiffs*3600)) * 1000,
												result.dataPoints[line][values][1]
											]);
								}
								seriesData.push(json);

								newJsonSeries = [];
								
								if (seriesData.length == Object.keys(result.dataPoints).length) {
									var axesNumber = Object.keys(result.dataPoints).length;
									for (var i = 0; i < clearSky.length; i++) {
										var clearSkySeriesObject = []; 
										seriesData.push(clearSky[i]);
										clearSkySeriesObject['label'] = clearSkyDeviceNames[i];
										clearSkySeriesObject['yaxis'] = 'yaxis';
										result.series.splice(axesNumber, 0, clearSkySeriesObject);
										result.series.join();
										axesNumber++;
									}
								}
							}
							
							// if there is no seriesData;
							// set dummy data as serieData
							if(seriesData.length==0){
								seriesData = null;
								seriesData = seriesDataDummy;
							}
							
							if (result.json.legend != '') {
								if (result.json.legend.renderer == 'EnhancedLegendRenderer') {
									result.json.legend.renderer = $.jqplot.EnhancedLegendRenderer;
								}
								graphOptions.legend = result.json.legend;
							}
						    
							var newAxes = [];

							for (axes in result.axes) {
								var jsonAxe = result.axes[axes].json;

								newAxes[jsonAxe.axe] = jsonAxe;

								if (newAxes[jsonAxe.axe]['renderer'] == 'DateAxisRenderer') {
									newAxes[jsonAxe.axe]['renderer'] = $.jqplot.DateAxisRenderer;
								}
								if (newAxes[jsonAxe.axe]['tickRenderer'] == 'CanvasAxisTickRenderer') {
									newAxes[jsonAxe.axe]['tickRenderer'] = $.jqplot.CanvasAxisTickRenderer;
								}
								if (newAxes[jsonAxe.axe]['labelRenderer'] == 'CanvasAxisLabelRenderer') {
									newAxes[jsonAxe.axe]['labelRenderer'] = $.jqplot.CanvasAxisLabelRenderer;
								}
								if (newAxes[jsonAxe.axe]['formatter'] == 'DayDateTickFormatter') {
									newAxes[jsonAxe.axe]['labelRenderer'] = $.jqplot.DayDateTickFormatter;
								}
							}

							
							
							graphOptions.axes = newAxes;
							/////////////////////////////////
							graphOptions.axes.xaxis.min = 
								new Date((result.timestamp.beginDate -(correctTimeZoneDiffs*3600)) * 1000);
							/////////////////////////////////
							graphOptions.axes.xaxis.max = 
								new Date((result.timestamp.endDate -(correctTimeZoneDiffs*3600)) * 1000);
							/////////////////////////////////
							graphOptions.series = result.series;
						}

						var seriesHidden = [];
						// loop through all hidden
						$('td.jqplot-series-hidden').each(function() {
							$this = $(this);
							if ($this.text() != '') {
								seriesHidden.push($this.text());
							}
						});

						if (currentGraphHandler) {
							currentGraphHandler.destroy();
							$("#graph" + tab + "Content").empty();
							$("#graph" + tab + "Content").html('<div id="loading">refreshing graph...</div>');
						}

						handle = null;
						delete handle;
						
						// plot graph
						handle = $.jqplot('graph' + tab + 'Content',seriesData, graphOptions);
						if (result.json.legend.left > 0) {
							$('table.jqplot-table-legend').css('left',result.json.legend.left);
						}
						if (result.json.legend.width > 0) {
							$('table.jqplot-table-legend').css('width',result.json.legend);
						}

						// iterator to keep track on the legenda items
						i = 1;
						// walkthrough legenda
						$('td.jqplot-table-legend').each(function() {
							$this = $(this);
							// walkthrough array with hidden lines
							for (line in result.hideSeries) {
								// if legenda.text is equal to hideSeries text; 
								// Click this legenda item to hide is
								if (seriesHidden.length > 0) {
									if ($this.text() == seriesHidden[line]) { //CLICK!!
										$("td:contains("+ $this.text()+ ")").click();
									}
								} else {
									if ($this.text() == result.hideSeries[line]) { // CLICK!!
										$("td:contains("+ $this.text()+ ")").click();
									}
								}
							}
							// UP the legenda item iterator
							i = i + 1;
						});
						
						//
						// make graph title
						//
						/**/
						if($(result.meta).hasOwnProperty('KWH')){
							var graphTitle = '<div class="my-jqplot-title" style="position:absolute;text-align:center;padding-top: 1px;width:100%">'
								+ result.lang.generated+ ': '
								+ result.meta.KWH.cumPower+ ' '
								+ result.meta.KWH.KWHTUnit+ ' ('
								+ result.meta.KWH.KWHKWP+ ' kWh/kWp)&nbsp&nbsp;'
								+ result.lang.max+ ': '
								+ parseInt(plantTotalPower) + ' '
								+ result.meta.KWH.KWHTUnit + ' ('
								+ ((typeof totalKWhkWp === 'undefined') ? 0: totalKWhkWp) + ' kWh/kWp)</div>';
						}else{
							var graphTitle = '';
						}

						delete seriesData, graphOptions;
						// if (typeof (result.json.KWH) !== "undefined") {
						setGraphTitle(graphTitle, '#graph' + getDay);
						// }
						fnFinish.call(this, handle);
						ajaxReady();
					}
				}
			});
	},
	createPeriodGraph : function(devicenum, type, count, date, divId, fnFinish) {
		ajaxStart();
		$("#graphReady").val('false');
		$.ajax({url : "server.php?method=getGraphPoints&type=" + type + "&count=" + count + "&date=" + date + "&devicenum=" + devicenum,
					beforeSend : function(xhr) {
						if (getWindowsState() == false) {
							ajaxAbort(xhr, '');
						}
					},
					method : 'GET',
					dataType : 'json',
					success : function(result) {
						var dayData1 = [];
						var dayData2 = [];
						var i = 0;
						for (line in result.dayData.data) {
							var object = result.dayData.data[line];
							dayData1.push([ object[0] * 1000, object[1] ]);
							dayData2.push([ object[0] * 1000, object[2] ]);
							i += 1;
						}
						// add a custom tick formatter, so that you don't have
						// to include the entire date renderer library.
						$.jqplot.DateTickFormatter = function(format, val) {
							// for some reason, format isn't being passed
							// through properly, so just going to hard code for
							// purpose of this jsfiddle
							val = (new Date(val)).getTime();
							format = '%b %#d';
							return $.jsDate.strftime(val, format);
						};
						var graphDayPeriodOptions = {
							series : [ {
								label : result.lang.harvested,
								yaxis : 'yaxis',
								showMarker : false,
								renderer : $.jqplot.BarRenderer,
								pointLabels : {
									show : false
								}
							}, {
								label : result.lang.cumulative,
								yaxis : 'y2axis',
								pointLabels : {
									show : false
								}
							} ],
							seriesDefaults : {
								labelOptions : {
									formatString : '%d-%',
									fontSize : '20pt'
								},
								rendererOptions : {
									fillToZero : true,
									barWidth : 5
								},
								showMarker : false,
								pointLabels : {
									show : true,
									formatString : '%s'
								},
							},
							axesDefaults : {
								useSeriesColor : true,
								tickRenderer : $.jqplot.CanvasAxisTickRenderer,
								tickOptions : {
									angle : -30,
									fontSize : '10pt'
								}
							},
							legend : {
								show : true,
								location : "nw",
								renderer : $.jqplot.EnhancedLegendRenderer,
								rendererOptions : {
									// set to true to replot when toggling
									// series on/off
									// set to an options object to pass in
									// replot options.
									seriesToggle : 'normal',
								// seriesToggleReplot: {resetAxes: true}
								}
							},
							highlighter : {
								tooltipContentEditor : tooltipPeriodContentEditor,
								show : true,
								yvalues : 4,
								tooltipLocation : 'n'
							},
							axes : {
								// Use a category axis on the x axis and use our
								// custom ticks.
								xaxis : {
									labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
									renderer : $.jqplot.DateAxisRenderer,
									tickRenderer : $.jqplot.CanvasAxisTickRenderer,
									tickOptions : {
										angle : -20
									}
								},
								yaxis : {
									label : result.lang.harvested,
									min : 0,
									labelRenderer : $.jqplot.CanvasAxisLabelRenderer
								},
								y2axis : {
									label : result.lang.cumulative,
									min : 0,
									labelRenderer : $.jqplot.CanvasAxisLabelRenderer
								}
							}
						};
						graphDayPeriodOptions.axes.xaxis.min = result.dayData.data[0][0] * 1000;
						graphDayPeriodOptions.axes.xaxis.max = result.dayData.data[i - 1][0] * 1000;
						var plot = $.jqplot(divId, [ dayData1, dayData2 ], graphDayPeriodOptions).destroy();
						plot = null;
						var plot = $.jqplot(divId, [ dayData1, dayData2 ], graphDayPeriodOptions);
						ajaxReady();
						$("#graphReady").val('true').trigger('change');
					}
				});
	},

	init_production : function(divId) {
		$(divId).html('<div id="datePickerContainer"></div><div id="graphContainer"></div><div id="figuresContainer">figurs loading...</div>');

		WSL.connect.getJSON('server.php?method=getDevices',function(data) {
			$('#datePickerContainer').html(
					WSL.template.get('pageDateFilter', {
						'data' : data,
						'lang' : data.lang
					}));

			$("#datePickerPeriod").val(new Date().getFullYear());
			var currentYear = $("#datePickerPeriod").val();

			$("#datePickerPeriod").datepicker({
								dateFormat : 'yy',
								changeMonth : false,
								changeYear : true,
								onSelect : function(dateText,inst) {
									// $(this).val($('#datePickerPeriod').val());
								},
								onClose : function(dateText,inst) {
									var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
									// new Date(year,month, day) // W3schools
									// if(currentYear != year){
									$(this).val($.datepicker.formatDate('yy',new Date(year,1,1)));
									$("#graphContainer").html('reloading....');
									$("#figuresContainer").html('reloading....');
									devicenum = $('#devicenum').val();
									WSL.createProductionGraph(devicenum,'graphContainer', year); // Initial load fast
									// }
								}
							});

			$('#devicenum').bind('change',function() {
				devicenum = $(this).val();
				var year = $("#datePickerPeriod").val();
				//$(this).val($.datepicker.formatDate('yy',new Date(year,1,1)));
				$("#graphContainer").html('reloading....');
				$("#figuresContainer").html('reloading....');
				WSL.createProductionGraph(devicenum,'graphContainer', year); // Initial load fast
			});

			var pickerDate = $('#datePickerPeriod').val(); // new Date(year,month, day) // W3schools
			$(this).val($.datepicker.formatDate('yy', new Date(pickerDate, 1, 1)));
			var devicenum = $('#devicenum').val();
			WSL.createProductionGraph(devicenum, 'graphContainer', pickerDate); // Initial load fast

			$("#datePickerPeriod").focus(function() {
				$(".ui-datepicker-calendar").hide();
				$(".ui-datepicker-month").hide();
				$(".ui-icon-circle-triangle-w").hide();
				$(".ui-icon-circle-triangle-e").hide();
				$("#ui-datepicker-div").position({
					my : "center top",
					at : "center bottom",
					of : $(this)
				});
			});
		});
	},

	createProductionGraph : function(devicenum, divId, year) {
		ajaxStart();
		$("#graphReady").val('false');
		$.ajax({
			url : "server.php?method=getProductionGraph&devicenum=" + devicenum
					+ "&year=" + year,
			beforeSend : function(xhr) {
				if (getWindowsState() == false) {
					ajaxAbort(xhr, '');
				}
			},
			method : 'GET',
			dataType : 'json',
			success : function(result) {
				var dataDay1 = [];
				var dataDay2 = [];
				var dataDay3 = [];
				var dataDay4 = [];
				var dataTable = [];
				var ticksTable = [];

				if (result.dayData) {
					$.ajax({
						url : 'js/templates/productionFigures.hb',
						beforeSend : function(xhr) {
							if (getWindowsState() == false) {
								ajaxAbort(xhr, '');
							}
						},
						success : function(source) {
							var template = Handlebars.compile(source);
							for (line in result.dayData.data) {
								var data = result.dayData.data[line];
								var item = {
									"har" : data[0],
									"date" : data[7],
									"exp" : data[2],
									"diff" : data[3],
									"cumExp" : data[4],
									"cumHar" : data[5],
									"cumDiff" : data[6],
									"cumDiffTest" : data[5] - data [4]
								};
								dataTable.push([ item ]);
							}
							console.log(dataTable);
							var html = template({
								'data' : dataTable,
								'lang' : result.lang
							});
							$("#figuresContainer").html('');
							$('#figuresContainer').html(html);
						},
						dataType : 'text',
					});
					// create "white-space" on begin of x-axes
					ticksTable.push("0");
					var monthTable = [];
					var harvested = [];
					var expected = [];
					var maxes = [];
					
					for (line in result.dayData.data) {
						var object = result.dayData.data[line];
						dataDay1.push(object[0]);
						dataDay2.push(object[2]);
						dataDay3.push(object[4]);
						dataDay4.push(object[5]);
					}
					// create "white-space" on end of x-axes
					ticksTable.push("13");

					graphProductionOptions = graphProductionSettings();
					graphProductionOptions.axes.xaxis.numberTicks = ticksTable;
					graphProductionOptions.axes.xaxis.ticks = ticksTable;

					for ( var i = 0; i < dataDay1.length; i++) { //Iterates over numeric indexes from 0 to 5, as everyone expects
						harvested.push(dataDay1[i]);
					}
					var maxHarvested = Math.max.apply(Math, harvested);
					for ( var i = 0; i < dataDay2.length; i++) { // Iterates over numeric indexes from 0 to 5, as everyone expects
						expected[i] = dataDay2[i];
					}
					var maxExpected = Math.max.apply(Math, expected);
					

					maxes.push(Math.round(maxHarvested / 10) * 10);
					maxes.push(Math.round(maxExpected / 10) * 10);

					//get Max values for axe
					maxAxesValue = Math.max.apply(Math,maxes);
					
					//calculate 10% from Max Axe value
					var axesMargin = Math.round(maxAxesValue / 100) * 10;
					
					//set the Max value summed with Margin
					graphProductionOptions.axes.yaxis.max = maxAxesValue + axesMargin;
					graphProductionOptions.axes.y2axis.max = maxAxesValue + axesMargin;

					maxAxesValue = Math.max(Math.round(dataDay3[11] / 100) * 100, Math.round(dataDay4[11] / 100) * 100)+10;
					var axesMargin = Math.round(maxAxesValue / 100) * 10;
					graphProductionOptions.axes.y3axis.max = maxAxesValue + axesMargin;
					graphProductionOptions.axes.y4axis.max = maxAxesValue + axesMargin;
					graphProductionOptions.axes.xaxis.min = 0;
					graphProductionOptions.axes.xaxis.max = 13;

					$("#graphContainer").html('');
					$("#graphContainer").height(450);
					$("#graphContainer").width(900);
					handle = null;
					delete handle;
					handle = $.jqplot("graphContainer", [ dataDay1, dataDay2,
							dataDay3, dataDay4 ], graphProductionOptions);
					ajaxReady();
				}
			}
		});
	},
	init_details : function(divId, queryDate) {
		$("#main-middle").prepend('<div id="datePeriodFilter"></div><div id="detailsGraph"></div><div id="detailsSwitches"></div>');
		WSL.connect.getJSON('server.php?method=getDetailsSwitches', function(data) {
			$('#detailsSwitches').html(WSL.template.get('detailsSwitches', {
				'data' : data,
				'lang' : data.lang
			}));
		});

		WSL.connect.getJSON('server.php?method=getPeriodFilter&type=today', function(data) {
			$('#datePeriodFilter').html(
				WSL.template.get('datePeriodFilter', {
					'data' : data,
					'lang' : data.lang
				}));

			var date = (queryDate != "undefined") ? queryDate : date;

			// Initialize date picker
			var datePicker = $('#datepicker').datepicker({dateFormat : 'yy-mm-dd'});
			datePicker.datepicker('setDate', new Date());
			datePicker.css('z-index', 0); // fix for Graph //Tooltip

			var deviceSelect = $('#devicenum');
			$('#pickerPeriod, #datepicker, #devicenum').bind('change',function() {
				var date = new Date(datePicker.datepicker('getDate'));
				WSL.createDetailsGraph(deviceSelect.val(), divId, date.yyyymmdd());
			});

			$('#next').unbind('click');
			$('#previous').unbind('click');
			$('#pickerPeriod').unbind('click');

			$('#next').click(function() {
				var date = new Date(datePicker.datepicker('getDate'));
				var splitDate = datePicker.val().split('-');
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
				datePicker.datepicker('setDate', date);
				var splitDate = $('#datepicker').val().split('-');
				WSL.createDetailsGraph(deviceSelect.val(),divId, splitDate[0]+ '-'+ splitDate[1]+ '-'+ splitDate[2]);
			});

			$('#previous').click(function() {
				var date = new Date(datePicker.datepicker('getDate'));
				var splitDate = datePicker.val().split('-');
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
				datePicker.datepicker('setDate', date);
				var splitDate = datePicker.val().split('-');
				WSL.createDetailsGraph(deviceSelect.val(),divId, splitDate[0]+ '-'+ splitDate[1]+ '-'+ splitDate[2]);
			});
			WSL.createDetailsGraph(deviceSelect.val(), divId,date);
		});
	},

	createDetailsGraph: function(devicenum, divId, date) {
		$.ajax({
			url: "server.php?method=getDetailsGraph&devicenum=" + devicenum + "&date=" + date,
			method : 'GET',
			dataType : 'json',
			beforeSend : function(xhr) {
				if (getWindowsState() == false) {
					ajaxAbort(xhr, '');
				}
			},
			success : function(result) {
				if (result.dayData.data) {
					var seriesLabels = [];
					var seriesData = [];
					var switches = [];
					for (line in result.dayData.data) {
						var json = [];
						for (values in result.dayData.data[line]) {
							json.push([
								result.dayData.data[line][values][0] * 1000,
								result.dayData.data[line][values][1] ]);
						}
						seriesLabels.push(line);
						seriesData.push(json);
					}
					var lang = result.lang;
					var graphOptions = {
							series : [ {yaxis : 'yaxis'},// 0
										{yaxis : 'y2axis'},// 1
										{yaxis : 'y3axis'},// 2
										{yaxis : 'y4axis'},// 3
										{yaxis : 'yaxis'},// 4
										{yaxis : 'y2axis'},// 5
										{yaxis : 'y3axis'},// 6
										{yaxis : 'y5axis'},// 7
										{yaxis : 'yaxis'},// 8
										{yaxis : 'y2axis'},// 9
										{yaxis : 'y3axis'},// 10
										{yaxis : 'y5axis'},// 11
										{yaxis : 'y7axis'},// 12
										{yaxis : 'y6axis'},// 13
										{yaxis : 'y6axis'} // 13
										],
						axesDefaults : {
							useSeriesColor : true
						},
						legend : {
							show : true,
							location : 's',
							placement : 'outsideGrid',
							renderer : $.jqplot.EnhancedLegendRenderer,
							rendererOptions : {
								seriesToggle : 'normal',
								numberColumns : 4,// seriesToggleReplot:{resetAxes:["yaxis"]}
							}
						},
						seriesDefaults : {
							rendererOptions : {
								smooth : true
							},
							tickOptions : {
								formatString : '%d'
							},
							pointLabels : {
								show : false
							},
							showMarker : false,
							autoscale : true,
						},
						axes : {
							xaxis : {
								label : '',
								labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
								renderer : $.jqplot.DateAxisRenderer,
								tickInterval : '3600', /* 1 hour */
								tickOptions : {
									angle : -30,
									formatString : '%H:%M'
								}
							},
							yaxis : {
								label : lang.P,
								min : 0,
								labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
								autoscale : true,
								tickOptions : {
									formatString : '%.0f'
								}
							},
							y2axis : {
								label : lang.V,
								min : 100,
								labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
								autoscale : true,
								tickOptions : {
									formatString : '%.0f'
								}
							},
							y3axis : {
								label : lang.A,
								min : 0,
								max : 20,
								labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
								autoscale : true,
								tickOptions : {
									formatString : '%.0f'
								}
							},
							y4axis : {
								label : lang.F,
								min : 0,
								labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
								autoscale : true,
								tickOptions : {
									formatString : '%.0f'
								}
							},
							y5axis : {
								label : lang.R,
								min : 0,
								labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
								autoscale : true,
								tickOptions : {
									formatString : '%.0f'
								}
							},
							y6axis : {
								label : lang.T,
								min : 0,
								labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
								autoscale : true,
								tickOptions : {
									formatString : '%.0f'
								}
							},
							y7axis : {
								label : lang.E,
								min : 0,
								labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
								autoscale : true,
								tickOptions : {
									formatString : '%.0f'
								}
							}
						},

						cursor : {
							zoom : true,
							show : true,
							showTooltip : false,
							style : 'default',
							followMouse : true
						},
						highlighter : {
							show : true,
							showTooltip : false
						}
					};
					var maxP = result.dayData.max.P;
					graphOptions.axes.yaxis.max = maxP + ((maxP / 100) * 5);
					graphOptions.axes.y7axis.max = maxP + ((maxP / 100) * 5);

					var maxV = result.dayData.max.V;
					graphOptions.axes.y2axis.max = maxV + ((maxV / 100) * 5);

					var maxA = result.dayData.max.A;
					graphOptions.axes.y3axis.max = maxA + ((maxA / 100) * 5);

					var maxFRQ = result.dayData.max.FRQ;
					graphOptions.axes.y4axis.max = maxFRQ + ((maxFRQ / 100) * 5);

					var maxRatio = result.dayData.max.Ratio;
					(!maxRatio) ? maxRatio = 100 : maxRatio = maxRatio;
					graphOptions.axes.y5axis.max = maxRatio + ((maxRatio / 100) * 5);

					var maxT = result.dayData.max.T;
					graphOptions.axes.y6axis.max = maxT+ ((maxT / 100) * 5);

					var maxEFF = result.dayData.max.EFF;
					(!maxEFF) ? maxEFF = 100 : maxEFF = maxEFF;
					graphOptions.axes.y7axis.max = maxEFF+ ((maxEFF / 100) * 5);

					switches = result.dayData.switches;

					$("#detailsGraph").height(450);

					graphOptions.axes.xaxis.min = seriesData[0][0][0];
					graphOptions.legend.labels = result.dayData['labels'];

					handle = $.jqplot("detailsGraph", seriesData,graphOptions).destroy();
					delete handle;
					handle = $.jqplot("detailsGraph", seriesData,graphOptions);
					// testZoom();
					modLegenda(handle);

					function testZoom() {
						$.jqplot.postDrawHooks.push(zoomHandler);
					}
					function zoomHandler() {
						var c = this.plugins.cursor;
						if (c._zoom.zooming) {
							modLegenda(handle);
						} else {
							modLegenda(handle);
						}
					}

					$('#detailsSwitches').on('change','[type=checkbox]',
						function() {
							var id = $(this).attr("id");
							if (id == 'every') {
								if ($(this).is(':checked')) {
									for ( var i = 0; i < handle.series.length; i++) {
										handle.series[i].show = true; // i is an integer
									}
									$('[type="checkbox"]').attr('checked',true);
								} else {
									for ( var i = 0; i < handle.series.length; i++) {
										handle.series[i].show = false; // i is an integer
									}
									$('[type="checkbox"]').attr('checked',false);
								}
							} else {
								if ($(this).is(':checked')) {
									for ( var i = 0; i < switches[this.id].length; i++) {
										handle.series[switches[this.id][i]].show = true; // i is an integer
									}
								} else {
									for ( var i = 0; i < switches[this.id].length; i++) {
										handle.series[switches[this.id][i]].show = false; // i is an integer
									}
								}
							}
							handle.replot();
							// $('table.jqplot-table-legend').attr('class','jqplot-table-legend');
							$('table.jqplot-table-legend').css('left', 5);
							$('table.jqplot-table-legend').css('width', 400);
						});
				ajaxReady();
			}
		}
	});
	},
	init_compare : function(divId) {
		var dataTable = [];
		// initialize languages selector on the given div

		WSL.connect.getJSON('server.php?method=getCompareFilters&type=today',function(data) {
			$(divId).html(WSL.template.get('compareFilters', {
				'data' : data,
				'lang' : data.lang
			}));

			WSL.createCompareGraph($('#devicenum').val(), whichMonth,whichYear, compareMonth, compareYear, 0);

			$('#compareFilter').on('change','#devicenum',function() {
				WSL.createCompareGraph($('#devicenum').val(), $('#whichMonth').val(),$('#whichYear').val(), $('#compareMonth').val(),$('#compareYear').val()); // Initial load fast
			});
			$('#compareFilter').on('change','#whichMonth',function() {
				WSL.createCompareGraph($('#devicenum').val(), $('#whichMonth').val(),$('#whichYear').val(), $('#compareMonth').val(),$('#compareYear').val()); // Initial load fast
			});
			$('#compareFilter').on('change','#whichYear',function() {
				WSL.createCompareGraph($('#devicenum').val(), $('#whichMonth').val(),$('#whichYear').val(), $('#compareMonth').val(),$('#compareYear').val()); // Initial load fast
			});
			$('#compareFilter').on('change','#compareMonth',function() {
				WSL.createCompareGraph($('#devicenum').val(), $('#whichMonth').val(),$('#whichYear').val(), $('#compareMonth').val(),$('#compareYear').val()); // Initial load fast
			});
			$('#compareFilter').on('change','#compareYear',function() {
				WSL.createCompareGraph($('#devicenum').val(), $('#whichMonth').val(),$('#whichYear').val(), $('#compareMonth').val(),$('#compareYear').val()); // Initial//load//fast
			});
			ajaxReady();
		});
	},

	createCompareGraph : function(devicenum, whichMonth, whichYear,compareMonth, compareYear, type) {
		$('#whichMonth').val(whichMonth);
		$('#whichYear').val(whichYear);
		$('#compareMonth').val(compareMonth);
		$('#compareYear').val(compareYear);
		(type == 0) ? compareYear = 0 : compareYear = compareYear;
		$('#compareYear').val(compareYear);
		var graphOptions = {
			series : [ {
				label : 'aaa',
				yaxis : 'y2axis',
				renderer : $.jqplot.BarRenderer
			}, {
				label : 'aaa',
				xaxis : 'xaxis',
				renderer : $.jqplot.LineRenderer
			}, {
				label : 'aaa',
				xaxis : 'x2axis',
				renderer : $.jqplot.LineRenderer
			} ],
			axesDefaults : {
				useSeriesColor : true,
				tickRenderer : $.jqplot.CanvasAxisTickRenderer,
				tickOptions : {
					angle : -30,
					fontSize : '10pt'
				}
			},
			legend : {
				show : true,
				location : 's',
				placement : 'outsideGrid',
				renderer : $.jqplot.EnhancedLegendRenderer,
				rendererOptions : {
					seriesToggle : 'normal',
					numberRows : 1,
				}
			},
			seriesDefaults : {
				rendererOptions : {
					barMargin : 10,
					barWidth : 10
				},
				pointLabels : {
					show : false
				},
			},
			axes : {
				xaxis : {
					labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
					renderer : $.jqplot.DateAxisRenderer,
					angle : -30,
					tickOptions : {
						formatString : '%d-%m'
					}
				},
				x2axis : {
					labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
					renderer : $.jqplot.DateAxisRenderer,
					angle : -30,
					tickOptions : {
						formatString : '%d-%m'
					}
				},
				yaxis : {
					labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
					tickOptions : {
						formatString : '%d kWh'
					},
					angle : -30,
				},
				y2axis : {
					labelRenderer : $.jqplot.CanvasAxisLabelRenderer,
					tickOptions : {
						formatString : '%d kWh'
					},
					angle : -30,
				},
			},
			canvasOverlay : {
				show : true,
				objects : [ {
					horizontalLine : {
						name : 'test',
						yaxis : 'y2axis',
						lineWidth : 3,
						color : 'rgb(0, 125,0)',
						shadow : false
					}
				}, ]
			},
			cursor : {
				zoom : true,
				show : true,
			},
			highlighter : {
				tooltipContentEditor : tooltipCompareEditor,
				show : true
			}
		};

		$.ajax({
					url : "server.php?method=getCompareGraph&devicenum="+ devicenum + '&whichMonth=' + whichMonth+ '&whichYear=' + whichYear + '&compareMonth='+ compareMonth + '&compareYear=' + compareYear,
					beforeSend : function(xhr) {
						if (getWindowsState() == false) {
							ajaxAbort(xhr, '');
						}
					},
					method : 'GET',
					dataType : 'json',
					success : function(result) {
						if (result.dayData.data) {
							var dataDay1 = [];
							var dataDay2 = [];
							var dataDay3 = [];
							var dailykWh = [];
							var compareTable = [];
							var whichTable = [];
							for (line in result.dayData.data.compare) {
								var object = result.dayData.data.compare[line];
								dataDay1.push([ parseFloat(object[0] * 1000),
										object[2], object[3] ]);
								var item = {
									"timestamp" : parseFloat(object[0] * 1000),
									"har" : object[2],
									"date" : object[1],
									"displayKWH" : object[3],
									"harvested" : object[4],
								};
								compareTable.push([ item ]);
							}
							for (line in result.dayData.data.which) {
								var object = result.dayData.data.which[line];
								dataDay2.push([ parseFloat(object[0] * 1000),object[2], object[3] ]);
								dataDay3.push([ parseFloat(object[0] * 1000),parseFloat(object[4]) ]);
								dailykWh.push(parseFloat(object[4]));
								var item = {
									"timestamp" : parseFloat(object[0] * 1000),
									"har" : object[2],
									"date" : object[1],
									"displayKWH" : object[3],
									"harvested" : object[4],
								};

								whichTable.push([ item ]);
							}

							$("#content").append('<div id="compareGraph"></div>');
							$('#content').append('<div id="compareFigures"></div>');
							$("#compareGraph").height(500);
							$("#compareGraph").width(830);

							graphOptions.axes.x2axis.label = $("#whichMonth option:selected").text()+ ' '+ $("#whichYear option:selected").text();
							graphOptions.axes.xaxis.label = $("#compareMonth option:selected").text()+ ' '+ $("#compareYear option:selected").text();

							graphOptions.series[0].label = 'day';
							graphOptions.series[1].label = $("#whichMonth option:selected").text()+ ' '+ $("#whichYear option:selected").text();
							graphOptions.series[2].label = $("#compareMonth option:selected").text()+ ' '+ $("#compareYear option:selected").text();

							whichLength = result.dayData.data.which.length - 1;
							graphOptions.axes.xaxis.min = result.dayData.data.which[0][0] * 1000;
							graphOptions.axes.xaxis.max = result.dayData.data.which[whichLength][0] * 1000;

							compareLength = result.dayData.data.compare.length - 1;
							graphOptions.axes.x2axis.min = result.dayData.data.compare[0][0] * 1000;
							graphOptions.axes.x2axis.max = result.dayData.data.compare[compareLength][0] * 1000;

							graphOptions.axes.yaxis.min = 0;
							graphOptions.axes.y2axis.min = 0;

							var maxDailykWh = Math.max.apply(null, dailykWh);

							graphOptions.axes.y2axis.max = Math.round(maxDailykWh * parseFloat(1.10));

							handle = $.jqplot("compareGraph", [ dataDay3,dataDay2, dataDay1, ], graphOptions);
							graphOptions.canvasOverlay.objects[0].horizontalLine.y = dataDay1[0][1];
							handle.replot(graphOptions);

							$('#compareFigures').html(
									WSL.template.get('compareFigures', {
										'compare' : compareTable,
										'which' : whichTable,
										'diff' : result.dayData.data.diff,
										'lang' : result.lang
									}));
						}
					}
				});
	},
	isSuccess : function(result) {
		isError = false;

		// Try to detect if there was an error
		if (typeof result === 'undefined') {
			return true; // Nothing to check so expect that its good ???
		}
		if (typeof result.result !== 'undefined' && result.result === 'error') {
			isError = true;
		}
		if (typeof result.success !== 'undefined' && !result.success) {
			isError = true;
		}

		// If we have an error display an message
		if (isError) {
			$.pnotify({
				title : 'Error :: ' + result.exception,
				text : 'Something went wrong:<br />' + result.message,
				nonblock : true,
				hide : true,
				closer : true,
				sticker : false,
				type : 'error'
			});
		}

		return !isError;
	}
};

// api class
WSL.api.getHistoryValues = function(success) {
	WSL.connect.getJSON('server.php?method=getHistoryValues', success);
};

WSL.api.getTabs = function(page, success) {
	WSL.connect.getJSON('server.php?method=getTabs&page=' + page + '&noDB=true', success);
};

WSL.api.getCompare = function(success) {
	WSL.connect.getJSON('server.php?method=getCompareGraph', success);
};

WSL.api.getCompareFilters = function(succes) {
	WSL.connect.getJSON('server.php?method=getCompareFilters', success);
};

WSL.api.getPageIndexTotalValues = function(success) {
	WSL.connect.getJSON('server.php?method=getPageIndexTotalValues', success);
};

WSL.api.getWeatherValues = function(success) {
	WSL.connect.getJSON('api.php/Weather/live', success);
};

WSL.api.init_PageLiveValues = function(success) {
	WSL.connect.getJSON('server.php?method=getPageLiveValues', success);
};

WSL.api.getPageIndexValues = function(success) {
	WSL.connect.getJSON('server.php?method=getPageIndexValues', success);
};

WSL.api.getPageIndexBlurLiveValues = function(success) {
	WSL.connect.getJSONOnBlur('server.php?method=getPageIndexBlurLiveValues',success);
};

WSL.api.getPageIndexLiveValues = function(success) {
	WSL.connect.getJSON('server.php?method=getPageIndexLiveValues', success);
};

WSL.api.getPageTodayValues = function(success) {
	WSL.connect.getJSON('server.php?method=getPageTodayValues', success);
};

WSL.api.getPageMonthValues = function(date, success) {
	WSL.connect.getJSON('server.php?method=getPageMonthValues&date=' + date,success);
};

WSL.api.getPageYearValues = function(date, success) {
	WSL.connect.getJSON('server.php?method=getPageYearValues&date=' + date,success);
};

WSL.api.getMisc = function(devicenum, success) {
	WSL.connect.getJSON('server.php?method=getMisc&devicenum=' + devicenum,success);
};

WSL.api.getInvInfo = function(devicenum, success) {
	WSL.connect.getJSON('server.php?method=getInvInfo&devicenum=' + devicenum, success);
};

WSL.api.getInverters = function(success) {
	WSL.connect.getJSON('server.php?method=getInverters', success);
};

WSL.api.getLiveData = function(devicenum, success) {
	WSL.connect.getJSON('server.php?method=getLiveData&devicenum=' + devicenum, success);
};

WSL.api.getPlantInfo = function(devicenum, success) {
	WSL.connect.getJSON('server.php?method=getPlantInfo&devicenum=' + devicenum, success);
};

WSL.api.getLanguages = function(success) {
	WSL.connect.getJSON('server.php?method=getLanguages', success);
};

WSL.api.mainSummary = function(date, success) {
	WSL.connect.getJSON('api.php/Summary/' + date, success);
};

WSL.api.summaryPage = function(date, success) {
	WSL.connect.getJSON('api.php/Summary/' + date, success);
};

WSL.api.systemPhotos = function(success) {
	WSL.connect.getJSON('api.php/SystemPhotos/', success);
};

WSL.api.getMenu = function(success) {
	WSL.connect.getJSON('server.php?method=getMenu', success);
};

WSL.api.live = function(success) {
	WSL.connect.getJSON('api.php/Live', success);
};

WSL.api.upgradeMessageShow = function(success) {
	WSL.connect.getJSON('api.php/Config/upgradeMessageShow', success);
};

WSL.api.upgradeMessage = function(success) {
	WSL.connect.getJSON('api.php/Config/upgradeMessage', success);
};

/**
 * ***** connect class *******
 */
WSL.connect.urlPrefix = "";

/**
 * Retrieves some data from the given url
 * 
 * @param url
 * @param type
 * @param success
 * @param error
 */
WSL.connect.get = function(url, type, success, error) {
	WSL.connect.__ajax(url, 'GET', type, null, true, success, error, false);
};

/**
 * Send some data too the given url
 * 
 * @param url
 * @param type
 * @param data
 * @param success
 * @param error
 */
WSL.connect.post = function(url, type, data, success, error) {
	WSL.connect.__ajax(url, 'POST', type, data, true, success, error, false);
};

/**
 * Send some data too the given url (Add Type to delete as it is an object
 * property)
 * 
 * @param url
 * @param type
 * @param data
 * @param success
 * @param error
 */
WSL.connect.deleteType = function(url, type, data, success, error) {
	WSL.connect.__ajax(url, 'DELETE', type, data, true, success, error, false);
};

/**
 * Retrieves some JSON data from the given url
 * 
 * @param url
 * @param success
 * @param error
 */
WSL.connect.getJSON = function(url, success, error) {
	WSL.connect.get(url, 'json', success, error, false);
};

/**
 * Retrieves some JSON data from the given url during blur time
 * 
 * @param url
 * @param success
 * @param error
 */
WSL.connect.getJSONOnBlur = function(url, success, error) {
	WSL.connect.__ajax(url, 'GET', 'json', null, true, success, error, true);
};

/**
 * Send some data too the given url
 * 
 * @param url
 * @param data
 * @param success
 * @param error
 */
WSL.connect.postJSON = function(url, data, success, error) {
	WSL.connect.post(url, 'json', data, success, error);
};

/**
 * Send some data too the given url
 * 
 * @param url
 * @param data
 * @param success
 * @param error
 */
WSL.connect.deleteJSON = function(url, data, success, error) {
	WSL.connect.deleteType(url, 'json', data, success, error);
};

/**
 * Retrieves some data from the given url in SYNC mode
 * 
 * @param url
 * @param type
 * @param success
 * @param error
 */
WSL.connect.getAjaxSync = function(url, type, success, error) {
	WSL.connect.__ajax(url, 'GET', type, null, false, success, error, false);
};

/**
 * handles the connections made by wsl
 * 
 * @param url =
 *            $.ajax request URL
 * @param type =
 *            method (GET/POST/DELETE)
 * @param dataType =
 *            $.ajax dataType (default: Intelligent Guess (xml, json, script, or
 *            html))
 * @param data =
 *            array with fields to submit or null for nothing
 * @param success =
 *            Success function
 * @param runOnBlur =
 *            should this request run on Blur? (Only for GET requests)
 */
WSL.connect.stats = {};
WSL.connect.stats.success = 0;
WSL.connect.stats.error = 0;
WSL.connect.stats.connections = 0;
WSL.connect.stats.active = 0;
WSL.connect.settings = {};
WSL.connect.settings.useRunOnBlur = true;
WSL.connect.__ajax = function(url, type, dataType, data, async, success, error,runOnBlur) {
	$.ajax(
			{
				url : WSL.connect.urlPrefix + url,
				type : type,
				dataType : dataType,
				data : data,
				async : async,
				beforeSend : function(xhr) {
					WSL.connect.stats.active++;
					if (WSL.connect.settings.useRunOnBlur && type == 'GET'
							&& getWindowsState() == runOnBlur) {
						ajaxAbort(xhr);
					}
				}
			}).done(function(data) {
		WSL.connect.stats.success++;
		if (WSL.isSuccess(data)) {
			success(data);
		} else if (typeof error !== 'undefined' && error != null) {
			error(data);
		}
	}).fail(function() {
		WSL.connect.stats.error++;
	}).always(function() {
		WSL.connect.stats.active--;
		WSL.connect.stats.connections++;
	});
};

/**
 * ***** template class *******
 */
WSL.template.cache = {}; // template cache
WSL.template.baseUrl = 'js/templates/';
WSL.template.get = function(url, data) {
	template = WSL.template.getTemplate(url);
	return template(data);
};
WSL.template.getTemplate = function(url) {
	var template = WSL.template.cache[url];
	if (template == null) {
		WSL.connect.getAjaxSync(WSL.template.baseUrl + url + ".hb", 'text',
				function(source) {
					template = Handlebars.compile(source);
				});
		WSL.template.cache[url] = template;
	}
	return template;
};
WSL.template.preLoadTemplate = function(url) {
	WSL.template.getTemplate(url);
};

/**
 * ***** scrollTo class ? *******
 */
WSL.scrollTo = function(options) {
	if (typeof (options.time) == '')
		options.time = 350;
	// console.log(options);
	$('body').animate({
		scrollTop : parseInt($(options.element).offset().top + options.offset)
	}, options.time);
}
/**
 * ***** scrollTo class ? *******
 */
WSL.checkURL = function() {
	hash = document.URL.split('#');// split on #
	// go further if there is a split and more than 1 element in the array
	if (hash.length > 1) {
		shortcutFunction = hash[1];
		hash = hash[1];
		// #devices-1?id=1
		// loses: ?id=1
		if (hash.indexOf('?') > 0) {
			var splitHash = hash.split('?');
			get = splitHash[1]; // remove querystring params
			hash = splitHash[0];
			shortcutFunction = hash;
			// gives: #devices-1
		}
		// #devices-1?id=1
		// loses: -1
		if (hash.indexOf('-') > 0) {
			shortcut = hash.split('-'); // remove querystring params
			shortcutFunction = shortcut[0];
			hashId = shortcut[1];
			// gives: #devices
		}
	}
	WSL.scrollTo({
		element : '#navigation',
		time : '',
		offset : 0
	});
}

/*
 * Create StickyNavigation bar on top of page.
 */
WSL.stickyNavigation = function() {
	// grab the initial top offset of the navigation
	var sticky_navigation_offset_top = $('#navigation').offset().top;

	$('#navigation').after('<div id="navigation_sticky"></div>');
	$('#navigation_sticky').html('<div class="shell_sticky">' + $('#navigation').html()+ '<div id="main-top"></div></div>').hide();

	// our function that decides weather the navigation bar should have "fixed"
	// css position or not.
	var sticky_navigation = function() {
		var scroll_top = $(window).scrollTop(); // our current vertical position
												// from the top

		// if we've scrolled more than the navigation, change its position to
		// fixed to stick to top,
		// otherwise change it back to relative
		if (scroll_top > sticky_navigation_offset_top) {
			$('#navigation_sticky').css({
				'position' : 'fixed',
				'top' : 0,
				'width' : '100%',
				'z-index' : 1110,
				'left' : 0
			}).show();
		}
		if (scroll_top < sticky_navigation_offset_top) {
			$('#navigation_sticky').css({}).hide();
		}
	};
	// run our function on load
	sticky_navigation();

	// and run it again every time you scroll
	$(window).scroll(function() {
		sticky_navigation();
	});
}

/**
 * Capitalize String
 */
WSL.capitalize = function(string) {
	return string.charAt(0).toUpperCase() + string.slice(1);
}

WSL.notify = {};
WSL.notify.show_bar_top = function(type, title, message) {
	var opts = {
		title : title,
		text : message,
		addclass : "stack-bar-top",
		cornerclass : "",
		width : "100%",
		hide : false,
		stack : {
			"dir1" : "down",
			"dir2" : "right",
			"push" : "top",
			"spacing1" : 0,
			"spacing2" : 0
		},
		type : type
	// error | info | success
	};

	$.pnotify(opts);
};