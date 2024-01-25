function removeShared (request_id, ccid, fnum) {
    //TODO: Ask confirmation before delete file request
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
        if(res.status) {
            document.querySelector('#collaborator_block_'+request_id).remove();
        }
    });
}

function sendNewEmail(request_id, ccid, fnum) {
    let formData = new FormData();

    formData.append('request_id', request_id);
    formData.append('fnum', fnum);
    formData.append('ccid', ccid);

    fetch('index.php?option=com_emundus&controller=application&task=sendnewcollaborationemail', {
        body: formData,
        method: 'post',
    }).then((response) => {
        if (response.ok) {
            return response.json();
        }
    }).then((res) => {
        if(res.status) {

        }
    });
}

function toggleRequests() {
    let requests = document.querySelector('#collaborators_requests');
    let requestsIcon = document.querySelector('#requests_icon');
    if(requests.classList.contains('tw-hidden')) {
        requests.classList.remove('tw-hidden');
        requestsIcon.innerHTML = 'expand_more';
    } else {
        requests.classList.add('tw-hidden');
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

        }
    });
}