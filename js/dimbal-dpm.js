/*
 * Dimbal Software WordPress Plugin
 * Version 4.0
 * Release Date: Monday May 1st, 2014
 * Author: Ben Hall
 * Copyright 2014 Dimbal Software.
 * URL: http://www.dimbal.com
 *
 */


// Jquery routine to initialize the Dimbal Object
jQuery(document).ready(function($) {
    dimbalPoll_DPM_PRO.initialize();
    dimbalUserMessages_DPM_PRO.load();
});

// Dimbal Poll Object - should rename this object to dpm
var dimbalPoll_DPM_PRO = {

    initialized:false,
    slug:"DIMBAL_CONST_DPM_PRO_SLUG",
    url_root:"",
    url_ajax:"",
    url_ajax_load:"",

    // Element Containers
    elementContainers:[],

    // Confirm Box
    blankCounter:1,
    lastDeleteChoiceId:0,

    initialize:function() {

        if(this.initialized){
            // Already initialized
            return;
        }

        //Setup a get Elements by ClassName function if one does not exist
        if (document.getElementsByClassName == undefined) {
            document.getElementsByClassName = function(className)
            {
                var hasClassName = new RegExp("(?:^|\\s)" + className + "(?:$|\\s)");
                var allElements = document.getElementsByTagName("*");
                var results = [];

                var element;
                for (var i = 0; (element = allElements[i]) != null; i++) {
                    var elementClass = element.className;
                    if (elementClass && elementClass.indexOf(className) != -1 && hasClassName.test(elementClass))
                        results.push(element);
                }
                return results;
            }
        }

        console.log("SLUG: "+this.slug);

        // Setup the URLS
        this.url_root = dimbal_dpm_vars.url;
        this.url_ajax = dimbal_dpm_vars.ajax_url;
        this.url_ajax_load = this.url_root+"/images/loading2.gif";

        //Get the initial elements
        var elements = document.getElementsByClassName(this.slug+"-WidgetWrapper");
        for(var i=0;i<elements.length;i++){
            //Get needed attributes
            var dpmQueryString;
            var element = elements[i];
            var dpmPoll = element.getAttribute("dpm_poll");
            var dpmZone = element.getAttribute("dpm_zone");
            var dpmZoneDisplayAll = element.getAttribute("dpm_zone_display_all");
            var elementId = element.getAttribute("id");

            //First enter an ID if one does not exist (required for Ajax response)
            if(elementId == undefined || elementId == ""){
                elementId = this.slug+"_"+i;
                elements[i].setAttribute("id", elementId);
            }

            // Setup the proper Display Ajax Action
            var queryParams = {
                action: dimbalPoll_DPM_PRO.slug+"-display-poll",
                dpmEId: elementId
            };



            //Now get the Poll or Zone
            if(dpmPoll != undefined && dpmPoll != "" && dpmPoll != null){
                queryParams.pollId = dpmPoll;
            }else if(dpmZone != undefined && dpmZone != "" && dpmZone != null){
                queryParams.zoneId = dpmZone;
                if(dpmZoneDisplayAll != undefined && dpmZoneDisplayAll != "" && dpmZoneDisplayAll != null && dpmZoneDisplayAll=="1"){
                    queryParams.dpmZoneDisplayAll = 1;
                }
            }

            //Now make the Ajax Request
            jQuery.post(dimbalPoll_DPM_PRO.url_ajax, queryParams, function( response ){
                dimbalPoll_DPM_PRO.generalAjaxResponse( response );
            });

        }

        // Mark this object as initialized
        this.initialized = true;
    },

    generalAjaxResponse:function( response ){

        if(response == undefined || response.dpmEId == undefined || response.html == undefined){
            // Bad response
            //console.log("Bad Data inside displayPollResponse");
        }else{

            var dpmEId = response.dpmEId;

            jQuery('#'+dpmEId).html(response.html);

            var chartElementId = "dpmGoogleChart_"+dpmEId;
            var chartElement = document.getElementById(chartElementId);

            if(chartElement != undefined){

                var parentWrapper = chartElement.parentNode;
                var dpmChartData = parentWrapper.getAttribute("dpmchartdata");
                var dpmChartOptions = parentWrapper.getAttribute("dpmchartoptions");

                //Build the Data Array for the chart
                var dataObject = JSON.parse(dpmChartData);
                var dataArray = [['Key','Value']];
                for( var i in dataObject ) {
                    var arr =[dataObject[i].text,dataObject[i].answers];
                    dataArray.push(arr);
                }

                var optionsObject = JSON.parse(dpmChartOptions);
                var options = {};
                if(optionsObject.is3d == true){
                    options.is3D = true;
                }

                // Remove the Legend if specified
                if(optionsObject.showLegend != true){
                    options.legend = 'none';
                }else{
                    options.legend = {};
                    options.legend.position = 'bottom';
                }


                // Setup the Response Distribution Pie Chart and add to the ToDo list
                var pieChart = new dimbalChartObject();
                pieChart.type = 1;
                pieChart.elemId = chartElementId;
                pieChart.data = dataArray;
                pieChart.options = options;
                dimbalCharts_DPM_PRO.chartsToDo.push(pieChart);

                dimbalCharts_DPM_PRO.drawGoogleCharts();

            }

        }

    },

    displayPoll:function(pollId, elemId){

        // Setup the proper Display Ajax Action
        var queryParams = {
            action: dimbalPoll_DPM_PRO.slug+"-display-poll",
            dpmEId: elemId,
            pollId: pollId
        };

        //Now make the Ajax Request
        jQuery.post(dimbalPoll_DPM_PRO.url_ajax, queryParams, function( response ){
            dimbalPoll_DPM_PRO.generalAjaxResponse( response );
        });
    },

    submitPoll:function(elemId){

        //Get the Primary Wrapper ID
        var wrapperElement = document.getElementById(elemId);
        var elementId = wrapperElement.getAttribute("id");

        //Get the Poll ID
        var pollId = document.getElementById("dpmFormPollId_"+elementId).value;
        var pollChoice = false;

        //Get the selected Choice
        var pollChoiceObject = document.getElementsByName("dpmFormPollChoice_"+elementId);
        var radioLength = pollChoiceObject.length;
        if(radioLength == undefined){
            if(pollChoiceObject.checked){
                pollChoice = pollChoiceObject.value;
            }
        }
        for(var i = 0; i < radioLength; i++) {
            if(pollChoiceObject[i].checked) {
                pollChoice = pollChoiceObject[i].value;
            }
        }

        // Setup the proper Display Ajax Action
        var queryParams = {
            action: dimbalPoll_DPM_PRO.slug+"-submit-poll",
            dpmEId: elemId,
            pollId: pollId,
            pollChoice: pollChoice
        };

        //Now make the Ajax Request
        jQuery.post(dimbalPoll_DPM_PRO.url_ajax, queryParams, function( response ){
            dimbalPoll_DPM_PRO.generalAjaxResponse( response );
        });
    },

    viewEarlyResults:function(elemId){

        var wrapperElement = document.getElementById(elemId);
        var elementId = wrapperElement.getAttribute("id");

        var pollId = document.getElementById("dpmFormPollId_"+elementId).value;

        // Setup the proper Display Ajax Action
        var queryParams = {
            action: dimbalPoll_DPM_PRO.slug+"-view-results",
            dpmEId: elemId,
            pollId: pollId
        };

        //Now make the Ajax Request
        jQuery.post(dimbalPoll_DPM_PRO.url_ajax, queryParams, function( response ){
            dimbalPoll_DPM_PRO.generalAjaxResponse( response );
        });

    },


    updateWidgetDisplay:function(displayId){
        jQuery('#'+response.dpmEId).hide();
    },

    // Utility function to shuffle an array
    shuffleArray:function(o){
        for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
        return o;
    },

    addBlankRow:function(objectName, beforeId){
        this.blankCounter++;
        var html = '<div><input type="checkbox" name="'+objectName+'_blankChk_'+this.blankCounter+'" checked="checked" /> <input type="text" name="'+objectName+'_blankTxt_'+this.blankCounter+'" value="" size="50" /></div>';
        jQuery("#"+beforeId).before(html);
    },

    confirmChoiceDelete:function(id){
        //Show a confirmation message saying that all data will be destroyed
        this.lastDeleteChoiceId = id;
        this.confirm({
            'title'		: 'Delete Confirmation',
            'message'	: 'Caution! You are about to delete this answer choice and all associated responses. <br />This action cannot be reverted at a later time! Continue?',
            'buttons'	: {
                'Yes'	: {
                    'class'	: 'blue',
                    'action': function(){
                        var id = dimbalPoll_DPM_PRO.lastDeleteChoiceId;
                        jQuery("input[name="+id+"_chk]").attr("checked", "deleted");
                        jQuery("input[name="+id+"_chk]").val("deleted");
                        jQuery("input[name="+id+"_chk]").css("visibility", "hidden");
                        jQuery("input[name="+id+"_chk]").css("display", "none");
                        jQuery("input[name="+id+"_txt]").css("visibility", "hidden");
                        jQuery("input[name="+id+"_txt]").css("display", "none");
                        jQuery("input[name="+id+"_dlt]").css("visibility", "hidden");
                        jQuery("input[name="+id+"_dlt]").css("display", "none");
                        dimbalPoll_DPM_PRO.lastDeleteChoiceId = 0;
                    }
                },
                'No'	: {
                    'class'	: 'gray',
                    'action': function(){}	// Nothing to do in this case. You can as well omit the action property.
                }
            }
        });
    },

    // Confirm Popup
    confirm: function(params){

        if(jQuery('#confirmOverlay').length){
            // A confirm is already shown on the page:
            return false;
        }

        var buttonHTML = '';
        jQuery.each(params.buttons,function(name,obj){
            // Generating the markup for the buttons:
            buttonHTML += '<a href="#" class="button '+obj['class']+'">'+name+'<span></span></a>';
            if(!obj.action){
                obj.action = function(){};
            }
        });

        var markup = [
            '<div id="confirmOverlay">',
            '<div id="confirmBox">',
            '<h1>',params.title,'</h1>',
            '<p>',params.message,'</p>',
            '<div id="confirmButtons">',
            buttonHTML,
            '</div></div></div>'
        ].join('');

        jQuery(markup).hide().appendTo('body').fadeIn();

        var buttons = jQuery('#confirmBox .button'),
            i = 0;

        jQuery.each(params.buttons,function(name,obj){
            buttons.eq(i++).click(function(){
                // Calling the action attribute when a click occurs, and hiding the confirm.
                obj.action();
                jQuery('#confirmOverlay').fadeOut(function(){
                    jQuery(this).remove();
                });
                return false;
            });
        });
    },

    widgetChangeType:function(elemId, typeId) {
        //console.log("Widget Change Type: elemId["+elemId+"] typeId["+typeId+"]");
        jQuery('.dimbal_dpm_widget_wrapper_'+elemId).hide();
        var elementName = elemId+"_"+typeId;
        //console.log("Final Element Name: "+elementName);
        jQuery("."+elementName).show();
    },

    reportsPollAnalysis:function(){

        // Add a loading routine to the Reports view
        console.log("dimbalPoll_DPM_PRO.url_ajax_load: "+dimbalPoll_DPM_PRO.url_ajax_load);
        jQuery("#reportResponse").html("<div style='text-align:center;'><img src='"+dimbalPoll_DPM_PRO.url_ajax_load+"' /></div>");

        var e = document.getElementById("pollId");
        var pollId = e.options[e.selectedIndex].value;

        // Get the data from the server
        var queryParams = {
            action: dimbalPoll_DPM_PRO.slug+"-admin-poll-analysis",
            pollId: pollId
        };

        //Now make the Ajax Request
        jQuery.post(dimbalPoll_DPM_PRO.url_ajax, queryParams, function( response ){
            dimbalPoll_DPM_PRO.reportsPollAnalysisResponse( response );
        });
    },

    reportsPollAnalysisResponse:function(response){

        if(response == undefined){
            // Bad response
            console.log("Bad Data inside displayPollResponse");
            jQuery("#reportResponse").html("<p>No valid data returned.  Selected poll may not active, valid or have any results yet.</p>");
        }else{

            if(response.pollObject == undefined){
                jQuery("#reportResponse").html("<p>No valid poll data returned.  Selected poll may not be active, valid or have any results yet.</p>");
            }else{

                // Setup main objects
                var pollObject = response.pollObject;
                var wrapperId = "dimbal_chart_poll_id_"+pollObject.id;
                jQuery("#reportResponse").html('<div id="'+wrapperId+'"></div>');

                /*
                var pollTableData = [];
                pollTableData.push(['Property', 'Value']);
                pollTableData.push(['Text', pollObject.text]);
                pollTableData.push(['Created Date', pollObject.createdDate]);
                pollTableData.push(['Last Hit Date', pollObject.lastHitDate]);
                pollTableData.push(['Hit Count', ''+pollObject.hitCount]);
                pollTableData.push(['Response Count', pollObject.responseCount]);
                pollTableData.push(['Answer Choices', pollObject.choices.length]);


                // Setup the Left Table and add to the To Do list
                var pollTable = new dimbalChartObject();
                pollTable.type = 3;
                pollTable.elemId = wrapperId+'_poll_table';
                pollTable.data = pollTableData;
                pollTable.options = {sort:'disable', width:'100%'};
                jQuery("#"+wrapperId).append('<div id="'+pollTable.elemId+'" class="dimbal-charts-wrapper-half"></div>');
                dimbalCharts_DPM_PRO.chartsToDo.push(pollTable);
                */

                if(response.responseCounts != undefined){
                    // Response Count analysis is provided -- make the graphs
                    var responseCounts = response.responseCounts;

                    //console.log("Response Counts");
                    //console.dir(responseCounts);

                    // Format Google Data
                    var googleDataArray = [];
                    googleDataArray.push(['Poll', 'Response Count']);
                    for (var i in responseCounts) {
                        var responseCount = responseCounts[i];

                        //console.log("Response Count Object");
                        //console.dir(responseCount);
                        if(responseCount != undefined){
                            if(responseCount.text != undefined && responseCount.answers != undefined){
                                googleDataArray.push([responseCount.text, responseCount.answers]);
                            }
                        }

                    }

                    // Setup the Response Distribution Pie Chart and add to the ToDo list
                    var pieChart = new dimbalChartObject();
                    pieChart.type = 1;
                    pieChart.elemId = wrapperId+'_dist_pie_chart';
                    pieChart.data = googleDataArray;
                    pieChart.options = {title:'Response Distribution by Answer Choice'};
                    jQuery("#"+wrapperId).append('<div id="'+pieChart.elemId+'" class="dimbal-charts-wrapper-half"></div>');
                    dimbalCharts_DPM_PRO.chartsToDo.push(pieChart);


                    // Setup Options for Response Count Bar Chart
                    var barChart = new dimbalChartObject();
                    barChart.type = 2;
                    barChart.elemId = wrapperId+'_count_bar_chart';
                    barChart.data = googleDataArray;
                    barChart.options = {title:'Response Counts by Answer Choice',legend:'none',chartArea: {left:'30%', width: '60%'}};
                    jQuery("#"+wrapperId).append('<div id="'+barChart.elemId+'" class="dimbal-charts-wrapper-half"></div>');
                    dimbalCharts_DPM_PRO.chartsToDo.push(barChart);

                }

                if(response.responseDates != undefined){
                    // Response Dates analysis is provided -- make the graphs

                    var responseDates = response.responseDates;

                    //console.log("Response Dates");
                    //console.dir(responseDates);

                    googleDataArray = [];
                    googleDataArray.push(['Date', 'Qty']);

                    for (var date in responseDates) {
                        var count = responseDates[date];
                        var dateToUse = new Date(date*1000);
                        googleDataArray.push([dateToUse, count]);

                    }

                    //console.log("Annotation Data");
                    //console.dir(googleDataArray);

                    // Setup Options for Response Count Bar Chart
                    var annotationChart = new dimbalChartObject();
                    annotationChart.type = 4;
                    annotationChart.elemId = wrapperId+'_response_annotation_chart';
                    annotationChart.data = googleDataArray;
                    annotationChart.options = {title:'Response Counts Over Time'};
                    jQuery("#"+wrapperId).append('<div id="'+annotationChart.elemId+'" class="dimbal-charts-wrapper-full" style="min-height:300px"></div>');
                    dimbalCharts_DPM_PRO.chartsToDo.push(annotationChart);

                }

            }

            // Queue up the Chart Display
            dimbalCharts_DPM_PRO.drawGoogleCharts();
        }

    },

    reportsZoneAnalysis:function(){

        // Add a loading routine to the Reports view
        jQuery("#reportResponse").html("<div style='text-align:center;'><img src='"+dimbalPoll_DPM_PRO.url_ajax_load+"' alt='loading' /></div>");

        var e = document.getElementById("zoneId");
        var zoneId = e.options[e.selectedIndex].value;

        // Get the data from the server
        var queryParams = {
            action: dimbalPoll_DPM_PRO.slug+"-admin-zone-analysis",
            zoneId: zoneId
        };

        //Now make the Ajax Request
        jQuery.post(dimbalPoll_DPM_PRO.url_ajax, queryParams, function( response ){
            dimbalPoll_DPM_PRO.reportsZoneAnalysisResponse( response );
        });
    },

    reportsZoneAnalysisResponse:function(response){

        if(response == undefined){
            // Bad response
            //console.log("Bad Data inside reportsZoneAnalysisResponse");
            jQuery("#reportResponse").html("<p>No valid data returned.  Selected zone may not have any results yet.</p>");
        }else{

            //console.log("reportsZoneAnalysisResponse : Good Response Object");
            //console.dir(response);

            if(response.zoneObject != undefined){

                var zoneObject = response.zoneObject;

                //console.log("reportsZoneAnalysisResponse : Good Zone Object");
                //console.dir(zoneObject);

                var wrapperId = "dimbal_chart_zone_id_"+zoneObject.id;
                jQuery("#reportResponse").html('<div id="'+wrapperId+'"></div>');

                if(response.pollCounts != undefined){
                    // Response Count analysis is provided -- make the graphs
                    var pollCounts = response.pollCounts;

                    //console.log("Poll Counts");
                    //console.dir(pollCounts);

                    // Format Google Data
                    var googleDataHits = [];
                    googleDataHits.push(['Zone', 'Poll Hit Counts']);
                    var googleDataResponses = [];
                    googleDataResponses.push(['Zone', 'Poll Response Counts']);
                    var googleDataHitResponseRatio = [];
                    googleDataHitResponseRatio.push(['Zone', 'Poll Hit / Response Ratio']);

                    // Loop through to build data elements
                    for (var i in pollCounts) {
                        var pollCount = pollCounts[i];

                        //console.log("Poll Count Object");
                        //console.dir(pollCount);
                        if(pollCount != undefined && pollCount.text != undefined){

                            // Hit Counts
                            if(pollCount.hitCount != undefined){
                                googleDataHits.push([pollCount.text, pollCount.hitCount]);
                            }

                            // Response Counts
                            if(pollCount.responseCount != undefined){
                                googleDataResponses.push([pollCount.text, pollCount.responseCount]);
                            }

                            // Hit / Response Ratio
                            if(pollCount.hitCount != undefined && pollCount.responseCount != undefined){
                                var ratio = 0;
                                if(pollCount.responseCount > 0){
                                    ratio = pollCount.hitCount / pollCount.responseCount;
                                }
                                googleDataHitResponseRatio.push([pollCount.text, ratio]);
                            }

                        }

                    }

                    // Setup the Hit Count Pie Chart and add to the To Do list
                    var pieChartHit = new dimbalChartObject();
                    pieChartHit.type = 1;
                    pieChartHit.elemId = wrapperId+'_hit_pie_chart';
                    pieChartHit.data = googleDataHits;
                    pieChartHit.options = {title:'Hit Distribution by Poll'};
                    jQuery("#"+wrapperId).append('<div id="'+pieChartHit.elemId+'" class="dimbal-charts-wrapper-half"></div>');
                    dimbalCharts_DPM_PRO.chartsToDo.push(pieChartHit);


                    // Setup the Hit Count Bar Chart and add to the To Do list
                    var barChartHit = new dimbalChartObject();
                    barChartHit.type = 2;
                    barChartHit.elemId = wrapperId+'_hit_bar_chart';
                    barChartHit.data = googleDataHits;
                    barChartHit.options = {title:'Hit Count by Poll',legend:'none',chartArea: {left:'30%', width: '60%'}};
                    jQuery("#"+wrapperId).append('<div id="'+barChartHit.elemId+'" class="dimbal-charts-wrapper-half"></div>');
                    dimbalCharts_DPM_PRO.chartsToDo.push(barChartHit);

                    // Setup the Response Count Pie Chart and add to the To Do list
                    var pieChartResponse = new dimbalChartObject();
                    pieChartResponse.type = 1;
                    pieChartResponse.elemId = wrapperId+'_response_pie_chart';
                    pieChartResponse.data = googleDataResponses;
                    pieChartResponse.options = {title:'Response Distribution by Poll'};
                    jQuery("#"+wrapperId).append('<div id="'+pieChartResponse.elemId+'" class="dimbal-charts-wrapper-half"></div>');
                    dimbalCharts_DPM_PRO.chartsToDo.push(pieChartResponse);


                    // Setup the Response Count Bar Chart and add to the To Do list
                    var barChartResponse = new dimbalChartObject();
                    barChartResponse.type = 2;
                    barChartResponse.elemId = wrapperId+'_response_bar_chart';
                    barChartResponse.data = googleDataResponses;
                    barChartResponse.options = {title:'Response Count by Poll',legend:'none',chartArea: {left:'30%', width: '60%'}};
                    jQuery("#"+wrapperId).append('<div id="'+barChartResponse.elemId+'" class="dimbal-charts-wrapper-half"></div>');
                    dimbalCharts_DPM_PRO.chartsToDo.push(barChartResponse);

                    /*
                    // Setup the Ratio Count Pie Chart and add to the To Do list
                    var pieChartRatio = new dimbalChartObject();
                    pieChartRatio.type = 1;
                    pieChartRatio.elemId = wrapperId+'_ratio_pie_chart';
                    pieChartRatio.data = googleDataHitResponseRatio;
                    pieChartRatio.options = {title:'Hit / Response Ratio Distribution by Poll'};
                    jQuery("#"+wrapperId).append('<div id="'+pieChartRatio.elemId+'" class="dimbal-charts-wrapper-half"></div>');
                    dimbalCharts_DPM_PRO.chartsToDo.push(pieChartRatio);


                    // Setup the Ratio Count Bar Chart and add to the To Do list
                    var barChartRatio = new dimbalChartObject();
                    barChartRatio.type = 2;
                    barChartRatio.elemId = wrapperId+'_ratio_bar_chart';
                    barChartRatio.data = googleDataHitResponseRatio;
                    barChartRatio.options = {title:'Hit / Response Ratio by Poll'};
                    jQuery("#"+wrapperId).append('<div id="'+barChartRatio.elemId+'" class="dimbal-charts-wrapper-half"></div>');
                    dimbalCharts_DPM_PRO.chartsToDo.push(barChartRatio);
                    */
                }

                if(response.responseDates != undefined){
                    // Response Dates analysis is provided -- make the graphs

                }

            }else{
                // Not doing anything if the zone object is undefined
            }

            // Queue up the Chart Display
            dimbalCharts_DPM_PRO.drawGoogleCharts();
        }

    },

    reportsAppAnalysis:function(){

    },

    reportsTimeframeAnalysis:function(){

    }
};


var dimbalChartObject = function(){
    var elemId = null;
    var type = null;      // 1 for pie chart, 2 for bar chart, 3 for table, 4 annotation, 5 column
    var options = [];
    var data = [];
};

var dimbalCharts_DPM_PRO = {

    // Google Charts
    isGoogleLoaded:false,
    isGoogleVisLoaded:false,

    // Charts that need to be drawn
    chartsToDo:[],

    loadGoogle:function() {
        if(dimbalCharts_DPM_PRO.isGoogleLoaded){
            dimbalCharts_DPM_PRO.loadGoogleVisualization();
        }else{
            dimbalCharts_DPM_PRO.loadJsFile("https://www.google.com/jsapi?callback=dimbalCharts_DPM_PRO.loadGoogleVisualization");
        }
    },

    loadGoogleVisualization:function() {
        //Validate google object is loaded
        if(google == undefined){
            dimbalCharts_DPM_PRO.loadGoogle();
            return; //Do the main loader
        }else{
            dimbalCharts_DPM_PRO.isGoogleLoaded = true;
        }

        //Load the visualizer
        if(dimbalCharts_DPM_PRO.isGoogleVisLoaded){
            dimbalCharts_DPM_PRO.drawGoogleCharts();
        }else{
            google.load("visualization", "1", {'packages':['corechart','table','annotationchart'], "callback" : dimbalCharts_DPM_PRO.drawGoogleCharts});
        }
    },

    drawGoogleCharts:function(){

        //Validate that google and google.visualization is loaded
        if(dimbalCharts_DPM_PRO.isGoogleLoaded){
            if(google.visualization == undefined){
                dimbalCharts_DPM_PRO.loadGoogleVisualization();
                return; //Do the visualization loader
            }else{
                dimbalCharts_DPM_PRO.isGoogleVisLoaded = true;
            }
        }else{
            dimbalCharts_DPM_PRO.loadGoogle();
            return;	//Start over at the google loader and exit this function
        }

        console.log("Charts to Do");
        console.dir(dimbalCharts_DPM_PRO.chartsToDo);

        // Loop through any elements added to our to-do array...
        while(dimbalCharts_DPM_PRO.chartsToDo.length > 0){
            var dimbalChart = dimbalCharts_DPM_PRO.chartsToDo[0];

            //console.log("Drawing Google Chart:");
            //console.dir(dimbalChart);

            if(dimbalChart.elemId != undefined){
                var elemId = dimbalChart.elemId;
                var chartElement = document.getElementById(elemId);
                if(chartElement != undefined){

                    // Load the data array
                    var dataArray = [];
                    if(dimbalChart.data != undefined && dimbalChart.data.length > 1){
                        dataArray = dimbalChart.data;
                    }else{
                        // Add an error message on the Chart
                        jQuery("#"+elemId).html("<h3 style='text-align: center;'>No Data to Display</h3>");

                        // Remove the entry from the array
                        dimbalCharts_DPM_PRO.removeArrayEntry(dimbalCharts_DPM_PRO.chartsToDo, 0);

                        continue;   // Can't have missing data or data with just one row in it... Skip this chart
                    }

                    // Load the Options
                    var options = [];
                    if(dimbalChart.options != undefined){
                        options = dimbalChart.options;
                    }

                    // Format for Google
                    var data = google.visualization.arrayToDataTable(dataArray);


                    /*  Assignments vary depending on Chart Type - so skipping for now
                    // Assign random charting colors
                    if(options.colors == undefined){
                        options.colors=[];
                    }
                    for(var i = 0;i < data.getNumberOfRows();i++){      // getNumberOfRows can only be called once data is Google Formatted
                        var randomColor = dimbalCharts_DPM_PRO.getRandomColor();
                        console.log("Random Color: "+randomColor);
                        options.colors.push(randomColor);
                    }
                    */

                    // Setup the Chart Visualization Object
                    var chart = null;
                    if(dimbalChart.type == 1 || dimbalChart.type == undefined){
                        chart = new google.visualization.PieChart(chartElement);
                    }else if(dimbalChart.type == 2){
                        chart = new google.visualization.BarChart(chartElement);
                    }else if(dimbalChart.type == 3){
                        chart = new google.visualization.Table(chartElement);
                    }else if(dimbalChart.type == 4){
                        chart = new google.visualization.AnnotationChart(chartElement);
                    }else if(dimbalChart.type == 5){
                        chart = new google.visualization.ColumnChart(chartElement);
                    }

                    console.log("Options");
                    console.dir(options);

                    // Draw the chart
                    if(chart != null){
                        chart.draw(data, options);
                    }

                }
            }else{
                // Bad Element record in the chartsToDo list
            }

            // Remove the entry from the array
            dimbalCharts_DPM_PRO.removeArrayEntry(dimbalCharts_DPM_PRO.chartsToDo, 0);

        }
    },

    // Create an additional JS file and loads it into the DOM
    loadJsFile:function(filename){
        //create a script element and set it's type and async attributes
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.async = true;
        script.src = filename;
        //add the script element to the DOM
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(script, s);
    },

    removeArrayEntry:function(array, from, to){
        var rest = array.slice((to || from) + 1 || array.length);
        array.length = from < 0 ? array.length + from : from;
        return array.push.apply(array, rest);
    },

    getRandomColor:function(){
        var letters = '0123456789ABCDEF'.split('');
        var color = '#';
        for (var i = 0; i < 6; i++ ) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }
};


var dimbalUserMessages_DPM_PRO = {
    counter:0,
    load: function(){
        var html = jQuery("#dimbal-user-messages-tmp").html();
        if(html != undefined){
            jQuery("#dimbalWrapper").prepend(html);
            jQuery("#dimbal-user-messages-tmp").remove();
            setTimeout(function(){
                jQuery(".dimbal-user-message").fadeOut();
            }, 5000);
        }
    },
    remove: function(id){
        console.log("Removing Message with ID: "+id);
        jQuery('#dimbal_user_message_'+id).fadeOut();
    }
};
