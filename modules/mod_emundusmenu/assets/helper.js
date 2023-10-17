// get profile color
    let url = window.location.origin+'/index.php?option=com_emundus&controller=users&task=getcurrentprofile';
    fetch(url, {
        method: 'GET',
    }).then((response) => {
        if (response.ok) {
            return response.json();
        }
        throw new Error(Joomla.JText._('COM_EMUNDUS_ERROR_OCCURED'));
    }).then((result) => {
        if(result.status) {

            let profile_color = result.data.class;
            let profile_state = result.data.published;

            let label_colors = {
                'lightpurple' : '--em-purple-1',
                'purple' : '--em-purple-2',
                'darkpurple' : '--em-purple-2',
                'lightblue' : '--em-light-blue-1',
                'blue' : '--em-blue-2',
                'darkblue' : '--em-blue-3',
                'lightgreen' : '--em-green-1',
                'green' : '--em-green-2',
                'darkgreen' : '--em-green-2',
                'lightyellow' : '--em-yellow-1',
                'yellow' : '--em-yellow-2',
                'darkyellow' : '--em-yellow-2',
                'lightorange' : '--em-orange-1',
                'orange' : '--em-orange-2',
                'darkorange' : '--em-orange-2',
                'lightred' : '--em-red-1',
                'red' : '--em-red-2',
                'darkred' : '--em-red-2',
                'lightpink' : '--em-pink-1',
                'pink' : '--em-pink-2',
                'darkpink' : '--em-pink-2',
            };

            if(profile_state == 1) { // it's an applicant profile

                let root = document.querySelector(':root');
                let css_var = getComputedStyle(root).getPropertyValue("--em-primary-color");

                document.documentElement.style.setProperty("--em-profile-color", css_var);

            }
            else  { // it's a coordinator profile

                if(profile_color != '') {

                    profile_color = profile_color.split('-')[1];

                    if(label_colors[profile_color] != undefined) {
                        let root = document.querySelector(':root');
                        let css_var = getComputedStyle(root).getPropertyValue(label_colors[profile_color]);

                        document.documentElement.style.setProperty("--em-profile-color", css_var);
                    }
                }

            }
        }
    });

