<template>
  <div id="dropdownF">
    <div class="row rowmodal">
      <div class="form-group">
        <label>{{translations.helptext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.rollover" />
      </div>
      <div class="d-flex mb-1">
        <label class="require col-md-3">{{translations.suboptions}} :</label>
      </div>
      <div class="col-md-12 form-group flex">
        <div class="toggle">
        <input type="checkbox"
               true-value="true"
               false-value="false"
               id="no_default_value"
               class="check"
               v-model="no_default_value" />
          <strong class="b switch"></strong>
          <strong class="b track"></strong>
        </div>
        <label for="no_default_value" class="ml-10px mb-0">{{ translations.No_Default_Value }}</label>
      </div>
      <div class="col-md-10 form-group flex">
        <div class="toggle">
          <input type="checkbox"
                 true-value="1"
                 false-value="0"
                 class="check"
                 id="databasejoin_check"
                 name="'databasejoin_check'"
                 v-model="databasejoin"
          />
          <strong class="b switch"></strong>
          <strong class="b track"></strong>
        </div>
        <label for="databasejoin_check" class="ml-10px mb-0">{{ translations.DataTables  }}</label>
      </div>
      <div class="col-md-10">
        <draggable
                v-model="arraySubValues"
                v-if="databasejoin != 1"
                @end="needtoemit()"
                handle=".handle">
        <div v-for="(sub_values, i) in arraySubValues" :key="i" class="d-flex mb-1" v-if="display_first_option != i">
          <span class="icon-handle">
            <em class="fas fa-grip-vertical handle"></em>
          </span>
          <input type="text" v-model="arraySubValues[i]" @change="needtoemit()" class="form__input field-general w-input mb-0" style="height: 35px" :id="'suboption_' + i" @keyup.enter="add"/>
          <button @click.prevent="leave(i)" type="button" class="remove-option">-</button>
        </div>
        </draggable>
        <button @click.prevent="add" type="button" v-if="databasejoin != 1" class="bouton-sauvergarder-et-continuer-3 button-add-option" style="margin-bottom: 2em">{{translations.AddOption}}</button>
        <select v-if="databasejoin == 1" class="dropdown-toggle" v-model="databasejoin_data" style="margin: 20px 0 30px 0;" @change="retrieveDataBaseJoinColumns()">
          <option v-for="(database,index) in databases" :value="index">{{database.label}}</option>
        </select>
       <div v-if="databasejoin == 1">
          <label>{{translations.OrderBy}}</label>
          <select class="dropdown-toggle" v-model="databasejoin_data_order" style="margin: 20px 0 30px 0;">
            <option v-for="val in databases_colums" :value="val.COLUMN_NAME">{{val.COLUMN_NAME}}</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import Swal from 'sweetalert2';
import draggable from 'vuedraggable';

const qs = require('qs');

export default {
  name: 'dropdownF',
  components: {
    draggable,
  },
  props: { element: Object, databases: Array },
  data() {
    return {
      arraySubValues: [],
      databases_colums: [],
      databasejoin: '0',
      no_default_value: false,
      display_first_option: null,
      databasejoin_data: 0,
      databasejoin_data_order: '',
      translations: {
        helptext: Joomla.JText._('COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT'),
        suboptions: Joomla.JText._('COM_EMUNDUS_ONBOARD_BUILDER_OPTIONS'),
        AddOption: Joomla.JText._('COM_EMUNDUS_ONBOARD_BUILDER_ADD_OPTIONS'),
        DataTables: Joomla.JText._('COM_EMUNDUS_ONBOARD_TIP_DATABASEJOIN'),
        No_Default_Value: Joomla.JText._('COM_EMUNDUS_ONBOARD_BUILDER_NO_DEFAULT_VALUE'),
        OrderBy: Joomla.JText._('COM_EMUNDUS_ONBOARD_BUILDER_ORDER_BY'),
      },
    };
  },
  methods: {
    add: _.debounce(function () {
      const size = Object.keys(this.arraySubValues).length;
      this.$set(this.arraySubValues, size, '');
      this.needtoemit();
      const id = `suboption_${size.toString()}`;
      setTimeout(() => {
        document.getElementById(id).focus();
      }, 100);
    }, 150),
    leave(index) {
      this.$delete(this.arraySubValues, index);
      this.needtoemit();
    },
    initialised() {
      if (this.element.plugin === 'databasejoin') {
        this.databasejoin = 1;

        if(this.element.params.database_join_show_please_select=="1"){
          this.no_default_value=true;
        } else {
          this.no_default_value=false;
        }


        this.databases.forEach((db, index) => {
          if (db.database_name == this.element.params.join_db_name) {
            this.databasejoin_data = index;
            this.retrieveDataBaseJoinColumns();
            const order = this.element.params.database_join_where_sql;
            this.databasejoin_data_order = order.replace(/order by /g, '');
          }
        });
      } else {
        this.element.params.default_value = false;
        this.no_default_value = false;
        this.retrieveDataBaseJoinColumns();
        if (typeof this.element.params.sub_options !== 'undefined') {
          if (typeof this.element.params.sub_options.sub_initial_selection !== 'undefined') {
            this.element.params.sub_options.sub_values.forEach((value, i) => {
              if (value == this.element.params.sub_options.sub_initial_selection[0]) {
                this.display_first_option = i;
              }
            });
            if (this.display_first_option != null) {
              this.element.params.default_value = true;
              this.no_default_value = true;
            }
          }
          axios({
            method: 'post',
            url: 'index.php?option=com_emundus_onboard&controller=formbuilder&task=getJTEXTA',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            data: qs.stringify({
              toJTEXT: this.element.params.sub_options.sub_labels,
            }),
          }).then((response) => {
            Object.values(response.data).forEach((rep) => {
              this.arraySubValues.push(rep);
            });
            this.needtoemit();
          }).catch((e) => {
            console.log(e);
          });
        } else {
          this.element.params.sub_options = {
            sub_values: [],
            sub_labels: [],
          };
          this.arraySubValues = this.element.params.sub_options.sub_labels;
        }
      }
    },

    retrieveDataBaseJoinColumns() {
      axios({
        method: 'post',
        url:
            'index.php?option=com_emundus_onboard&controller=formbuilder&task=getdatabasesjoinOrdonancementColomns',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        data: qs.stringify({
          database_name: (this.databases[this.databasejoin_data]).database_name,
        }),
      }).then((response) => {
        this.databases_colums = response.data.data;
      }).catch((e) => {
        console.log(e);
      });
    },
    needtoemit: _.debounce(function () {
      this.$emit('subOptions', this.arraySubValues);
    }),

    checkOnboarding() {
      axios({
        method: 'get',
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=checkfirstdatabasejoin',
      }).then((response) => {
        if (response.data.status) {
          Swal.fire({
            title: Joomla.JText._('COM_EMUNDUS_ONBOARD_TIP_DATABASEJOIN'),
            text: Joomla.JText._('COM_EMUNDUS_ONBOARD_TIP_DATABASEJOIN_TEXT'),
            type: 'info',
            showCancelButton: false,
            showCloseButton: true,
            allowOutsideClick: false,
            confirmButtonColor: '#de6339',
            confirmButtonText: Joomla.JText._('COM_EMUNDUS_ONBOARD_OK'),
          }).then((result) => {
            if (result.value) {
              axios({
                method: 'post',
                url: 'index.php?option=com_emundus_onboard&controller=settings&task=removeparam',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                data: qs.stringify({
                  param: 'first_databasejoin',
                }),
              });
            }
          });
        }
      });
    },
  },
  created() {
    this.initialised();
  },
  watch: {
    databasejoin(value) {
      if (value == 1) {
        this.checkOnboarding();

        this.element.plugin = 'databasejoin';
        this.element.params.join_db_name = this.databases[this.databasejoin_data].database_name;
        this.element.params.database_join_display_type = 'dropdown';
        this.element.params.join_key_column = this.databases[this.databasejoin_data].join_column_id;
        if (this.databases[this.databasejoin_data].translation == '1') {
          this.element.params.join_val_column = `${this.databases[this.databasejoin_data].join_column_val}_fr`;
          this.element.params.join_val_column_concat = `{thistable}.${this.databases[this.databasejoin_data].join_column_val}_{shortlang}`;
        } else {
          this.element.params.join_val_column = this.databases[this.databasejoin_data].join_column_val;
          this.element.params.join_val_column_concat = '';
        }

      } else {
        this.element.plugin = 'dropdown';
        delete this.element.params.join_db_name;
        delete this.element.params.database_join_display_type;
        delete this.element.params.join_key_column;
        delete this.element.params.join_val_column;
        delete this.element.params.join_val_column_concat;
        delete this.element.params.database_join_where_sql;
        if (typeof this.element.params.sub_options === 'undefined') {
          this.element.params.sub_options = {
            sub_values: [],
            sub_labels: [],
          };
          this.arraySubValues = this.element.params.sub_options.sub_labels;
        }
      }
    },

    databasejoin_data(value) {
      const db = this.databases[value];
      this.element.params.join_db_name = db.database_name;
      this.element.params.join_key_column = db.join_column_id;
      if (db.translation === '1') {
        this.element.params.join_val_column = `${db.join_column_val}_fr`;
        this.element.params.join_val_column_concat = `{thistable}.${db.join_column_val}_{shortlang}`;
      } else {
        this.element.params.join_val_column = db.join_column_val;
        this.element.params.join_val_column_concat = '';
      }
    },

    databasejoin_data_order(value) {
      if (this.databasejoin == 1) {
        this.element.params.database_join_where_sql = `order by ${value}`;
      }
    },

    no_default_value(value) {
      this.element.params.default_value = value;
    },
  },
};
</script>
<style scoped>
.flex {
  display: flex;
  align-items: center;
  margin-bottom: 1em;
  height: 30px;
}
.rowmodal {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}
#dropdownF{
  padding: 10px 0;
}
.icon-handle{
  color: #cecece;
  cursor: grab;
  margin-right: 10px;
}
</style>
