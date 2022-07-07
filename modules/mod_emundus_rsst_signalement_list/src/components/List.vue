<template>
    <div id="actions-list-table">

        <div class="em-flex-row-start em-flex-space-between em-w-auto em-mb-32">
            <filter-item></filter-item>
            <filter-item></filter-item>
            <filter-item></filter-item>
            <filter-item></filter-item>
            <filter-item></filter-item>
            <filter-item></filter-item>
        </div>

        <table :aria-describedby="'Table of actions lists '">
            <thead class="list-table-head">
            <tr>
                <th><!--<input type="checkbox" v-model="checkedRows.rows" class="em-switch input" :value="listData" > --></th>
                <th v-for="data in listColumns" :id="data.column_name" :key="data.id"
                    @click="orderBy(data.column_name)">
                    <span
                        v-if="sort.orderBy == data.column_name && sort.order == 'asc'"
                        class="material-icons"
                    >arrow_upward</span>
                    <span
                        v-if="sort.orderBy == data.column_name && sort.order == 'desc'"
                        class="material-icons"
                    >arrow_downward</span>

                    {{ translate(data.label) }}
                </th>

            </tr>
            </thead>
            <tbody>
            <Row
                v-for="data in listData"
                :key="data.id"
                :rowData="data"
                :listColumns="listColumns"
                :checkedRows = 'checkedRows'
            />
            </tbody>
        </table>
    </div>
</template>

<script>

import Row from './Row.vue';
import Filter from './Filter.vue';
import ListService from '../services/list';


export default {
    name: "List",
    components: {
        Row,
        'filter-item': Filter,
    },
    data: () => ({
        loading: true,
        listColumns: [],
        listData: [],
        sort: {
            last: "",
            order: "",
            orderBy: "",
        },
        checkedRows:{
            rows: []
        },

    }),
    created() {
        this.retriveListData();
    },
    methods: {
        async retriveListData() {

            try {
                const response = await ListService.getListAndDataContains();

                this.listColumns = response.data.listColumns;
                this.listData = response.data.listData;
            } catch (e) {
                console.log(e);
            }
        },
        orderBy(key) {

            if (this.sort.last == key) {
                this.sort.order = this.sort.order == "asc" ? "desc" : "asc";
                this.listData.reverse();
            } else {
                // sort in ascending order by key
                this.listColumns.sort((a, b) => {
                    if (a[key] < b[key]) {
                        return -1;
                    }
                    if (a[key] > b[key]) {
                        return 1;
                    }
                    return 0;
                });

                this.sort.order = "asc";
            }

            this.sort.orderBy = key;
            this.sort.last = key;
        },
    }
}
</script>

<style scoped>

</style>
