<template>
  <div id="actions-list-table">
    <div class="em-flex-row em-flex-space-between em-w-auto em-mb-32">
      <input
          class="em-input withSearchIco"
          type="search"
          placeholder="Rechercher"
          v-model="searchTerm"
          @keyup="searchInAllListColumn"
      />
      <span class="material-icons reload-icons" @click="reloadData">loop</span>
    </div>
    <div class="em-flex-row em-w-auto em-mb-32">
      <filter-item :filterType="'groupBy'" :filterDatas="listColumns" @groupByCriteriaValue="groupByColumn"/>
      <filter-item
          v-for="data in fieldAndDropdownFilters"
          :id="data.id+'_'+data.filter_type"
          :key="data.id"
          :filterType="data.filter_type" :filterDatas="retrieveFiltersInputData(data)"
          @filterValue="getFilterValue"
          :columnName="data.column_name"
          :columnNameLabel="data.label"
      />
    </div>
    <table :class="{ loading: loading }" :aria-describedby="'Table of actions lists '">
      <thead class="list-table-head">
      <tr>
        <th></th>
        <th v-for="data in showingListColumns" :id="data.column_name" :key="data.id" @click="orderBy(data.column_name)"
            class="em-pointer" style="user-select: none;">
          <span v-if="sort.orderBy == data.column_name && sort.order == 'asc'"
                class="material-icons">arrow_upward</span>
          <span v-else-if="sort.orderBy == data.column_name && sort.order == 'desc'" class="material-icons">arrow_downward</span>
          {{ translate(data.label) }}
        </th>
        <th>
          <span v-if="checkedRows.length > 0" class="material-icons em-pointer"
                @click="moreOptionsOpened = !moreOptionsOpened">more_horiz</span>
          <more-options v-if="checkedRows.length > 0 && moreOptionsOpened" :options="moreOptionsData"
                        @select-option="onSelectOption" @focusout="moreOptionsOpened = false"></more-options>
        </th>
      </tr>
      </thead>
      <tbody>
      <template v-if="hasBeenGroupBy">
        <template v-for="group in items">
          <tr @click="(e) => {toggle(rowGroupByRowKeyName(group), e); retrieveGroupeClassColor(group)}"
              :class="retrieveGroupeClassColor(group)">
            <td :colspan="showingListColumns.length+1">
              <input type="checkbox" class="em-switch input" style="margin-top: -2px;"
                     @change="(e) => toggleCheckGroupRows(e, group)">
              <div class="em-ml-32" style="display: inline-block;"><b v-html="rowGroupByRowKeyName(group)"></b></div>
            </td>
            <td style="border-left: none;text-align: end">
              <div>
                <span v-if="opened.includes(rowGroupByRowKeyName(group))" class="material-icons">arrow_drop_down</span>
                <span v-else class="material-icons">arrow_drop_up</span>
              </div>
            </td>
          </tr>
          <Row v-show="opened.includes(rowGroupByRowKeyName(group))"
               v-for="data in groupByItemArraySubValues(group)" :key="data.id"
               :rowData="data"
               :listColumns="listColumns"
               :checkedRows='checkedRows'
               :actionColumnId="ListActionColumn"
               :rowGroupByRowKeyName="rowGroupByRowKeyName(group)"
               :listColumnShowingAsBadge="listColumnShowingAsBadge"
               :filterColumnUsedActually="filterColumnUsedActually"
               :listColumnToNotShowingWhenFilteredBy="listColumnToNotShowingWhenFilteredBy"
               :readOnly="readOnly"
               @toggle-check="toggleCheckRow"
          />
        </template>
      </template>
      <Row v-else
           v-for="data in items"
           :key="data.id"
           :rowData="data"
           :listColumns="listColumns"
           :checkedRows='checkedRows'
           :actionColumnId="ListActionColumn"
           :listId="listId"
           :listColumnShowingAsBadge="listColumnShowingAsBadge"
           :filterColumnUsedActually="filterColumnUsedActually"
           :listColumnToNotShowingWhenFilteredBy="listColumnToNotShowingWhenFilteredBy"
           :readOnly="readOnly"
           @toggle-check="toggleCheckRow"
      />
      <tr v-if="items.length == 0">
        <td :colspan="listColumns.length+2" class="em-text-align-center">
          {{ translate('COM_EMUNDUS_MOD_RSST_LIST_NO_DATA') }}
        </td>
      </tr>
      </tbody>
    </table>
    <div v-if="loading" class="em-page-loader"></div>
  </div>
</template>

<script>
import Row from './Row.vue';
import Filter from './Filter.vue';
import ListService from '../services/list';
import MoreOptions from "./MoreOptions";

export default {
  name: "List",
  components: {
    Row,
    'filter-item': Filter,
    MoreOptions
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
    listParticularConditionalColumn: {
      type: String,
      required: false,
    },
    listParticularConditionalColumnValues: {
      type: String,
      required: false
    },
    listColumnShowingAsBadge: {
      type: String,
      required: false,
    },
    listColumnToNotShowingWhenFilteredBy: {
      type: String,
      required: false
    },
    readOnly: {
      type: Boolean,
      default: false
    }
  },
  data: () => ({
    loading: true,
    listColumns: [],
    listData: [],
    items: [],
    opened: [],
    filterColumnUsedActually: [],
    sort: {
      last: "",
      order: "",
      orderBy: "",
    },
    filters: [],
    checkedRows: [],
    searchTerm: '',
    hasBeenGroupBy: false,
    filterGroupCriteria: '',
    moreOptionsOpened: false,
    moreOptionsData: [
      {value: 'en_cours', label: 'En cours'},
      {value: 'a_faire', label: 'A faire'},
      {value: 'fait', label: 'Fait'},
      {value: 'sans_objet', label: 'Sans objet'}
    ]
  }),
  created() {
    this.retrieveListData();
  },
  methods: {
    toggle(key, e) {
      if (!e.target.classList.contains('em-switch')) {
        const index = this.opened.indexOf(key);
        if (index > -1) {
          this.opened.splice(index, 1);
        } else {
          this.opened.push(key)
        }
      }
    },
    groupByItemArraySubValues(item) {
      return item[1];
    },
    rowGroupByRowKeyName(item) {
      return this.texteFromValue(this.translate(item[0]));
    },
    reloadData() {
      this.loading = true;
      this.listColumns = [];
      this.listData = [];
      this.items = [];
      this.filters = [];
      this.hasBeenGroupBy = false;
      this.retrieveListData();
    },
    retrieveGroupeClassColor(group) {
      const data = this.groupByItemArraySubValues(group);
      let valueToHighlight = 'fait';

      if (data.find((item) => {
        return item.etat == 'a_faire' || item.etat == 'sans_objet'
      })) {
        valueToHighlight = 'a_faire';
      } else if (data.find((item) => {
        return item.etat == 'en_cours'
      })) {
        valueToHighlight = 'en_cours';
      } else {
        valueToHighlight = 'fait';
      }

      return this.classFromValue(valueToHighlight);
    },
    async retrieveListData() {
      let particularConditionalColumn = this.listParticularConditionalColumn.split(',') || []
      let particularConditionalColumnValues = this.listParticularConditionalColumnValues.split(',') || [];
      let particularConditionalRealColumnValues = [];
      particularConditionalColumnValues.forEach(element => {
        particularConditionalRealColumnValues.push((element.split('|')).join(','));
      });
      try {
        const response = await ListService.getListAndDataContains(this.listId, particularConditionalColumn, particularConditionalRealColumnValues);
        if (response.status) {
          this.listColumns = response.data.listColumns;
          this.listData = response.data.listData;
          this.items = this.listData;
          this.filtersInitialize();
        }
      } catch (e) {
        console.log(e);
      }
      this.loading = false;
    },
    orderBy(key) {
      if (!this.hasBeenGroupBy) {
        if (this.sort.last == key) {
          this.sort.order = this.sort.order == "asc" ? "desc" : "asc";
          this.items.reverse();
        } else {
          this.items.sort((a, b) => {
            if (a[key] < b[key]) {
              return -1;
            } else if (a[key] > b[key]) {
              return 1;
            }
            return 0;
          });
          this.sort.order = "asc";
        }
      } else {
        this.items.forEach((group, index) => {
          if (this.sort.last == key) {
            this.sort.order = this.sort.order == "asc" ? "desc" : "asc";
            this.items[index][1].reverse();
          } else {
            this.items[index][1].sort((a, b) => {
              if (a[key] < b[key]) {
                return -1;
              } else if (a[key] > b[key]) {
                return 1;
              }
              return 0;
            });

            this.sort.order = "asc";
          }
        });
      }

      this.sort.orderBy = key;
      this.sort.last = key;
    },
    groupByColumn(columnName) {
      this.filterGroupCriteria = columnName;
      if (columnName != null && columnName != '' && columnName != 'all') {
        this.items = Object.entries(this.groupBy(this.listData, columnName));
        this.hasBeenGroupBy = true;
      } else {
        this.items = this.listData;
        this.hasBeenGroupBy = false;
      }
    },
    groupBy(arr, criteria) {
      const itemsGroupBY = arr.reduce(function (acc, currentValue) {
        if (!acc[currentValue[criteria]]) {
          acc[currentValue[criteria]] = [];
        }
        acc[currentValue[criteria]].push(currentValue);
        return acc;
      }, {});
      return itemsGroupBY;
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
      });
    },
    getFilterValue(value, column_name) {
      const index = this.filterColumnUsedActually.indexOf(column_name);
      if (index > -1 || value == 'all') {
        this.filterColumnUsedActually.splice(index, 1);
      } else {
        this.filterColumnUsedActually.push(column_name)
      }
      this.filters = this.filters.map(el => {
        if (el.column_name == column_name) {
          return {...el, filterValue: value === 'all' ? '' : value}
        }
        return el;
      });

      this.filtering();
    },

    filtering() {
      this.items = this.listData.filter(item => {
        return this.filters.every(key => {
          if (key !== null && key.filterValue !== null) {
            return key.filterValue.toLowerCase().split(' ').every(v => {
              if (item[key.column_name] !== null) {
                return item[key.column_name].toLowerCase().includes(v)
              } else {
                return false;
              }
            });
          } else {
            return false;
          }
        })
      });
      if (this.hasBeenGroupBy) {
        if (this.filterGroupCriteria != null && this.filterGroupCriteria != '' && this.filterGroupCriteria != 'all') {
          this.items = Object.entries(this.groupBy(this.items, this.filterGroupCriteria));
        }
      }
    },
    classFromValue(val) {
      let className = 'list-row ';
      switch (val) {
        case 'en_cours':
          className += 'inprogress';
          break;
        case 'fait' :
        case '1' :
          className += 'done';
          break;
        case '0' :
        case 'sans_objet' :
        case 'a_faire':
          className += 'todo';
          break;
        default :
          break;
      }
      className += ' em-pointer';
      return className;
    },

    searchInAllListColumn() {
      this.items = this.listData.filter(item => {
        return Object.keys(item).some(key => {
          if (item[key] !== null) {
            return item[key].toLowerCase().includes(this.searchTerm.toLowerCase());
          } else {
            return false;
          }
        });
      });
    },
    clearFilters() {
      this.filters.map(el => {
        return {...el, filterValue: ''}
      })
      this.filtering();
    },

    async setAs(actionColumn, value) {
      try {
        const checkedRowsId = this.checkedRows.map((val) => {
          return val.id
        });
        const response = await ListService.setAs(actionColumn, value, checkedRowsId.join(','));
      } catch (e) {
        console.log(e);
      }
    },
    toggleCheckRow(rowData) {
      const checked = this.checkedRows.some((row) => {
        return row.id == rowData.id && row.id != undefined
      });
      if (checked) {
        this.checkedRows = this.checkedRows.filter((row) => {
          return row.id != rowData.id
        });
      } else {
        this.checkedRows.push(rowData);
      }
    },
    toggleCheckGroupRows(e, group) {
      if (e.target.checked) {
        group[1].forEach((groupItem) => {
          if (!this.checkedRows.find((checked) => {
            return checked.id == groupItem.id
          })) {
            this.checkedRows.push(groupItem);
          }
        });
      } else {
        this.checkedRows = this.checkedRows.filter((checked) => {
          return !(group[1].find((groupItem) => {
            return groupItem.id == checked.id
          }));
        });
      }
    },
    onSelectOption(option) {
      this.moreOptionsOpened = false;
      ListService.updateActionState(option.value, this.checkedRows).then((response) => {
        if (response.status) {
          this.checkedRows.forEach((item) => {
            item.etat = option.value;
          });

          this.checkedRows = [];
        }
      });
    },
  },
  computed: {
    fieldAndDropdownFilters() {
      return this.listColumns.filter((data) => {
        return data.filter_type === 'field' || data.filter_type === 'dropdown';
      });
    },
    showingListColumns() {
      const unwantedColumns = this.listColumnToNotShowingWhenFilteredBy.split(',') || [];
      return this.listColumns.filter((data) => {
        if (data.column_name == 'id') {
          return false;
        }

        if (this.filterColumnUsedActually.length > 0 && this.filterColumnUsedActually.includes(data.column_name)) {
          return !unwantedColumns.includes(data.column_name);
        } else {
          return true;
        }
      });
    }
  },
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

tr.list-row td {
  border-left: 0;
  border-right: 0;
  font-size: 12px;
  padding: 0.85rem 0.5rem;
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
    color: black;
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
        color: black;

        .material-icons {
          transform: translateY(3px);
        }
      }
    }
  }


}

.list-table-head {
  background-color: white !important;
}

.list-row {

  &.done {
    background-color: #DFF5E9 !important;
    color: black;
    opacity: 100%;
  }

  &.todo {
    color: black;
    background-color: #FFEEEE !important;
  }

  &.inprogress {
    background: #FFFBDB;
  }
}


</style>
