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
                <th><input type="checkbox" class="em-switch input"></th>
                <th v-for="data in listColumns" :id="data.column_name" :key="data.id" >{{translate(data.label)}}</th>

            </tr>
            </thead>
            <tbody>
            <Row
                v-for="data in listData"
                :key="data.id"
                :rowData="data"
                :listColumns="listColumns"


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
        listData : [],

    }),
    created() {
        this.retriveListData();
    },
    methods: {
        async retriveListData() {
            console.log('******$')
            try {
                const response = await ListService.getListAndDataContains();
                console.log(response);
                this.listColumns = response.data.listColumns;
                this.listData = response.data.listData;
            } catch (e) {
                 console.log(e);
            }


        }
    }
}
</script>

<style scoped>

</style>
