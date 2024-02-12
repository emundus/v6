// File#: _1_color-swatches
// Usage: codyhouse.co/license
class ColorSwatches {

    constructor(element)
    {
        this.element = element;
        this.select = false;
        this.initCustomSelect(this); // replace <select> with custom <ul> list
        this.list = this.element.getElementsByClassName('js-color-swatches__list')[0];
        this.swatches = this.list.getElementsByClassName('js-color-swatches__option');
        this.labels = this.list.getElementsByClassName('js-color-swatch__label');
        this.selectedLabel = this.element.getElementsByClassName('js-color-swatches__color');
        this.focusOutId = false;
        this.initColorSwatches(this);
    }

    initCustomSelect(element) {
        var select = element.element.getElementsByClassName('js-color-swatches__select');
        if(select.length == 0) return;
        element.select = select[0];
        var customContent = '';
        for(var i = 0; i < element.select.options.length; i++) {
            var ariaChecked = i == element.select.selectedIndex ? 'true' : 'false',
                customClass = i == element.select.selectedIndex ? ' color-swatches__item--selected' : '',
                customAttributes = this.getSwatchCustomAttr(element.select.options[i]);
            customContent = customContent + '<li class="color-swatches__item js-color-swatches__item'+customClass+'" role="radio" aria-checked="'+ariaChecked+'" data-value="'+element.select.options[i].value+'"><span class="js-color-swatches__option js-tab-focus" tabindex="0"'+customAttributes+'><span class="sr-only js-color-swatch__label">'+element.select.options[i].text+'</span><span aria-hidden="true" style="'+element.select.options[i].getAttribute('data-style')+'" class="color-swatches__swatch"></span></span></li>';
        }

        var list = document.createElement("ul");
        list.setAttribute('class', 'color-swatches__list js-color-swatches__list');
        list.setAttribute('role', 'radiogroup');

        list.innerHTML = customContent;
        element.element.insertBefore(list, element.select);
        element.select.classList.add('hidden');
    }

    initColorSwatches(element) {
        // detect focusin/focusout event - update selected color label
        element.list.addEventListener('focusin', () => {
            if(element.focusOutId) clearTimeout(element.focusOutId);
            this.updateSelectedLabel(element, document.activeElement);
        });
        element.list.addEventListener('focusout', () =>{
            element.focusOutId = setTimeout(() => {
                this.resetSelectedLabel(element);
            }, 200);
        });

        // mouse move events
        for(var i = 0; i < element.swatches.length; i++) {
            this.handleHoverEvents(element, i);
        }

        // --select variation only
        if(element.select) {
            // click event - select new option
            element.list.addEventListener('click', (event) => {
                // update selected option
                this.resetSelectedOption(element, event.target);
            });

            // space key - select new option
            element.list.addEventListener('keydown', (event) => {
                if( (event.keyCode && event.keyCode == 32 || event.key && event.key == ' ') || (event.keyCode && event.keyCode == 13 || event.key && event.key.toLowerCase() == 'enter')) {
                    // update selected option
                    this.resetSelectedOption(element, event.target);
                }
            });
        }
    }

    handleHoverEvents(element, index) {
        element.swatches[index].addEventListener('mouseenter', (event) => {
            this.updateSelectedLabel(element, element.swatches[index]);
        });
        element.swatches[index].addEventListener('mouseleave', () => {
            this.resetSelectedLabel(element);
        });
    }

    resetSelectedOption(element, target) { // for --select variation only - new option selected
        var option = target.closest('.js-color-swatches__item');
        if(!option) return;
        var selectedSwatch =  element.list.querySelector('.color-swatches__item--selected');
        if(selectedSwatch) {
            selectedSwatch.classList.remove('color-swatches__item--selected');
            selectedSwatch.setAttribute('aria-checked', 'false');
        }
        option.classList.add('color-swatches__item--selected');
        option.setAttribute('aria-checked', 'true');
        // update select element
        this.updateNativeSelect(element.select, option.getAttribute('data-value'));
    }

    resetSelectedLabel(element) {
        var selectedSwatch =  element.list.getElementsByClassName('color-swatches__item--selected');
        if(selectedSwatch.length > 0 ) this.updateSelectedLabel(element, selectedSwatch[0]);
    }

    updateSelectedLabel(element, swatch) {
        var newLabel = swatch.getElementsByClassName('js-color-swatch__label');
        if(newLabel.length == 0 ) return;
        element.selectedLabel[0].textContent = newLabel[0].textContent;
    }

    updateNativeSelect(select, value) {
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].value == value) {
                select.selectedIndex = i; // set new value
                select.dispatchEvent(new CustomEvent('change')); // trigger change event
                break;
            }
        }
    }

    getSwatchCustomAttr(swatch) {
        var customAttrArray = swatch.getAttribute('data-custom-attr');
        if(!customAttrArray) return '';
        var customAttr = ' ',
            list = customAttrArray.split(',');
        for(var i = 0; i < list.length; i++) {
            var attr = list[i].split(':')
            customAttr = customAttr + attr[0].trim()+'="'+attr[1].trim()+'" ';
        }
        return customAttr;
    }
}