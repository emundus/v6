function checkUpdates() {
    var url = "index.php?option=com_falang&task=cpanel.checkUpdates&format=raw&tmpl=component";
    var update = document.id("falang-update-progress").empty();
    update.set("html", "<span class='red'>"+progress_msg+"</span>");
    new Request.JSON({
        url : url,
        method : 'get',
        onSuccess : function(response) {
            update.empty();
            updateUpdates(response);
        },
        onFailure : function(xhr) {
            update.empty();
            updateUpdates('Server not responding for Updates check');
        }
    }).get();
}

function updateUpdates(response) {
    if (response.update == "true") {
        var lastversion = document.id("falang-last-version").empty();
        lastversion.set("html", "<span class='update-msg-new'> "+response.version+" </span><span class='update-msg-new'>"+response.message+"</span>");
    } else {
        //remove check button and put the version
        var lastversion = document.id("falang-last-version").empty();
        lastversion.set("html", response.version+" <span class='update-msg-info'>"+response.message+"</span>");
    }
}