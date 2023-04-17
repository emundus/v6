const appliedFiltersSection = document.getElementById('applied-filters');
const filterSelect = document.querySelector('#filters-selection');
let filters = [];

function initFilters(){
    if (filters.length > 0) {
        applyFilters();
    }
}

function toggleFilterSelect() {
    if (filterSelect.classList.contains('hidden')) {
        filterSelect.classList.remove('hidden');
    } else {
        filterSelect.classList.add('hidden');
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

    const filterLabel = document.createElement('label');
    filterLabel.classList.add('filter-label');
    filterLabel.classList.add('em-w-100');
    filterLabel.for = 'filter-' + filter.id;
    filterLabel.innerHTML = filter.name;


    const filterSelect = document.createElement('select');
    filterSelect.classList.add('filter-select');
    filterSelect.classList.add('em-w-100');
    filterSelect.name = 'filter[' + filter.id + ']';
    filterSelect.id = 'filter-' + filter.id;

    const option = document.createElement('option');
    option.value = '0';
    option.text = Joomla.JText._('MOD_EMUNDUS_FILTERS_SELECT_VALUE');
    filterSelect.appendChild(option);

    filter.values.forEach(function (value) {
        const option = document.createElement('option');
        option.value = value.value;
        option.text = value.label;
        filterSelect.appendChild(option);
    });
    filterSelect.value = '0';

    filterContainer.appendChild(filterLabel);
    filterContainer.appendChild(filterSelect);
    appliedFiltersSection.appendChild(filterContainer);
}

function removeFilter(e) {

}

function applyFilters() {

}

initFilters();

document.getElementById('mod_emundus_filters').addEventListener('click', function (e) {
    if (e.target.id === 'add-filter') {
        addFilter(e);
    } else if (e.target.matches('.remove-filter')) {
        removeFilter(e);
    } else if (e.target.matches('.apply-filters')) {
        applyFilters();
    }
});

document.getElementById('filters-selection').addEventListener('change', function (e) {
    if (e.target.value !== '0') {
        toggleFilterSelect(e);

        const selectedOption = e.target.options[e.target.selectedIndex];
        const filter = {
            id: e.target.value,
            name: selectedOption.text,
            values: JSON.parse(atob(selectedOption.dataset.values)),
            value: ''
        };

        filters.push(filter);
        createFilter(filter);
    }
});