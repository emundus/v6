<template>
    <div class="filter-row">
        <label for="filter-name">Filtres</label>
        <select name="filter-name" @change="changeFilter">
            
        </select>
        
        <label for="filter-action">Actions</label>
        <select name="filter-action">
            
        </select>

        <label for="filter-value">Valeurs</label>
        <select name="filter-value">

        </select>
    </div>
</template>

<script>
export default {
    data() {
        return {
            names: [],
            actions: [],
            values: []
        };
    },
    methods: {
        changeFilter() {
            // update the actions and the values based on name selected

        }
    }
}
</script>