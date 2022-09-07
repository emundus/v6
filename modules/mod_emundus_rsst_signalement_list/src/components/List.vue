<template>
    <div id="actions-list-table">
        <div class="em-flex-row em-flex-space-between em-w-auto em-mb-32">
            <input class="em-input withSearchIco" type="search" placeholder="Rechercher" v-model="searchTerm"
                   @keyup="searchInAllListColumn">
            <span class="material-icons reload-icons" @click="reloadData">loop</span>
        </div>
        <div class="em-flex-row em-flex-space-between em-w-auto em-mb-32">
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

        <table :class="{ loading: loading }" :aria-describedby="'Table of actions lists '">
            <thead class="list-table-head">
            <tr>
                <th>
                    <!--<input type="checkbox" v-model="checkedRows.rows" class="em-switch input" :value="listData" > -->
                </th>
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
                <th>
                    <span>
                        <list-action-menu :actionColumnId="ListActionColumn" :listId="listId"
                                          @setAs="setAs"></list-action-menu>
                    </span>
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
                :actionColumnId="ListActionColumn"
                :listId="listId"
            />
            <tr v-if="items.length == 0">
                <td :colspan="listColumns.length+2" class="em-text-align-center">
                    {{ translate('COM_EMUNDUS_MOD_RSST_LIST_NO_DATA') }}
                </td>
            </tr>
            </tbody>
        </table>
        <div v-if="loading">
            <div class="em-page-loader"></div>
        </div>
    </div>
</template>

<script>

import Row from './Row.vue';
import Filter from './Filter.vue';
import ListService from '../services/list';
import ListActionMenu from './ListActionMenu.vue';

export default {
    name: "List",
    components: {
        Row,
        'filter-item': Filter,
        'list-action-menu': ListActionMenu
    },
    props: {
        listId: {
            type: String,
            required: true,
        },
        ListActionColumn: {
            type: String,
            required: false
        },
        listParticularConditionalColumn:{
            type: String,
            required: false,
        },
        listParticularConditionalColumnValues:{
            type: String,
            required: false
        }
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
        searchTerm: ''

    }),
    created() {

        this.retriveListData();
    },
    methods: {
        reloadData() {
            this.loading = true;
            this.listColumns = [];
            this.listData = [];
            this.items = [];
            this.filters = [];
            this.retriveListData();

        },
        async retriveListData() {

            let particularConditionalColumn = this.listParticularConditionalColumn.split(',') || []
            let particularConditionalColumnValues = this.listParticularConditionalColumnValues.split(',') || [];
            let particularConditionalRealColumnValues = [];
            particularConditionalColumnValues.forEach(element =>{
                particularConditionalRealColumnValues.push((element.split('|')).join(','));
            });

            try {
                const response = await ListService.getListAndDataContains(this.listId,particularConditionalColumn,particularConditionalRealColumnValues);

                this.listColumns = response.data.listColumns;

                this.listData = response.data.listData;
                this.items = this.listData

                this.filtersInitialize();

                this.loading = false;


            } catch (e) {
                this.loading = false;
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
                return [...new Set(this.listData.map(el => {
                    return el[column.column_name]
                }))];
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
                    if(key !== null && key.filterValue !== null) {
                        return key.filterValue.toLowerCase().split(' ').every(v => item[key.column_name].toLowerCase().includes(v))
                    } else {
                        return false;
                    }
                })
            })

        },

        searchInAllListColumn() {
            this.items = this.listData.filter(item => {
                return Object.keys(item).some(key => {
                    if (item[key] !== null) {

                        return item[key].toLowerCase().includes(this.searchTerm.toLowerCase());
                    } else {
                        return false;
                    }
                })
            })
        },

        clearFilters() {

            this.filters.map(el => {
                return {...el, filterValue: ''}
            })

            this.filtering();
        },

        async setAs(actionColumn, value) {

            try {
                const checkedRowsId = this.checkedRows.rows.map((val) => {
                    return val.id
                });
                const response = await ListService.setAs(actionColumn, value, checkedRowsId.join(','));

            } catch (e) {
                console.log(e);
            }


        }


    }
}
</script>

<style scoped lang="scss">

input.withSearchIco {
    height: 35px !important;
    display: block;
    padding: 9px 4px 9px 45px !important;
    background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'%3E%3C/path%3E%3C/svg%3E") no-repeat 13px center;
}

.reload-icons {
    font-size: 30px !important;
    cursor: grab;
    transform: rotate(90deg);
}

table {

    &.loading {
        visibility: hidden;
    }

    border: 0;

    tr {
        th:first-of-type {
            width: 39px;

            input {
                margin-right: 0px;
            }
        }
    }

    tr,
    th {
        height: 49px;
        background: transparent;
        background-color: transparent;
    }

    td,
    th {
        width: fit-content;
    }

    th.desc,
    td.desc {
        max-width: 300px;
        width: initial;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    th.status,
    td.status {
        min-width: 100px;
        white-space: nowrap;
    }

    thead {
        tr {
            th {
                border-top: 1px solid #e0e0e0;
                border-bottom: 1px solid #e0e0e0;

                .material-icons {
                    transform: translateY(3px);
                }
            }
        }
    }
}


</style>
