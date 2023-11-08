<template>
  <tr class="list-row">
    <td><input type="checkbox" class="em-switch input" v-model='isChecked' checked='true'
               @change="$emit('toggle-check', rowData)"/></td>
    <td v-for="column in showingListColumns" :key="column.label">
      <div v-if="!badgeForColumn(column.column_name) ">
        <div v-if="column.plugin =='date'">{{ formattedDate(rowData[column.column_name]) }}</div>
        <div v-else-if="column.display_type == 'html'" v-html="rowData[column.column_name]"></div>
        <div v-else v-html="rowData[column.column_name]"></div>
      </div>
      <div v-else><span
          :class="classFromValue(rowData[column.column_name])">{{ texteFromValue(rowData[column.column_name]) }}</span>
      </div>
    </td>
    <td><span v-if="!readOnly" class="material-icons em-pointer" @click="moreOptionsOpened = !moreOptionsOpened">more_horiz</span>
    </td>
    <more-options v-if="moreOptionsOpened && !readOnly" :options="moreOptionsData" @select-option="onSelectOption"
                  @focusout="moreOptionsOpened = false"></more-options>
  </tr>
</template>

<script>
import ListService from '../services/list';
import MoreOptions from "./MoreOptions";

export default {
  name: "Row",
  props: {
    rowData: {
      type: Object,
      required: true,
    },
    listColumns: {
      type: Array,
      required: true
    },
    checkedRows: {
      type: Array,
      required: true
    },
    listColumnShowingAsBadge: {
      type: String,
      required: false,
    },
    filterColumnUsedActually: {
      type: Array,
      required: false
    },
    listColumnToNotShowingWhenFilteredBy: {
      type: String,
      required: false,
    },
    readOnly: {
      type: Boolean,
      default: false
    }
  },
  components: {
    MoreOptions
  },
  data: () => ({
    isChecked: false,
    moreOptionsOpened: false,
    moreOptionsData: []
  }),
  created() {
    this.isChecked = this.checkedRows.find(row => row.id === this.rowData.id) ? true : false;
    if (this.rowData.num_signalement && this.rowData.etat) {
      this.moreOptionsData = [
        {value: 'en_cours', label: 'En cours'},
        {value: 'a_faire', label: 'A faire'},
        {value: 'fait', label: 'Fait'},
        {value: 'sans_objet', label: 'Sans objet'}
      ];
    }
  },
  methods: {
    badgeForColumn(name) {
      const availableList = this.listColumnShowingAsBadge.split(',') || [];
      return availableList.includes(name);
    },
    classFromValue(val) {
      let className = '';
      switch (val) {
        case 'a_faire':
          className = 'tag todo';
          break;
        case 'en_cours':
          className = 'tag inprogress';
          break;
        case 'fait' :
          className = 'tag done';
          break;
        case 'sans_objet' :
          className = 'tag todo';
          break;
        case '1' :
          className = 'tag done';
          break;
        case '0' :
          className = 'tag todo';
          break;
        default :
          className = 'tag todo';
      }
      return className;
    },
    async setAs(actionColumn, value) {
      try {
        if (this.isChecked) {
          const response = await ListService.setAs(actionColumn, value, this.rowData.id);
        } else {
          Swal.fire({
            title: 'Merci de sélectionner une ligne avant de pouvoir effectuer cette action.',
            showCancelButton: false,
            confirmButtonColor: '#12db42',
            confirmButtonText: this.translate("OK"),
            customClass: {
              title: 'em-swal-title',
              confirmButton: 'em-swal-confirm-button',
            },
          });
        }
      } catch (e) {
        console.log(e);
      }
    },
    onSelectOption(option) {
      this.moreOptionsOpened = false;
      ListService.updateActionState(option.value, [this.rowData]).then((response) => {
        this.rowData.etat = option.value;
      });
    }
  },
  computed: {
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
  watch: {
    checkedRows: {
      handler() {
        this.isChecked = this.checkedRows.some((row) => {
          return row.id == this.rowData.id && row.id != undefined
        });
      },
      deep: true,
    }
  }
}
</script>

<style scoped lang="scss">
.list-row {
  span.tag {
    margin: 0 8px 8px 0;
    padding: 4px 8px;
    border-radius: 4px;
    color: #080C12;
    height: fit-content;
    background: #F2F2F3;

    &.done {
      background: #80d5a7;
    }

    &.todo {
      color: black;
      background: #f28d8d;
    }

    &.inprogress {

      background: #ffeb47;
      color: black
    }

    &.default {
      background: #F2F2F3;
      colo: black
    }
  }

  span.list-td-label,
  span.list-td-subject {
    cursor: pointer;
    transition: all .3s;

    &:hover {
      color: #20835F;
    }
  }
}

tr.list-row td {
  border-left: 0;
  border-right: 0;
  font-size: 12px;
  padding: 0.85rem 0.5rem;
}

.list-row:hover {
  background: #F2F2F3;
}
</style>

