document.addEventListener('click', function(event) {
    if (event.target.classList.contains('generate-config')) {
        event.stopPropagation();
        event.preventDefault();

        //const activeTab = document.querySelector('.tab-pane.active');
        let wellKnownURL = document.querySelector('#jform_params_well_known_url').value;

        if (wellKnownURL == '') {
            alert('Please enter a well-known URL.');
            return;
        }

        const url = new URL(wellKnownURL);
        if (url.protocol != 'https:') {
            alert('Well-known URL must be HTTPS.');
            return;
        }

        fetch('index.php?option=com_emundus&controller=plugins&task=get_well_known_configuration&url=' + url, {
            method: 'GET'
        }).then(function(response) {
                if (response.ok) {
                    return response.json();
                }
        }).then(function(json) {
            if(json.status) {
                document.getElementById('jform_params_scopes').setAttribute('value', json.data.scopes_supported.join(','));
                document.getElementById('jform_params_auth_url').setAttribute('value', json.data.authorization_endpoint);
                document.getElementById('jform_params_token_url').setAttribute('value', json.data.token_endpoint);
                document.getElementById('jform_params_sso_account_url').setAttribute('value', json.data.userinfo_endpoint);
            }
        }).catch(function(error) {
            console.log(error);
            alert('Error fetching well-known URL.');
        });
    }
});