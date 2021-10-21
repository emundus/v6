<template>
    <div id="filter-builder">
        <div class="filters">
            <div class="filter" :v-for="filter in filters">
                
            </div>
        </div>
        <div class="actions">
            <button class="btn btn-primary" @click="addFilter">
                <i class="fa fa-plus"></i>
                {{ __('Add filter') }}
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
            return this.$store.state.filters;
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