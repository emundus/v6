function checkPluginsUpdate(){
    var url = "https://www.faboba.com/update/falang/falangplugin_j4.json";
    jQuery.getJSON(url,function(){})
        .done(function(data){
            jQuery.each(data, function(key, val) {
                setDataByPlugin(key, val);
            });
        })
        .fail(function(){
            console.log('failure');
        })
}

function checkUpdates() {
    var url = "index.php?option=com_falang&task=cpanel.checkUpdates&format=raw&tmpl=component";
    var update = document.id("falang-update-progress").empty();
    update.set("html", "<span class='red'>"+progress_msg+"</span>");
    jQuery.getJSON(url,function(){})
        .done(function (data) {
            update.empty();
            updateUpdates(data);
        })
        .fail(function () {
            update.empty();
            updateUpdates('Server not responding for Updates check');
        })
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

function setDataByPlugin(extension, data){

    var tr = jQuery('tr#row_' + extension);

    if (!tr) {
        return;
    }

    var v_new = String(data['version']).trim();

    tr.find('.new_version').text(v_new);
    var v_current = tr.find('span.version').text().trim();
    if (v_current != null){
        // Current version is older than next_version
        var compare = compareVersions(v_current,v_new);
        if (compare == '<'){
            //display download link for new version
            tr.find('.new_version_link').show();
        }
    }
}

//compare version from regurlabs
function compareVersions (num1, num2) {
    num1 = num1.split('.');
    num2 = num2.split('.');

    var let1 = '';
    var let2 = '';

    var max = Math.max(num1.length, num2.length);
    for (var i = 0; i < max; i++) {
        if (typeof num1[i] === 'undefined') {
            num1[i] = '0';
        }
        if (typeof num2[i] === 'undefined') {
            num2[i] = '0';
        }

        let1    = num1[i].replace(/^[0-9]*(.*)/, '$1');
        num1[i] = parseInt(num1[i]);
        let2    = num2[i].replace(/^[0-9]*(.*)/, '$1');
        num2[i] = parseInt(num2[i]);

        if (num1[i] < num2[i]) {
            return '<';
        }

        if (num1[i] > num2[i]) {
            return '>';
        }
    }

    // numbers are same, so compare trailing letters
    if (let2 && (!let1 || let1 > let2)) {
        return '>';
    }

    if (let1 && (!let2 || let1 < let2)) {
        return '<';
    }

    return '=';
}
