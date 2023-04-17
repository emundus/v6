const appliedFiltersSection = document.getElementById('applied-filters');
const filtersSelectWrapper = document.querySelector('#filters-selection-wrapper');
const filtersSelect = document.getElementById('filters-selection');
let filters = [];

function initFilters(){
    if (filters.length > 0) {
        applyFilters();
    }
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
    filterLabel.innerHTML = filter.name;
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

            filterContainer.appendChild(filterSelect);
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
                name: selectedOption.text,
                values: JSON.parse(atob(selectedOption.dataset.values)),
                value: ''
            };

            filters.push(filter);
            createFilter(filter);
            e.target.value = '0';
        }
    });
}