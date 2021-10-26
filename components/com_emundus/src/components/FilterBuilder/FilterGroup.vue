<template>
    <div class="filters">
        <div class="rows">
            <div class="relation">
                <select name="and_or">
                    <option value="and">ET</option>
                    <option value="or">OU</option>
                </select>
            </div>
            <FilterRow :group="id" class="filter" v-for="(filter, index) in filters" :key="index" @removeFilter="removeFilter(index)"></FilterRow>
        </div>
        <div class="actions">
            <div class="btn-primary-vue" @click="addFilter">
                <span class="material-icons">
                    add
                </span>
                Ajouter un filtre
            </div>
        </div>
    </div>
</template>

<script>
import FilterRow from './FilterRow.vue';

export default {
    name: 'FilterGroup',
    props: {
        id: {
            type: Number,
            required: true
        },
    },
    components: {
        FilterRow
    },
    data() {
        return {
            filters: []
        }
    },
    methods: {
        addFilter() {
            this.filters.push(1);
        },
        removeFilter(index) {
            this.filters.splice(index, 1);
        }
    }
}
</script>