function loadJS(file_path, async = true) {
    let scriptEle = document.createElement('script');

    scriptEle.setAttribute('src', file_path);
    scriptEle.setAttribute('type', "text/javascript");
    scriptEle.setAttribute('async', async);

    document.body.appendChild(scriptEle);

    scriptEle.addEventListener("load", () => {});
    scriptEle.addEventListener("error", (ev) => {});
}

let fnum = '';

if(rows) {
    Object.entries(rows).forEach((row,key) => {
        if(fnum === '') {
            const fnum_key = Object.keys(row[1]).find((element) => element.includes('fnum'));

            fnum = row[1][fnum_key];
        }
    })

    if(fnum !== '') {
        fetch(window.location.origin + '/index.php?option=com_emundus&view=file&layout=evaluation&format=raw&fnum='+fnum).then(function (response) {
            return response.text();
        }).then(function (html) {

            Swal.fire({
                html: html,
                showConfirmButton: false,
                customClass: {
                    container: 'em-application-modal-container',
                    popup: 'em-w-100',
                },
            }).then(() => {
                const filters = document.querySelector('.fabrik_filter_submit');
                if(filters) {
                    filters.click();
                }
            });

            loadJS(window.location.origin + '/media/com_emundus_vue/chunk-vendors_emundus.js');
            loadJS(window.location.origin + '/media/com_emundus_vue/app_emundus.js');

        }).catch(function (err) {
            // There was an error
            console.warn('Something went wrong.', err);
        });
    }
}

