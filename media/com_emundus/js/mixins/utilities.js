function addLoader(container = 'body') {
    if(window.document.getElementById('em-dimmer') === null) {
        let loaderElement = window.document.createElement('div');
        loaderElement.id = 'em-dimmer';
        loaderElement.classList.add('em-page-loader');

        window.document.querySelector(container).insertAdjacentElement('afterend', loaderElement);
    }
}

function removeLoader() {
    const loader = document.getElementById('em-dimmer');
    if (loader!== null) {
       loader.remove();
    }
}

function getUrlParameter(url, sParam) {
    var sURLVariables = url.split('&');

    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === sParam) {
            if (typeof sParameterName[1] !== 'undefined') {
                return sParameterName[1];
            } else {
                return '';
            }
        }
    }
}

function getCookie(cname) {
    var name = cname + '=';
    var ca = document.cookie.split(';');
    for (var i=0; i<ca.length; i++) {
        var c = ca[i].trim();
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '';
}

function getSearchBox(id, fatherId) {
    var index = fatherId.split('-');
    $.ajax({
        type: 'get',
        url: 'index.php?option=com_emundus&controller=files&task=getbox&ItemId=' + itemId,
        dataType: 'json',
        data: ({
            id: id,
            index: index[index.length - 1]
        }),
        success: function(result) {
            if (result.status) {
                $('#em-adv-fil-' + index[index.length - 1]).remove();
                $('#em_adv_fil_' + index[index.length - 1] + '_chosen').remove();
                $('#' + fatherId).append(result.html);
                $('.chzn-select').chosen();

                reloadData($('#view').val());
            }
        },
        error: function(jqXHR) {
            console.log(jqXHR.responseText);
        }
    });
}

function componentToHex(c) {
    var hex = c.toString(16);
    return hex.length == 1 ? '0' + hex : hex;
}

function rgbToHex(r, g, b) {
    return '#' + componentToHex(r) + componentToHex(g) + componentToHex(b);
}

function getXMLHttpRequest() {
    var xhr = null;

    if (window.XMLHttpRequest || window.ActiveXObject) {
        if (window.ActiveXObject) {
            try {
                xhr = new ActiveXObject('Msxml2.XMLHTTP');
            } catch(e) {
                xhr = new ActiveXObject('Microsoft.XMLHTTP');
            }
        } else {
            xhr = new XMLHttpRequest();
        }
    } else {
        alert('Votre navigateur ne supporte pas l\'objet XMLHTTPRequest...');
        return null;
    }

    return xhr;
}

function decodeEntity(inputStr) {
    var textarea = document.createElement('textarea');
    textarea.innerHTML = inputStr;
    return textarea.value;
}
