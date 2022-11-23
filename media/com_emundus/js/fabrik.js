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

    elements.forEach((element) => {
        if (element) {
            if (clearElements) element.clear();
            element.hide();
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

    elements.forEach((element) => {
        if (element) element.show();
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

    elements.forEach((element) => {
        if (element) {
            document.getElementById(`group${element.groupid}`).classList.add('fabrikHide');

            if (clearElements) {

                if(form === null){
                    form = Fabrik.getBlock(element.form.block);
                }

                if(form) {
                    Object.values(form.elements).map((all_element) => {
                        if (all_element.groupid === element.groupid) all_element.clear();
                    });
                }
            }
        } else {
            console.log('hideFabrikGroupByElt: An element is undefined');
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

    elements.forEach((element) => {
        if (element) {
            document.getElementById(`group${element.groupid}`).classList.remove('fabrikHide');
        } else {
            console.log('showFabrikGroupByElt: An element is undefined');
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

    groups.forEach((group) => {
        let selector = document.getElementById(`group${group}`);
        if (selector) selector.classList.add('fabrikHide');

        if (clearElements) {
            let formDiv = document.querySelector(`.fabrikForm`).getAttribute('name');
            let form = Fabrik.getBlock(formDiv);

            Object.values(form.elements).map((element) => {
                if (element.groupid == group) element.clear();
            });
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

    groups.forEach((group) => {
        if (group) document.getElementById(`group${group}`).classList.remove('fabrikHide');
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
        })
    }
    else {
        Object.values(allCheck).forEach((option) =>{
            option.disabled = false;
        })
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

/**
 * Show/Hide an element depends on a value of checkbox
 *
 * Examples :
 * displayElementFromCheckboxValue(fab.get('jos_emundus_1001_00___grade'),'other',fab.get('jos_emundus_1001_00___other'));
 *
 * @param element
 * @param value
 * @param element_to_display
 * @param action
 */
function displayElementFromCheckboxValue(element, value, element_to_display, action = 'show') {
    //TODO : create the method
}
