<template>
    <div id="actions-list-table">

        <div class="em-flex-row-start em-flex-space-between em-w-auto em-mb-32">
            <template v-for="data in listColumns">
                <template v-if="data.filter_type ==='field' || data.filter_type ==='dropdown' ">
                    <filter-item :id="data.id+'_'+data.filter_type" :key="data.id+'_'+data.filter_type"
                                 :filterType="data.filter_type" :filterDatas="retrieveFiltersInputData(data)"
                                 @filterValue="getFilterValue"
                                 :columnName="data.column_name"
                                 :columnNameLabel="data.label"
                    />
                </template>

            </template>


        </div>

        <table :aria-describedby="'Table of actions lists '">
            <thead class="list-table-head">
            <tr>
                <th>
                    <!--<input type="checkbox" v-model="checkedRows.rows" class="em-switch input" :value="listData" > --></th>
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
                v-for="data in items"
                :key="data.id"
                :rowData="data"
                :listColumns="listColumns"
                :checkedRows='checkedRows'
            />
            <tr v-if="items.length == 0">
                <td :colspan="listColumns.length" >
                    {{translate('COM_EMUNDUS_MOD_RSST_LIST_NO_DATA')}}
                </td>
            </tr>
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
        items: [],
        sort: {
            last: "",
            order: "",
            orderBy: "",
        },
        filters: [],
        checkedRows: {
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
                this.items = this.listData

                this.filtersInitialize();


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

        retrieveFiltersInputData(column) {
            if (column.filter_type == 'dropdown') {
                return this.listData.map(el => {
                    return el[column.column_name]
                });
            } else {
                return [];
            }

        },

        filtersInitialize() {

            this.listColumns.forEach(element => {

                if (element.filter_type === "field" || element.filter_type === "dropdown") {
                    this.filters.push({column_name: element.column_name, filterValue: ''})
                }
            })

        },

        getFilterValue(value, column_name) {

            this.filters = this.filters.map(el => {

                if (el.column_name == column_name) {
                    return {...el, filterValue: value === 'all' ? '' : value}
                }
                return el;
            })
            this.filtering();

        },
        filtering() {

            this.items = this.listData.filter(item => {
                return this.filters.every(key => {
                    return key.filterValue.toLowerCase().split(' ').every(v => item[key.column_name].toLowerCase().includes(v))
                })
            })

        }


    }
}
</script>

<style scoped>

</style>
