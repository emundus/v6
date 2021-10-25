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
import filterService from '../../services/filter';
import FilterRow from './FilterRow.vue';

export default {
    name: 'FilterBuilder',
    components: {
        FilterRow
    },
    props: {
        type: {
            type: String,
            default: "list"
        },
        id: {
            type: String,
            default: "70"
        },
    },
    data() {
        return {
            filters: [],
        };
    },
    mounted() {
        this.getFilters();
    },
    methods: {
        async getFilters() {
            const response = await filterService.getFilters(this.type, this.id);
            
            if (response.status == true) {
                this.$store.dispatch('setFilters', response.filters);
            }
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