var filtersInstances = [];
const filterSampleContainerHTML = '<div class="filter-container em-w-100" id="sample-id" style="position: relative;">' +
    '<section class="filter-recap-container em-pointer em-border-radius-8 em-border-neutral-400 em-flex-row em-flex-space-between em-box-shadow em-white-bg">' +
    '   <div class="filter-recap em-p-8 em-flex-col-start">' +
    '       <div class="operator em-mt-8 em-ml-8 em-p-8-0"></div>' +
    '       <div class="options em-flex-row em-flex-wrap em-ml-8 em-p-8-0"></div>' +
    '       <p class="filter-empty-selection hidden"></p>' +
    '   </div>' +
    '   <span class="material-icons-outlined expand">expand_more</span>' +
    '</section>' +
    '<section class="filter-options-modal hidden em-w-100 em-white-bg em-border-radius-8 em-box-shadow" style="position: absolute;z-index:1;">' +
    '   <div class="filter-operators em-flex-col-start em-p-16"></div>' +
    '   <div class="filter-andor-operators em-flex-col-start em-p-16"></div>' +
    '   <hr>' +
    '   <ul class="filter-options em-m-16"></ul>' +
    '</section>' +
    '</div>';
let filterSampleContainer = document.createElement('div');
filterSampleContainer.innerHTML = filterSampleContainerHTML;

var basicOperators = [
    {value: '=', label: translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS')},
    {value: '!=', label: translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT')},
    {value: 'LIKE', label: translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_CONTAINS')},
    {value: 'NOT LIKE', label: translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_DOES_NOT_CONTAIN')},
    {value: 'IN', label: translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_ONE_OF')},
    {value: 'NOT IN', label: translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT_ONE_OF')},
];
var andOrOperators = [
    {value: 'AND', label: translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_AND')},
    {value: 'OR', label: translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_OR')},
];

function translate(str) {
    return Joomla.JText._(str);
}

class MultiSelectFilter {
    uid = null;
    id = null;
    options = [];
    modal = null;
    operators = basicOperators;
    andOrOperators = andOrOperators;
    selectedValues = [];
    selectedOperator = '=';
    selectedAndOrOperator = 'OR';

    constructor(filterContainer) {
        const select = filterContainer.querySelector('select');

        const defaultOperator = select.getAttribute('data-default-operator');
        if (defaultOperator !== null && defaultOperator !== undefined && defaultOperator !== '') {
            let found = false;
            basicOperators.forEach((operator) => {
                if (operator.value === defaultOperator) {
                    found = true;
                }
            });

            if (found) {
                this.selectedOperator = defaultOperator;
            }
        }

        const defaultAndorOperator = select.getAttribute('data-default-andor');
        if (defaultAndorOperator !== null && defaultAndorOperator !== undefined && defaultAndorOperator !== '') {
            if (defaultAndorOperator === 'AND' || defaultAndorOperator === 'OR') {
                this.selectedAndOrOperator = defaultAndorOperator;
            }
        }

        this.uid = filterContainer.dataset.filteruid;
        this.id = select.getAttribute('id');

        select.querySelectorAll('option').forEach((option) => {
            this.options.push({value: option.value, label: option.innerText});
        });

        select.multiple = true;
        select.classList.add('hidden');
        select.querySelectorAll('option').forEach((option) => {
            if (option.selected) {
                this.selectedValues.push(option.value);
            }
        });

        const filterSampleContainerCopy = filterSampleContainer.cloneNode(true);
        const filterOptions = filterSampleContainerCopy.querySelector('.filter-options');
        this.options.forEach((option) => {
            let optionInput = document.createElement('input');
            optionInput.type = 'checkbox';
            optionInput.value = option.value;
            optionInput.id = 'filter-' + this.id + '-' + option.value;
            optionInput.name = 'filter[' + this.id + '][]';
            if (this.selectedValues.includes(option.value) || (this.selectedValues.length < 1 && option.value === 'all')) {
                optionInput.checked = true;
            }
            optionInput.addEventListener('change', (e) => {
                this.onCheckboxChange(e);
            });

            let optionLabel = document.createElement('label');
            optionLabel.innerText = option.label;
            optionLabel.setAttribute('for', 'filter-' + this.id + '-' + option.value);

            let optionContainer = document.createElement('li');
            optionContainer.classList.add('filter-option');
            optionContainer.classList.add('em-flex-row');
            optionContainer.classList.add('em-mb-8');
            optionContainer.appendChild(optionInput);
            optionContainer.appendChild(optionLabel);

            filterOptions.appendChild(optionContainer);
        });

        const filterOperators = filterSampleContainerCopy.querySelector('.filter-operators');
        this.operators.forEach((operator) => {
            let operatorSpan = document.createElement('span');
            operatorSpan.innerText = operator.label;
            operatorSpan.dataset.operator = operator.value;
            operatorSpan.classList.add('filter-operator');
            operatorSpan.classList.add('em-mb-8');
            operatorSpan.classList.add('label');

            if (operator.value === this.selectedOperator) {
                operatorSpan.classList.add('label-default');
            } else {
                operatorSpan.classList.add('label-darkblue');
                operatorSpan.classList.add('em-pointer');
            }

            operatorSpan.addEventListener('click', (e) => {
                this.onChangeOperator(e);
            });

            filterOperators.appendChild(operatorSpan);
        });

        const filterAndOrOperators = filterSampleContainerCopy.querySelector('.filter-andor-operators');
        this.andOrOperators.forEach((operator) => {
            let operatorSpan = document.createElement('span');
            operatorSpan.innerText = operator.label;
            operatorSpan.dataset.operator = operator.value;
            operatorSpan.classList.add('filter-and-or-operator');
            operatorSpan.classList.add('em-mb-8');
            operatorSpan.classList.add('label');

            if (operator.value === this.selectedAndOrOperator) {
                operatorSpan.classList.add('label-default');
            } else {
                operatorSpan.classList.add('em-pointer');
                operatorSpan.classList.add('label-darkblue');
            }

            operatorSpan.addEventListener('click', (e) => {
                this.onChangeAndOrOperator(e);
            });

            filterAndOrOperators.appendChild(operatorSpan);
        });

        filterContainer.appendChild(filterSampleContainerCopy);

        this.recap = filterContainer.querySelector('.filter-recap-container');
        this.modal = filterContainer.querySelector('.filter-options-modal');

        this.addEventListeners();
        this.updateRecap();
    }

    addEventListeners() {
        this.recap.addEventListener('click', () => {
            if (this.modal.classList.contains('hidden')) {
                this.openModal();
            } else {
                this.closeModal();
            }
        });
    }

    onChangeOperator(e) {
        if (this.selectedOperator !== e.target.dataset.operator) {
            this.selectedOperator = e.target.dataset.operator;

            this.operators.forEach((operator) => {
                const operatorSpan = document.querySelector('select#' + this.id + ' +div .filter-operator[data-operator="' + operator.value + '"]');
                if (operator.value !== this.selectedOperator) {
                    operatorSpan.classList.remove('label-default');
                    operatorSpan.classList.add('label-darkblue');
                    operatorSpan.classList.add('em-pointer');
                } else {
                    operatorSpan.classList.remove('em-pointer');
                    operatorSpan.classList.remove('label-darkblue');
                    operatorSpan.classList.add('label-default');
                }
            });

            this.updateRecap();
        }
    }

    onChangeAndOrOperator(e) {
        if (this.selectedAndOrOperator !== e.target.dataset.operator) {
            this.selectedAndOrOperator = e.target.dataset.operator;

            this.andOrOperators.forEach((operator) => {
                const operatorSpan = document.querySelector('select#' + this.id + ' +div .filter-and-or-operator[data-operator="' + operator.value + '"]');
                if (operator.value !== this.selectedAndOrOperator) {
                    operatorSpan.classList.remove('label-default');
                    operatorSpan.classList.add('label-darkblue');
                    operatorSpan.classList.add('em-pointer');
                } else {
                    operatorSpan.classList.remove('em-pointer');
                    operatorSpan.classList.remove('label-darkblue');
                    operatorSpan.classList.add('label-default');
                }
            });

            this.updateRecap();
        }
    }

    onCheckboxChange(e) {
        if (e.target.checked) {
            this.selectedValues.push(e.target.value);
        } else {
            this.selectedValues = this.selectedValues.filter((value) => {
                return value !== e.target.value;
            });
        }

        this.updateRecap();
    }

    updateRecap() {
        const recap = this.recap.querySelector('.filter-recap');
        const recapOperators = recap.querySelector('.operator');
        const emptyWrapper = recap.querySelector('p.filter-empty-selection');
        const recapOptions = recap.querySelector('.options');
        emptyWrapper.classList.add('hidden');

        const oldElements = recap.querySelectorAll('.filter-recap-element');
        if (oldElements.length > 0) {
            oldElements.forEach((element) => {
                element.remove();
            });
        }

        let valuesCount = this.selectedValues.length;
        if (valuesCount > 0) {
            // first element is the operator
            let recapElement = document.createElement('span');
            recapElement.classList.add('filter-recap-element');
            recapElement.classList.add('label');
            recapElement.classList.add('label-darkblue');
            recapElement.classList.add('em-mr-8');
            recapElement.innerText = this.operators.find((operator) => {
                return operator.value === this.selectedOperator;
            }).label;
            recapOperators.appendChild(recapElement);
            recapOperators.classList.remove('hidden');

            this.selectedValues.forEach((value, index) => {
                recapElement = document.createElement('span');
                recapElement.classList.add('filter-recap-element');
                recapElement.classList.add('label');
                recapElement.classList.add('label-default');
                recapElement.classList.add('em-mr-8');

                const valueLabel = this.options.find((option) => {
                    return option.value === value;
                }).label;
                recapElement.innerText = valueLabel ? valueLabel : value;
                recapOptions.appendChild(recapElement);

                if (index < valuesCount - 1) {
                    recapElement = document.createElement('span');
                    recapElement.classList.add('filter-recap-element');
                    recapElement.classList.add('label');
                    recapElement.classList.add('label-darkblue');
                    recapElement.classList.add('em-mr-8');
                    recapElement.innerText = this.andOrOperators.find((operator) => {
                        return operator.value === this.selectedAndOrOperator;
                    }).label;
                    recapOptions.appendChild(recapElement);
                }
            });
            recapOptions.classList.remove('hidden');
        } else {
            emptyWrapper.innerText = translate('MOD_EMUNDUS_FILTERS_PLEASE_SELECT');
            recapOptions.classList.add('hidden');
            recapOperators.classList.add('hidden');
            emptyWrapper.classList.remove('hidden');
        }
    }

    openModal() {
        document.querySelectorAll('.filter-options-modal').forEach((modal) => {
            modal.classList.add('hidden');
        });

        this.modal.style.top = (this.recap.getHeight() + 16) + 'px';
        this.modal.classList.remove('hidden');
    }

    closeModal() {
        this.modal.classList.add('hidden');
    }
}

const appliedFiltersSection = document.getElementById('applied-filters');
const filtersSelectWrapper = document.querySelector('#filters-selection-wrapper');
const filtersSelect = document.getElementById('filters-selection');

function initFilters() {
    appliedFiltersSection.querySelectorAll('.filter-container').forEach(function (filterContainer) {
        // check type of filter
        let select = filterContainer.querySelector('select');
        if (select) {
            const filterInstance = new MultiSelectFilter(filterContainer);
            filtersInstances.push(filterInstance);
        } else {
            // can be an input text or an input date
            let input = filterContainer.querySelector('input[type="text"]');
            if (input) {

            } else {

            }
        }
    });
}

function toggleFilterSelect() {
    if (filtersSelectWrapper.classList.contains('hidden')) {
        filtersSelectWrapper.classList.remove('hidden');
    } else {
        filtersSelectWrapper.classList.add('hidden');
    }
}

function addFilter(e) {
    toggleFilterSelect(e);
}

function createFilter(filter) {
    const filterContainer = document.createElement('div');
    filterContainer.classList.add('filter-container');
    filterContainer.classList.add('em-w-100');
    filterContainer.classList.add('em-mb-16');
    filterContainer.dataset.filteruid = filter.uid;

    const filterHeader = document.createElement('div');
    filterHeader.classList.add('filter-header');
    filterHeader.classList.add('em-w-100');
    filterHeader.classList.add('em-flex-row');
    filterHeader.classList.add('em-flex-space-between');
    filterHeader.classList.add('em-mb-8');

    const filterLabel = document.createElement('label');
    filterLabel.classList.add('filter-label');
    filterLabel.classList.add('em-w-100');
    filterLabel.style.margin = '0';
    filterLabel.for = 'filter-' + filter.id;
    filterLabel.innerHTML = filter.label;
    filterHeader.appendChild(filterLabel);

    const filterRm = document.createElement('span');
    filterRm.classList.add('material-icons-outlined');
    filterRm.classList.add('remove-filter');
    filterRm.classList.add('em-pointer');
    filterRm.innerHTML = 'delete';
    filterRm.dataset.filteruid = filter.uid;

    filterHeader.appendChild(filterRm);
    filterContainer.appendChild(filterHeader);

    switch (filter.type) {
        case 'select':
            const filterSelect = document.createElement('select');
            filterSelect.classList.add('filter-select');
            filterSelect.classList.add('em-w-100');
            filterSelect.name = 'filter[' + filter.id + ']';
            filterSelect.id = 'filter-' + filter.id;
            filterSelect.dataset.filteruid = filter.uid;

            filter.values.forEach(function (value) {
                const option = document.createElement('option');
                option.value = value.value;
                option.text = value.label;
                filterSelect.appendChild(option);
            });

            filterContainer.appendChild(filterSelect);
            const filterInstance = new MultiSelectFilter(filterContainer);
            filtersInstances.push(filterInstance);

            break;
        case 'date':
            const filterDate = document.createElement('input');
            filterDate.classList.add('filter-date');
            filterDate.classList.add('em-w-100');
            filterDate.type = 'date';
            filterDate.name = 'filter[' + filter.id + ']';
            filterDate.id = 'filter-' + filter.id;
            filterDate.dataset.filteruid = filter.uid;
            filterContainer.appendChild(filterDate);
            filtersInstances.push(filter);
            break;
        case 'text':
        default:
            const filterInput = document.createElement('input');
            filterInput.classList.add('filter-input');
            filterInput.classList.add('em-w-100');
            filterInput.type = 'text';
            filterInput.name = 'filter[' + filter.id + ']';
            filterInput.id = 'filter-' + filter.id;
            filterInput.dataset.filteruid = filter.uid;
            filterContainer.appendChild(filterInput);
            filtersInstances.push(filter);
            break;
    }

    appliedFiltersSection.appendChild(filterContainer);
}

function removeFilter(filteruid) {
    if (filteruid !== undefined && filteruid !== null) {
        filtersInstances = filtersInstances.filter((filter) => {
            return filter.uid !== filteruid;
        });

        document.querySelector('.filter-container[data-filteruid="' + filteruid + '"]').remove();
    }
}

function applyFilters() {
    const filters = filtersInstances.map((filter) => {
        return {
            id: filter.id,
            uid: filter.uid,
            value: filter.selectedValues,
            operator: filter.selectedOperator,
            andorOperator: filter.selectedAndOrOperator,
        };
    });

    let formData = new FormData();
    formData.append('filters', JSON.stringify(filters));

    fetch('/index.php?option=com_emundus&controller=files&task=applyfilters', {
        method: 'POST',
        body: formData
    }).then(function (response) {
        if (response.ok) {
            return response.json();
        } else {
            throw new Error('Something went wrong');
        }
    }).then(function (data) {
        if (data.status) {
            window.location.reload();
        } else {
            throw new Error('Something went wrong');
        }
    }).catch(function (error) {
        console.log(error);
    });
}

initFilters();

document.getElementById('mod_emundus_filters').addEventListener('click', function (e) {
    if (e.target.id === 'add-filter') {
        addFilter(e);
    } else if (e.target.id === 'apply-filters') {
        applyFilters();
    } else if (e.target.matches('.remove-filter')) {
        removeFilter(e.target.dataset.filteruid);
    }
});

document.querySelector('#reset-all-filters').addEventListener('click', function (e) {
    filtersInstances = [];
    applyFilters();
});

if (filtersSelect) {
    filtersSelect.addEventListener('change', function (e) {
        if (e.target.value !== '0') {
            toggleFilterSelect(e);
            const selectedOption = e.target.options[e.target.selectedIndex];
            const filter = {
                type: selectedOption.dataset.type,
                id: e.target.value,
                uid: Date.now(),
                label: selectedOption.text,
                values: JSON.parse(atob(selectedOption.dataset.values)),
                value: ''
            };

            createFilter(filter);
            e.target.value = '0';
        }
    });
}