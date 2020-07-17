<template>
  <div id="dropdownF">

    <div class="row rowmodal">
      <div class="form-group">
        <label>{{helptext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.rollover" />
      </div>
      <div class="col-md-8 flex">
        <label class="require col-md-3">{{suboptions}} :</label>
        <button @click.prevent="add" class="add-option">+</button>
        <button class="add-databasejoin" :class="{'databasejoin-active':databasejoin}" @click="databasejoin = !databasejoin">
          <i class="fas fa-table"></i>
        </button>
      </div>
      <div class="col-md-10">
        <div v-for="(sub_values, i) in arraySubValues" :key="i" class="dpflex" v-if="!databasejoin">
          <input type="text" v-model="arraySubValues[i]" @change="needtoemit()" class="form__input field-general w-input" style="height: 35px" :id="'suboption_' + i" @keyup.enter="add"/>
          <button @click.prevent="leave(i)" class="remove-option">-</button>
        </div>
        <select v-if="databasejoin" class="dropdown-toggle" v-model="databasejoin_data" style="margin: 20px 0 30px 0;">
          <option v-for="(database,index) in databases" :value="index">{{database.label}}</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import Axios from "axios";
const qs = require("qs");

export default {
  name: "dropdownF",
  props: { element: Object, databases: Array },
  data() {
    return {
      arraySubValues: [],
      databasejoin: false,
      databasejoin_data: 0,
      helptext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT"),
      suboptions: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_OPTIONS"),
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
        this.databasejoin = true;
        this.databases.forEach((db,index) => {
          if(db.database_name == this.element.params.join_db_name){
            this.databasejoin_data = index;
          }
        })
        this.element.plugin = this.element.params.database_join_display_type;
      } else  {
        if(typeof this.element.params.sub_options !== 'undefined') {
          Axios({
            method: "post",
            url:
                    "index.php?option=com_emundus_onboard&controller=formbuilder&task=getJTEXTA",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              toJTEXT: this.element.params.sub_options.sub_labels
            })
          }).then(r => {
            this.arraySubValues = r.data;
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
    })
  },
  created: function() {
    this.initialised();
  },
  watch: {
    databasejoin: function(value){
      if(value) {
        this.element.params.join_db_name = this.databases[this.databasejoin_data].database_name;
        this.element.params.database_join_display_type = 'dropdown';
        this.element.params.join_key_column = this.databases[this.databasejoin_data].join_column_id;
        this.element.params.join_val_column = this.databases[this.databasejoin_data].join_column_val;
      } else {
        delete this.element.params.join_db_name;
        delete this.element.params.database_join_display_type;
        delete this.element.params.join_key_column;
        delete this.element.params.join_val_column;
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
      this.element.params.join_val_column = this.databases[value].join_column_val;
      this.element.params.database_join_display_type = 'dropdown';
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
#dropdownF{
  padding: 10px;
}
</style>
