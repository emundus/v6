<template>
    <div id="filter-builder">
        <div class="filters">
            <div class="filter" v-for="(filter, index) in filters" :key="index">
                
            </div>
        </div>
        <div class="actions">
            <button class="btn btn-primary" @click="addFilter">
                <i class="fa fa-plus"></i>
                Ajouter un filtre
            </button>
        </div>
    </div>
</template>

<script>

export default {
    name: 'FilterBuilder',
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
            return [
                {
                    type: 'text',
                    label: 'Nom',
                    value: '',
                },
            ];
        },
        addFilter() {
            this.filters.push({
                type: '',
                value: '',
            });
        },
    },
}
</script>