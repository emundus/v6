<template>
    <div class="filter-row">
        <label for="filter-name">Filtres</label>
        <select name="filter-name" @change="changeFilter">
            <option v-for="name in names" :key="name.id" :value="name.id">{{name.label}}</option>
        </select>
        
        <label for="filter-action">Actions</label>
        <select name="filter-action">
            <option v-for="(action, key) in actions" :key="key" :value="key">{{action}}</option>
        </select>

        <label for="filter-value">Valeurs</label>
        <select name="filter-value">
            <option v-for="(value, key) in values" :key="key" :value="key">{{value}}</option>
        </select>
    </div>
</template>

<script>
export default {
    data() {
        return {
            selectedFilter: null,
            names: [],
        };
    },
    mounted() {
        this.names = this.$store.state.filters.map((filter, index) => {
            if (index == 0) {
                this.selectedFilter = filter.id;
            }

            return {
                id: filter.id,
                label: filter.label
            };
        });
    },
    methods: {
        changeFilter(event) {
            // update the actions and the values based on name selected
            this.selectedFilter = event.target.value;
        }
    },
    computed: {
        actions() {
            const filter = this.$store.state.filters.find(filter => filter.id == this.selectedFilter);
            return filter ? filter.actions : [];
        },
        values() {
            const filter = this.$store.state.filters.find(filter => filter.id == this.selectedFilter);
            return filter ? filter.values : [];
        }
    }
}
</script>