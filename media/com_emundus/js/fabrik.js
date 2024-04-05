/***
 **
 * Fabrik functions
 **
 ***/

/**
 * Hide one or multiple elements
 *
 * Examples :
 * hideFabrikElt(fab.get('jos_emundus_1001_00___name'), clearElements = true);
 * hideFabrikElt([fab.get('jos_emundus_1001_00___name'), fab.get('jos_emundus_1001_00___city')], clearElements = true);
 *
 * @param elements
 * @param clearElements
 */
function hideFabrikElt(elements, clearElements = false) {
    if (!Array.isArray(elements)) elements = [elements];

    elements.forEach((element,index) => {
        if (element) {
            if (clearElements && element.plugin !== '') {
                element.clear();
            }
            element.hide();
        } else {
            console.log(`hideFabrikElt: Element at index ${index} is undefined`);
        }
    });

}

/**
 * Show one or multiple elements
 *
 * Examples :
 * showFabrikElt(fab.get('jos_emundus_1001_00___name'));
 * showFabrikElt([fab.get('jos_emundus_1001_00___name'), fab.get('jos_emundus_1001_00___city')]);
 *
 * @param elements
 */
function showFabrikElt(elements) {
    if (!Array.isArray(elements)) elements = [elements];

    elements.forEach((element,index) => {
        if (element) {
            element.show();
        } else {
            console.log(`showFabrikElt: Element at index ${index} is undefined`);
        }
    });

}


/**
 * Hide one or multiple groups by target an element of those groups
 *
 * Examples :
 * hideFabrikGroupByElt(fab.get('jos_emundus_1001_00___name'), clearElements = true);
 * hideFabrikGroupByElt([fab.get('jos_emundus_1001_00___name'), fab.get('jos_emundus_1001_00___city')], clearElements = true);
 *
 * @param elements
 * @param clearElements
 */
function hideFabrikGroupByElt(elements, clearElements = false) {
    if (!Array.isArray(elements)) elements = [elements];

    let form = null;

    elements.forEach((element,index) => {
        if (element) {
            document.getElementById(`group${element.groupid}`).classList.add('fabrikHide');

            if (clearElements) {

                if(form === null){
                    form = Fabrik.getBlock(element.form.block);
                }

                if(form) {
                    Object.values(form.elements).map((all_element) => {
                        if (all_element.groupid === element.groupid && all_element.plugin !== '') {
                            all_element.clear();
                        }
                    });
                }
            }
        } else {
            console.log(`hideFabrikGroupByElt: Element at index ${index} is undefined`);
        }
    });

}

/**
 * Show one or multiple groups by target an element of those groups
 *
 * Examples :
 * showFabrikGroupByElt(fab.get('jos_emundus_1001_00___name'));
 * showFabrikGroupByElt([fab.get('jos_emundus_1001_00___name'), fab.get('jos_emundus_1001_00___city')]);
 *
 * @param elements
 */
function showFabrikGroupByElt(elements) {
    if (!Array.isArray(elements)) elements = [elements];

    elements.forEach((element,index) => {
        if (element) {
            document.getElementById(`group${element.groupid}`).classList.remove('fabrikHide');
        } else {
            console.log(`showFabrikGroupByElt: Element at index ${index} is undefined`);
        }
    });
}

/**
 * Hide one or multiple groups with ID
 *
 * Examples :
 * hideFabrikGroup(748, clearElements = true);
 * hideFabrikGroup([748, 123, 564], clearElements = true);
 *
 * @param groups
 * @param clearElements
 */
function hideFabrikGroup(groups, clearElements = false) {
    if (!Array.isArray(groups)) groups = [groups];

    groups.forEach((group,index) => {
        if (group) {
            let selector = document.getElementById(`group${group}`);
            if (selector) selector.classList.add('fabrikHide');

            if (clearElements) {
                let formDiv = document.querySelector(`.fabrikForm`).getAttribute('name');
                let form = Fabrik.getBlock(formDiv);

                Object.values(form.elements).map((element) => {
                    if (element.groupid == group) element.clear();
                });
            }
        } else {
            console.log(`hideFabrikGroup: Group at index ${index} is undefined`);
        }
    });
}

/**
 * Show one or multiple groups by ID
 *
 * Examples :
 * showFabrikGroup(748);
 * showFabrikGroup([748, 123, 456);
 *
 * @param groups
 */
function showFabrikGroup(groups) {
    if (!Array.isArray(groups)) groups = [groups];

    groups.forEach((group,index) => {
        if (group) {
            document.getElementById(`group${group}`).classList.remove('fabrikHide');
        }  else {
            console.log(`showFabrikGroup: Group at index ${index} is undefined`);
        }
    });

}

/**
 * Disabled checkboxes when a limit has reached
 *
 * Examples :
 * defineCheckboxLimit(fab.get('jos_emundus_1001_00___name'));
 *
 * @param element
 * @param max
 */
function defineCheckboxLimit(element, max) {
    var allCheck = element.subElements;

    if(element.get('value').length >= max){
        Object.values(allCheck).forEach((option) =>{
            if(!element.get('value').includes(option.value)){
                option.disabled = true;
            }
        });
    }
    else {
        Object.values(allCheck).forEach((option) =>{
            option.disabled = false;
        });
    }
}

/**
 * Uppercase the first letter of a value (working on keyup event)
 *
 * Examples :
 * firstLetterToUppercase(fab.get('jos_emundus_1001_00___name'));
 *
 * @param element
 */
function firstLetterToUppercase(element) {
    if (element.get('value').length > 1) {
        element.set(element.get('value')[0].toUpperCase() + element.get('value').substring(1));
    }
}

function numberOfDaysBetweenDates(date1,date2 = null) {
    if(date2 === null) {
        date2 = new Date();
    }

    const diffTime = Math.abs(date2 - date1);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

function submit(title = 'Dossier en cours d\'envoi...',timer = 3000) {
    let fabrikForm = document.querySelector('form.fabrikForm');
    if(fabrikForm)
    {
        fabrikForm.style.opacity = 0;
    }
    let fabrikHeader = document.querySelector('.page-header');
    if(fabrikHeader)
    {
        fabrikHeader.style.opacity = 0;
    }

    let emundusForm = document.querySelector('.emundus-form');
    if(emundusForm)
    {
        emundusForm.classList.add('skeleton');
    }

    Swal.fire({
        type: 'success',
        position: 'center',
        title: title,
        showCancelButton: false,
        showConfirmButton: false,
        customClass: {
            title: 'em-swal-title',
        },
        timer: timer
    }).then(() => {
        if(fabrikForm)
        {
            fabrikForm.submit();
        }
    });
}
function purcentage(elements){

    const value = elements.get('value');

    if (typeof value === "number") {
        if (value < 0) {
            elements.set("");
        } else if (value > 100) {
            elements.set("100");
        } else {
            elements.set(value.toString());
        }
    } else if (typeof value === "string") {
        const numericValue = parseFloat(value);

        if (!isNaN(numericValue)) {
            if (numericValue < 0) {
                elements.set("");
            } else if (numericValue > 100) {
                elements.set("100");
            } else {
                elements.set(numericValue.toString());
            }
        } else {
            elements.set("");
        }
    }
}

/**
 * Check if the user is older than the minAge or younger than the maxAge
 * @param element
 * @param minAge
 * @param maxAge
 * @returns {Date}
 */
function birthDateValidation(element, minAge = 0, maxAge = 0, minMessage = 'Vous devez être plus agé que %s ans', maxMessage = 'Vous devez être plus jeune que %s ans') {
    const errorElement = document.querySelector('.fb_el_'+element.baseElementId + ' .fabrikErrorMessage');
    if(errorElement) {
        errorElement.innerHTML = '';
    }

    let error = '';
    const value = element.get('value');

    let regex = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/;
    const regexTest = regex.test(value);
    const userBirthDate = new Date(value.replace(regex, "$3-$2-$1"));
    let todayYear = (new Date()).getFullYear();

    if (!regexTest || isNaN(userBirthDate))
    {
        error = 'Veuillez saisir une date de naissance valide';
    }
    else if(minAge !== 0)
    {
        let cutOffMin = new Date();
        cutOffMin.setFullYear(todayYear - minAge);

        if (userBirthDate > cutOffMin) {
            error = minMessage.replace('%s', minAge.toString());
        }
    }
    else if(maxAge !== 0)
    {
        let cutOffMax = new Date();
        cutOffMax.setFullYear(todayYear - maxAge);

        if (userBirthDate < cutOffMax) {
            error = maxMessage.replace('%s', maxAge.toString());
        }
    }

    if(error !== '')
    {
        if(errorElement) {
            errorElement.innerHTML = error;
        }
    }

    return userBirthDate;
}

/**
 * Function to display a modal with a message in form 102
 */
function submitNewFile() {
    let campaign = document.getElementById('jos_emundus_campaign_candidature___campaign_id');
    for (let i = 0; i < campaign.length; i++){
        if (campaign.options[i].value == -1) {
            campaign.options[i].disabled = true;
            campaign.options[i].style.backgroundColor = "#efefef";
            campaign.options[i].style.fontStyle = "italic";
        }
    }

    var cid = document.querySelector('#jos_emundus_campaign_candidature___campaign_id option:checked').value;
    if(cid !== "") {
        document.querySelector('#form_102').style.visibility = 'hidden';
        Swal.fire({
                title: Joomla.JText._('COM_EMUNDUS_FABRIK_NEW_FILE'),
                text: Joomla.JText._('COM_EMUNDUS_FABRIK_NEW_FILE_DESC'),
                type: 'success',
                showConfirmButton: false
            }
        );
        document.querySelector('#form_102').submit();
    }
}

function checkPasswordSymbols(element) {
    var wrong_password_title = ['Invalid password', 'Mot de passe invalide'];
    var wrong_password_description = ['The #$\{\};<> characters are forbidden, as are spaces.', 'Les caractères #$\{\};<> sont interdits ainsi que les espaces'];

    var site_url = window.location.toString();
    var site_url_lang_regexp = /\w+.\/en/d;

    var index = 0;

    if(site_url.match(site_url_lang_regexp) === null) { index = 1; }

    var regex = /[#$\{\};<> ]/;
    var password_value = element.get('value');

    if (password_value.match(regex) != null) {
        Swal.fire({
            type: 'error',
            title: wrong_password_title[index],
            text: wrong_password_description[index],
            reverseButtons: true,
            customClass: {
                title: 'em-swal-title',
                confirmButton: 'em-swal-confirm-button',
                actions: 'em-swal-single-action',
            }
        });

        element.set('');
    }
}

function cleanNumberInput(element, maxDecimals) {
    var value = element.get('value');
    const input = document.getElementById(element.strElement);

    const nonDigitExceptCommaDot = /[^0-9.,]/;

    const moreThanOneCommaDot = /[.,].*[.,]/;

    const caretPosition = input.selectionStart;
    const lastInsertedCharacter = value.charAt(caretPosition - 1);

    if (nonDigitExceptCommaDot.test(value)) {
        value = value.replace(nonDigitExceptCommaDot, '');
    }

    // If the last inserted character is a comma, replace it with a dot
    if (lastInsertedCharacter === ',') {
        value = value.substring(0, caretPosition - 1) + '.' + value.substring(caretPosition);
    }

    if (moreThanOneCommaDot.test(value)) {
        value = value.substring(0, caretPosition - 1) + value.substring(caretPosition);
    }

    const dotOrCommaIndex = value.indexOf(".") !== -1 ? value.indexOf(".") : value.indexOf(",");
    if (dotOrCommaIndex !== -1) {
        const digitsAfterDotOrComma = value.substring(dotOrCommaIndex + 1).length;
        if (digitsAfterDotOrComma > maxDecimals) {
            value = value.substring(0, caretPosition - 1) + value.substring(caretPosition);
        }
    }

    if (maxDecimals === 0 && value.indexOf(".") !== -1) {
        value = value.replace(".", "");
    }

    return value;
}
