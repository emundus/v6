var filterSampleContainer = null;

class MultiSelectFilter {
    filterUid = null;
    filterId = null;
    options = [];
    modal = null;
    operators = [
        { value: '=', label: 'is'},
        { value: '!=', label: 'is not'},
        { value: 'LIKE', label: 'contains'},
        { value: 'NOT LIKE', label: 'does not contain'}
    ];
    andOrOperators = [
        { value: 'AND', label: 'and'},
        { value: 'OR', label: 'or'}
    ];

    selectedValues = [];
    selectedOperator = '=';
    selectedAndOrOperator = 'AND';

    constructor(filterContainer) {
        const select = filterContainer.querySelector('select');
        this.filterUid = Number(select.dataset.filterUid);
        this.filterId = select.id;

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
            optionInput.id = 'filter-' + this.filterId + '-' + option.value;
            optionInput.name = 'filter[' + this.filterId + '][]';
            if (this.selectedValues.includes(option.value) || option.value ==='all') {
                optionInput.checked = true;
            }
            optionInput.addEventListener('change', (e) => {
                this.onCheckboxChange(e);
            });

            let optionLabel = document.createElement('label');
            optionLabel.innerText = option.label;
            optionLabel.setAttribute('for', 'filter-' + this.filterId + '-' + option.value);

            let optionContainer = document.createElement('li');
            optionContainer.classList.add('filter-option');
            optionContainer.classList.add('em-flex-row');
            optionContainer.classList.add('em-mb-8');
            optionContainer.appendChild(optionInput);
            optionContainer.appendChild(optionLabel);

            filterOptions.appendChild(optionContainer);
        });

        filterContainer.appendChild(filterSampleContainerCopy);

        this.recap = filterContainer.querySelector('.filter-recap');
        this.modal = filterContainer.querySelector('.filter-options-modal');

        this.addEventListeners();
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

    onCheckboxChange(e) {
        if (e.target.checked) {
            this.selectedValues.push(e.target.value);
        } else {
            this.selectedValues = this.selectedValues.filter((value) => {
                return value !== e.target.value;
            });
        }

        console.log(this.selectedValues);

        this.updateRecap();
    }

    updateRecap() {

    }

    openModal() {
        document.querySelectorAll('.filter-options-modal').forEach((modal) => {
            modal.classList.add('hidden');
        });
        this.modal.classList.remove('hidden');
    }

    closeModal() {
        this.modal.classList.add('hidden');
    }
}

const appliedFiltersSection = document.getElementById('applied-filters');
const filtersSelectWrapper = document.querySelector('#filters-selection-wrapper');
const filtersSelect = document.getElementById('filters-selection');
let filters = [];

function initFilters(){
    appliedFiltersSection.querySelectorAll('.filter-container').forEach(function (filterContainer) {
        const filter = {
            type: filterContainer.dataset.type,
            id: filterContainer.dataset.id,
            uid: filterContainer.dataset.uid,
            label: filterContainer.dataset.name,
            values: filterContainer.dataset.values,
            value: 'all'
        };

        filters.push(filter);

        new MultiSelectFilter(filterContainer);
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
    filterRm.dataset.filterUid = filter.uid;

    filterHeader.appendChild(filterRm);

    filterContainer.appendChild(filterHeader);

    switch (filter.type) {
        case 'select':
            const filterSelect = document.createElement('select');
            filterSelect.classList.add('filter-select');
            filterSelect.classList.add('em-w-100');
            filterSelect.name = 'filter[' + filter.id + ']';
            filterSelect.id = 'filter-' + filter.id;

            filter.values.forEach(function (value) {
                const option = document.createElement('option');
                option.value = value.value;
                option.text = value.label;
                filterSelect.appendChild(option);
            });

            filterContainer.appendChild(filterSelect);
            new MultiSelectFilter(filterContainer);

            break;
        case 'date':
            const filterDate = document.createElement('input');
            filterDate.classList.add('filter-date');
            filterDate.classList.add('em-w-100');
            filterDate.type = 'date';
            filterDate.name = 'filter[' + filter.id + ']';
            filterDate.id = 'filter-' + filter.id;
            filterContainer.appendChild(filterDate);
            break;
        case 'text':
        default:
            const filterInput = document.createElement('input');
            filterInput.classList.add('filter-input');
            filterInput.classList.add('em-w-100');
            filterInput.type = 'text';
            filterInput.name = 'filter[' + filter.id + ']';
            filterInput.id = 'filter-' + filter.id;
            filterContainer.appendChild(filterInput);
            break;
    }

    appliedFiltersSection.appendChild(filterContainer);
}

function removeFilter(e) {
    const filterUid = Number(e.target.dataset.filterUid);
    filters = filters.filter((filter) => {
        return filter.uid !== filterUid;
    });

    const filterContainer = e.target.closest('.filter-container');
    if (filterContainer) {
        filterContainer.remove();
    }
}

function applyFilters() {
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

fetch('../../modules/mod_emundus_filters/tmpl/filter-sample.html')
    .then(response => response.text())
    .then(data => {
        filterSampleContainer = document.createElement('div');
        filterSampleContainer.innerHTML = data;
        initFilters();
    });

document.getElementById('mod_emundus_filters').addEventListener('click', function (e) {
    if (e.target.id === 'add-filter') {
        addFilter(e);
    } else if (e.target.id === 'apply-filters') {
        applyFilters();
    } else if (e.target.matches('.remove-filter')) {
        removeFilter(e);
    }
});

if (filtersSelect) {
    filtersSelect.addEventListener('change', function (e) {
        if (e.target.value !== '0') {
            toggleFilterSelect(e);
            const uid = Date.now();
            const selectedOption = e.target.options[e.target.selectedIndex];
            const filter = {
                type: selectedOption.dataset.type,
                id: e.target.value,
                uid: uid,
                label: selectedOption.text,
                values: JSON.parse(atob(selectedOption.dataset.values)),
                value: ''
            };

            filters.push(filter);
            createFilter(filter);
            e.target.value = '0';
        }
    });
}