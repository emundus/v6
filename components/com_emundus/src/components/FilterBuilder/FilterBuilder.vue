<template>
    <div id="filter-builder">
        <h2>Filtres</h2>
        <div class="filters">
            <div class="rows">
                <FilterRow class="filter" v-for="(filter, index) in filters" :key="index"></FilterRow>
            </div>
            <div class="actions">
                <button class="btn btn-primary" @click="addFilter">
                    <i class="fa fa-plus"></i>
                    Ajouter un filtre
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import FilterRow from './FilterRow.vue';

export default {
    name: 'FilterBuilder',
    components: {
        FilterRow
    },
    data() {
        return {
            filters: [],
        };
    },
    mounted() {
        this.filters = this.getFilters();
    },
    methods: {
        getFilters() {
            return [{
                'name': "",
                'operator': "",
                'value': ""
            }];
        },
        addFilter() {
            this.filters.push({
                'name': "",
                'operator': "",
                'value': ""
            });
        },
    },
}
</script>