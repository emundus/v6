function removeShared (request_id, ccid, fnum) {
    //TODO: Ask confirmation before delete file request
    if (confirm(Joomla.JText._('COM_EMUNDUS_APPLICATION_SHARE_CONFIRM_DELETE')) == true) {
        let formData = new FormData();

        formData.append('request_id', request_id);
        formData.append('fnum', fnum);
        formData.append('ccid', ccid);

        fetch('index.php?option=com_emundus&controller=application&task=removeshareduser', {
            body: formData,
            method: 'post',
        }).then((response) => {
            if (response.ok) {
                return response.json();
            }
        }).then((res) => {
            if (res.status) {
                document.querySelector('#collaborator_block_' + request_id).remove();
            }
        });
    }
}

function sendNewEmail(request_id, ccid, fnum) {
    let formData = new FormData();

    formData.append('request_id', request_id);
    formData.append('fnum', fnum);
    formData.append('ccid', ccid);

    document.getElementById('email_icon_'+request_id).innerHTML = 'sync';
    document.getElementById('email_icon_'+request_id).classList.add('animate-spin');

    fetch('index.php?option=com_emundus&controller=application&task=sendnewcollaborationemail', {
        body: formData,
        method: 'post',
    }).then((response) => {
        if (response.ok) {
            return response.json();
        }
    }).then((res) => {
        if(res.status) {
            Swal.showValidationMessage('<span class="material-icons-outlined">mark_email_read </span>'+res.msg);

            document.getElementById('email_icon_'+request_id).classList.remove('animate-spin');
            document.getElementById('email_icon_'+request_id).innerHTML = 'done';

            setTimeout(() => {
                Swal.resetValidationMessage();
                document.getElementById('email_icon_'+request_id).innerHTML = 'send';
            }, 4000);
        } else {
            Swal.showValidationMessage('<span class="material-icons-outlined text-red-500">error</span>'+res.msg);
            document.getElementById('email_icon_'+request_id).classList.remove('animate-spin');
            document.getElementById('email_icon_'+request_id).innerHTML = 'send';

            setTimeout(() => {
                Swal.resetValidationMessage();
            }, 4000);
        }
    });
}

function toggleRequests() {
    let requests = document.querySelector('#collaborators_requests');
    let requestsIcon = document.querySelector('#requests_icon');
    if(requests.classList.contains('hidden')) {
        requests.classList.remove('hidden');
        requestsIcon.innerHTML = 'expand_more';
    } else {
        requests.classList.add('hidden');
        requestsIcon.innerHTML = 'expand_less';
    }
}

function updateRight(request_id, ccid, fnum, right, value) {

    let formData = new FormData();
    formData.append('request_id', request_id);
    formData.append('fnum', fnum);
    formData.append('ccid', ccid);
    formData.append('right', right);
    formData.append('value', value);

    fetch('index.php?option=com_emundus&controller=application&task=updateright', {
        body: formData,
        method: 'post',
    }).then((response) => {
        if (response.ok) {
            return response.json();
        }
    }).then((res) => {
        if(res.status) {
            Swal.showValidationMessage(res.msg);

            setTimeout(() => {
                Swal.resetValidationMessage();
            }, 2000);
        }
    });
}