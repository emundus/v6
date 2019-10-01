/**
 * JCH Optimize - Plugin to aggregate and minify external resources for
 * optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

//Initialize timer object
var timer = null;
//Array of file objects to optimize
var files=[];
//Array of subdirectories under expanded folder in file tree
var subdirs=[];
//Count of current file being optimized initialized to 0	
var current=0;
//Total amount of files to be optimized
var cnt = 0;
var total=0;
//Amount of files that are actually optimized
var optimize=0;
//Path of expanded folder
var dir='';
//Path of log file
var log_path='';
//Object containing relevant settings saved in the plugin
var params={};
//set to fail if request not authenticated
var status='success';
//Message if not authenticated
var authmessage='';
 
function jchOptimizeImages(page) {
       
        params.pro_downloadid = jQuery("input[id$='pro_downloadid']").val();
        params.hidden_api_secret = jQuery("input[id$='hidden_api_secret']").val();
        params.ignore_optimized = jQuery("input:radio[name*='ignore_optimized']:checked").val();
      
	//Ensure Download ID is entered before proceeding
        if (params.pro_downloadid.length == 0)
        {
                alert(jch_noproid);
                return false;
        }

	//Get the folder in the file tree that is expanded
        var li = jQuery("#file-tree-container ul.jqueryFileTree").find("li.expanded").last();

	//At least one of the subfolder or files in Explorer View needs to be checked
        if (jQuery("#files-container input[type=checkbox]:checked").length) {
		//Save the path of the expanded folder found in the rel attribute of the anchor tag
                dir = {path: li.find("a").attr("rel")};

		//Paths of subfolders are saved in the value of each checkbox, push each checked box in subdirs
                jQuery("#files-container li.directory input[type=checkbox]:checked").each(function () {
                        subdirs.push(jQuery(this).val());
                });

		//Iterate over each selected file in expanded directory
                jQuery("#files-container li.file input[type=checkbox]:checked").each(function () {
			//Create file object
                        var file = {};
                        
			//Save path of file stored in value of checkbox
                        file.path = jQuery(this).val();
                        
			//Get the new width of file if entered
                        if(jQuery(this).parent().parent().find("input[name=width]").val().length){
                                file.width = jQuery(this).parent().parent().find("input[name=width]").val();
                        };
                        
			//Get the new height of file is entered
                        if(jQuery(this).parent().parent().find("input[name=height]").val().length){
                                file.height = jQuery(this).parent().parent().find("input[name=height]").val();
                        };
                        
			//Push file object in files array.
			files.push(file);
                });

		//Load progress bar with log window
                jQuery("#optimize-images-container")
                        .html('<div id="progressbar"></div> \
			 <div id="optimize-status">Gathering files to optimize. Please wait...</div> \
                         <div><ul id="optimize-log"></ul></div>');
                jQuery("#progressbar").progressbar({value: 0});

		//Call function to get names of all files in selected subdirectories
                jQuery.when(updateStatus(page, {}, params, 'getfiles')).then(function(){

			var no_files_msg = ' files found.';

			if (total > 0)
			{
				no_files_msg += ' Uploading files for optimization...';
			}

			jQuery("div#optimize-status").html(total.toLocaleString() + no_files_msg);

			//call function to optimize files in array
			optimizeImages(page); }); } else { alert(jch_message);
			} } ;

function optimizeImages(page) {
	//array to hold ajax objects
	var deferreds = [];
	//Number of ajax requests to send before waiting for Ajax completion
	var loops = 10; 
	//Size of packets of files to send for optimization
	var filepacksize = 10;

	for (i = 0; i < loops && cnt < total; i++) {
		//Packets of files
		var filepack = [];

		for (j = 0; j < filepacksize && cnt < total; cnt++, j++) {
			filepack.push(files[cnt]); }

		deferreds.push(updateStatus(page, filepack, params,
			'optimize')); }

	//When number of Ajax requests in loop is queued, wait until all Ajax
	//requests are completed before looping in another queue or print
	//completion message
	jQuery.when.apply(jQuery, deferreds).then(function(){
	
		if(status === 'fail') { window.location.href = page +
				"&status=fail&msg=" +
				encodeURIComponent(authmessage);

			return false; }

		if(cnt < total) { optimizeImages(page); } else {
			jQuery("ul#optimize-log").append('<li>Adding logs to '
				+ log_path +
				'/plg_jch_optimize.logs.php...</li>');

			setTimeout(function () {
				jQuery("ul#optimize-log").append('<li>Done!</li>');
			}, 1000);

			window.location.href = page + "&dir=" +
				encodeURIComponent(dir.path) + "&cnt=" +
				optimize; }
	
	}); 
	
} ;

/** Communicates with the website server via ajax re the files to be optimized
 *
 * @param page		string 	Url of admin settings page @param filepack
 * array   Package of files to be optimized 	@param params	object	Array
 * of plugin parameters obtained via javascript from settings page @param task
 * string 	Current task being completed (getfiles|optimize)
 */
function updateStatus(page, filepack, params, task) {

	//create timestamp to append to ajax call to prevent caching
	var timestamp = getTimeStamp();
        
	var xhr = jQuery.ajax({ dataType: 'json', url: jch_ajax_optimizeimages
	+ '&_=' + timestamp, data: {'filepack': filepack, 'subdirs': subdirs,
	'params': params, 'task': task}, success: function (response) {
                       
			//If we haven't started optimizing files then get the
		//total amount to be optimized
			if (task == 'getfiles') {
				//Add the selected files in expanded directory
		//to the files in selected subdirectories recursively
				
				//convert the data object to an array of objects
				var dataArray = Object.keys(response.data.files).map(i => response.data.files[i])
				files = jQuery.merge(files, dataArray); 
				total = files.length;
                                
				log_path = response.data.log_path; } else {
					if(!response.success) {
						logMessage(response.message);

					//If authentication error abort with
						//error message
					if (response.code === 403)
					{
						status = 'fail';
						authmessage = response.message;

						return false;
					}
				}
				else
				{
					response.data.forEach(function(item){ 

						//Calculate percentage of files that are currently optimized
						current++;
						pbvalue = Math.floor((current / total) * 100);

						if (pbvalue > 0) {
							//Update progress bar with new percentage
							jQuery('#progressbar').progressbar({
								value: pbvalue
							});
						}

						if(item.success)
						{
							//Increment number of files optimized
							optimize++;
						}

						jQuery('div#optimize-status').html('Processed ' + current.toLocaleString() + ' / ' + total.toLocaleString() + ' files, ' + optimize.toLocaleString() + ' optimized...');

						logMessage(item.message);
					});
				}
			}
                        
                },
                fail: function (jqXHR) {

                        jQuery("#progressbar").progressbar({
                                value: 100
                        });
                        window.location.href = page + "&status=fail&msg=" + encodeURIComponent(jqXHR.status + ": " + jqXHR.statusText);
                }
        });

	return xhr;
}

function logMessage(message)
{
	var logWindow = jQuery('ul#optimize-log');
	//Append message to log window
	logWindow.append('<li>' + message + '</li>');
	//Scroll to bottom
	logWindow.animate({ scrollTop: logWindow.prop("scrollHeight")}, 20);
}

