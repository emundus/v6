<template>
  <div id="checkboxF">
    <div class="row rowmodal">
      <div class="form-group">
        <label>{{helptext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.rollover" />
      </div>
      <div class="d-flex mb-1">
        <label class="require col-md-3">{{suboptions}} :</label>
      </div>
      <!--<div class="col-md-10 form-group flex">
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
        <label for="databasejoin_check" class="ml-10px mb-0">{{ DataTables }}</label>
      </div>-->
      <div class="col-md-10">
        <draggable
                v-model="arraySubValues"
                v-if="databasejoin != 1"
                @end="needtoemit()"
                handle=".handle">
        <div v-for="(sub_values, i) in arraySubValues" :key="i" class="d-flex mb-1">
          <span class="icon-handle">
            <em class="fas fa-grip-vertical handle"></em>
          </span>
          <input type="text" v-model="arraySubValues[i]" @change="needtoemit()" class="form__input field-general w-input mb-0" style="height: 35px" :id="'suboption_' + i" @keyup.enter="add"/>
          <button @click.prevent="leave(i)" type="button" class="remove-option">-</button>
        </div>
        </draggable>
        <button @click.prevent="add" type="button" v-if="databasejoin != 1" class="bouton-sauvergarder-et-continuer-3 button-add-option" style="margin-bottom: 2em">{{AddOption}}</button>
        <select v-if="databasejoin == 1" class="dropdown-toggle" v-model="databasejoin_data" style="margin: 20px 0 30px 0;">
          <option v-for="(database,index) in databases" :value="index">{{database.label}}</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import axios from "axios";
import Swal from "sweetalert2";
import draggable from "vuedraggable";
const qs = require("qs");

export default {
  name: "checkboxF",
  components: {
    draggable
  },
  props: { element: Object, databases: Array },
  data() {
    return {
      arraySubValues: [],
      databasejoin: "0",
      databasejoin_data: 0,
      helptext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT"),
      suboptions: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_OPTIONS"),
      AddOption: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_ADD_OPTIONS"),
      DataTables: this.translate("COM_EMUNDUS_ONBOARD_TIP_DATABASEJOIN"),
    };
  },
  methods: {
    add: _.debounce(function() {
      let size = Object.keys(this.arraySubValues).length;
      this.$set(this.arraySubValues, size, "");
      this.needtoemit();
      let id = 'suboption_' + size.toString();
      setTimeout(() => {
        document.getElementById(id).focus();
      }, 100);
    },150),
    leave: function(index) {
      this.$delete(this.arraySubValues, index);
      this.needtoemit();
    },
    initialised: function() {
      if(this.element.plugin === 'databasejoin'){
        this.databasejoin = 1;
        this.databases.forEach((db,index) => {
          if(db.database_name == this.element.params.join_db_name){
            this.databasejoin_data = index;
          }
        })
      } else {
        if (typeof this.element.params.sub_options !== 'undefined') {
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getJTEXTA",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              toJTEXT: this.element.params.sub_options.sub_labels
            })
          }).then(response => {
            Object.values(response.data).forEach(rep => {
              this.arraySubValues.push(rep);
            });
            this.needtoemit();
          }).catch(e => {
            console.log(e);
          });
        } else {
          this.element.params.sub_options = {
            'sub_values': [],
            'sub_labels': [],
          }
          this.arraySubValues = this.element.params.sub_options.sub_labels;
        }
      }
    },
    needtoemit: _.debounce(function() {
      this.$emit("subOptions", this.arraySubValues);
    }),

    checkOnboarding(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=settings&task=checkfirstdatabasejoin",
      }).then(response => {
        if(response.data.status) {
          Swal.fire({
            title: this.translate("COM_EMUNDUS_ONBOARD_TIP_DATABASEJOIN"),
            text: this.translate("COM_EMUNDUS_ONBOARD_TIP_DATABASEJOIN_TEXT"),
            type: "info",
            showCancelButton: false,
            showCloseButton: true,
            allowOutsideClick: false,
            confirmButtonColor: '#de6339',
            confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
          }).then(result => {
            if (result.value) {
              axios({
                method: "post",
                url: "index.php?option=com_emundus_onboard&controller=settings&task=removeparam",
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded"
                },
                data: qs.stringify({
                  param: 'first_databasejoin',
                })
              });
            }
          });
        }
      });
    }
  },
  created: function() {
    this.initialised();
  },
  watch: {
    databasejoin: function(value){
      if(value) {
        this.checkOnboarding();
        this.element.params.join_db_name = this.databases[this.databasejoin_data].database_name;
        this.element.params.database_join_display_type = 'checkbox';
        this.element.params.join_key_column = this.databases[this.databasejoin_data].join_column_id;
        if(this.databases[this.databasejoin_data].translation == '1') {
          this.element.params.join_val_column = this.databases[this.databasejoin_data].join_column_val + '_fr';
          this.element.params.join_val_column_concat = '{thistable}.' + this.databases[this.databasejoin_data].join_column_val + '_{shortlang}';
        } else {
          this.element.params.join_val_column = this.databases[this.databasejoin_data].join_column_val;
          this.element.params.join_val_column_concat = '';
        }
      } else {
        this.element.plugin = 'checkbox';
        delete this.element.params.join_db_name;
        delete this.element.params.database_join_display_type;
        delete this.element.params.join_key_column;
        delete this.element.params.join_val_column;
        delete this.element.params.join_val_column_concat;
        if(typeof this.element.params.sub_options === 'undefined') {
          this.element.params.sub_options = {
            'sub_values': [],
            'sub_labels': [],
          }
          this.arraySubValues = this.element.params.sub_options.sub_labels;
        }
      }
    },
    databasejoin_data: function(value){
      this.element.params.join_db_name = this.databases[value].database_name;
      this.element.params.join_key_column = this.databases[value].join_column_id;
      this.element.params.database_join_display_type = 'checkbox';
      if(this.databases[value].translation == '1') {
        this.element.params.join_val_column = this.databases[value].join_column_val + '_fr';
        this.element.params.join_val_column_concat = '{thistable}.' + this.databases[value].join_column_val + '_{shortlang}';
      } else {
        this.element.params.join_val_column = this.databases[value].join_column_val;
        this.element.params.join_val_column_concat = '';
      }
    }
  }
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
  #checkboxF{
    padding: 10px 0;
  }
.icon-handle{
  color: #cecece;
  cursor: grab;
  margin-right: 10px;
}
</style>
