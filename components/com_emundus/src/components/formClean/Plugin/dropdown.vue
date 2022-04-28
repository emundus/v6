<template>
  <div id="dropdownF">
    <div class="row rowmodal">
      <div class="em-mb-32">
        <label>{{translations.helptext}} :</label>
        <input type="text" v-model="element.params.rollover" />
      </div>
      <div class="em-flex-row em-mb-16">
        <label class="require col-md-3">{{translations.suboptions}} :</label>
      </div>
      <div class="col-md-12 em-flex-row em-mb-8">
        <div class="em-toggle">
        <input type="checkbox"
               true-value="true"
               false-value="false"
               id="no_default_value"
               class="em-toggle-check"
               v-model="no_default_value" />
          <strong class="b em-toggle-switch"></strong>
          <strong class="b em-toggle-track"></strong>
        </div>
        <label for="no_default_value" class="em-ml-8 em-mb-0">{{ translations.No_Default_Value }}</label>
      </div>
      <div class="col-md-10 em-flex-row em-mb-16">
        <div class="em-toggle">
          <input type="checkbox"
                 true-value="1"
                 false-value="0"
                 class="em-toggle-check"
                 id="databasejoin_check"
                 name="'databasejoin_check'"
                 v-model="databasejoin"
          />
          <strong class="b em-toggle-switch"></strong>
          <strong class="b em-toggle-track"></strong>
        </div>
        <label for="databasejoin_check" class="em-ml-8 em-mb-0">{{ translations.DataTables  }}</label>
      </div>
      <div class="col-md-10">
        <draggable
                v-model="arraySubValues"
                v-if="databasejoin != 1"
                @end="needtoemit()"
                handle=".handle">
        <div v-for="(sub_values, i) in arraySubValues" :key="i" class="em-flex-row em-mb-16" v-if="display_first_option != i">
          <span class="icon-handle">
            <span class="material-icons handle">drag_indicator</span>
          </span>
          <input type="text" v-model="arraySubValues[i].sub_label" @change="needtoemit()" :id="'suboption_' + i" @keyup.enter="add"/>
          <button @click.prevent="leave(i)" type="button" class="em-transparent-button em-pointer"><span class="material-icons">remove_circle_outline</span></button>
        </div>
        </draggable>
        <button @click.prevent="add" type="button" v-if="databasejoin != 1" class="em-secondary-button em-w-auto em-ml-32">{{translations.AddOption}}</button>
        <select v-if="databasejoin == 1" v-model="databasejoin_data" class="em-mt-16" @change="retrieveDataBaseJoinColumns()">
          <option v-for="(database,index) in databases" :value="index">{{database.label}}</option>
        </select>
       <div v-if="databasejoin == 1" class="em-mt-16">
          <label class="em-w-100">{{translations.OrderBy}}</label>
          <select v-model="databasejoin_data_order" class="em-mt-8 em-w-100">
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

import formBuilderService from '../../../services/formbuilder';
import settingsService from '../../../services/settings';

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
        helptext: this.translate('COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT'),
        suboptions: this.translate('COM_EMUNDUS_ONBOARD_BUILDER_OPTIONS'),
        AddOption: this.translate('COM_EMUNDUS_ONBOARD_BUILDER_ADD_OPTIONS'),
        DataTables: this.translate('COM_EMUNDUS_ONBOARD_TIP_DATABASEJOIN'),
        No_Default_Value: this.translate('COM_EMUNDUS_ONBOARD_BUILDER_NO_DEFAULT_VALUE'),
        OrderBy: this.translate('COM_EMUNDUS_ONBOARD_BUILDER_ORDER_BY'),
      },
    };
  },
  methods: {
    add: _.debounce(function () {
      const size = Object.keys(this.arraySubValues).length;
      this.$set(this.arraySubValues, size, {
        'sub_value' : null,
        'sub_label' : '',
      });
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

          formBuilderService.getJTEXTA(this.element.params.sub_options.sub_labels).then((response) => {
            this.element.params.sub_options.sub_values.forEach((value, i) => {
              let object = {
                'sub_value' : value,
                'sub_label' : Object.values(response.data)[i],
              };
              this.arraySubValues.push(object);
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
      formBuilderService.getDatabaseJoinOrderColumns(this.databases[this.databasejoin_data].database_name).then((response) => {
        this.databasejoin_columns = response.data;
      }).catch((e) => {
        console.log(e);
      });
    },
    needtoemit: _.debounce(function () {
      this.$emit('subOptions', this.arraySubValues);
    }),

    checkOnboarding() {
      settingsService.checkFirstDatabaseJoin().then((response) => {
        if (response.data.status) {
          Swal.fire({
            title: this.translate('COM_EMUNDUS_ONBOARD_TIP_DATABASEJOIN'),
            text: this.translate('COM_EMUNDUS_ONBOARD_TIP_DATABASEJOIN_TEXT'),
            type: 'info',
            showCancelButton: false,
            showCloseButton: true,
            allowOutsideClick: false,
            confirmButtonText: this.translate('COM_EMUNDUS_ONBOARD_OK'),
            customClass: {
              title: 'em-swal-title',
              confirmButton: 'em-swal-confirm-button',
            },
          }).then((result) => {
            if (result.value) {
              settingsService.removeParameter('first_databasejoin');
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
.em-mb-0{
  margin-bottom: 0 !important;
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
  margin-right: 18px;
}
</style>
