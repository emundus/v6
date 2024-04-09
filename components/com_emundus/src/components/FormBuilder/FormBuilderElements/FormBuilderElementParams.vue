<template>
  <div>
    <div v-for="param in params"  v-if="(param.published && !param.sysadmin_only) || (sysadmin && param.sysadmin_only && param.published)" class="form-group mb-4">
      <label :class="param.type === 'repeatable' ? 'font-bold' : ''">{{ translate(param.label) }}</label>

      <!-- DROPDOWN -->
      <div v-if="param.type === 'dropdown' || param.type === 'sqldropdown'">
        <select v-if="repeat_name !== '' && param.options && param.options.length > 0" v-model="element.params[repeat_name][index_name][param.name]" class="em-w-100">
          <option v-for="option in param.options" :value="option.value">{{ translate(option.label) }}</option>
        </select>
        <select v-else-if="param.options && param.options.length > 0" v-model="element.params[param.name]" class="em-w-100">
          <option v-for="option in param.options" :value="option.value">{{ translate(option.label) }}</option>
        </select>
        <div  v-if="element.plugin === 'emundus_fileupload'">
          <button type="button" class="mt-2 text-profile-color flex items-center" @click="EventNewDocForm">
            <label class="!mb-0">{{translate('COM_EMUNDUS_FORM_BUILDER_CREATE_DOCUMENT_NAME')}}</label>
            <span class="material-icons-outlined" :class="[(isActive ? 'rotate-90' : '')]">chevron_right</span>
          </button>
          <FormBuilderCreateDocument v-if="isActive" :profile_id="profile_id" :current_document="parseInt(element.params[param.name])" :key="parseInt(element.params[param.name])" :context="'element'" @documents-updated="reloadComponent"></FormBuilderCreateDocument>
        </div>

      </div>

      <!-- TEXTAREA -->
      <textarea v-else-if="param.type === 'textarea' && repeat_name !== ''" v-model="element.params[repeat_name][index_name][param.name]" class="em-w-100"></textarea>
      <textarea v-else-if="param.type === 'textarea'" v-model="element.params[param.name]" class="em-w-100"></textarea>

      <!-- DATABASEJOIN -->
      <div v-else-if="param.type === 'databasejoin' && repeat_name !== ''">
        <select v-model="element.params[repeat_name][index_name][param.name]" :key="reloadOptions" :id="param.name" @change="updateDatabasejoinParams" class="em-w-100" :class="databasejoin_description ? 'em-mb-4' : ''">
          <option v-for="option in param.options" :value="option.database_name">{{ option.label }}</option>
        </select>
        <label v-if="databasejoin_description" style="font-size: small">{{ databasejoin_description }}</label>
      </div>
      <div v-else-if="param.type === 'databasejoin'">
        <select v-model="element.params[param.name]" :key="reloadOptions" :id="param.name" @change="updateDatabasejoinParams" class="em-w-100" :class="databasejoin_description ? 'em-mb-4' : ''">
          <option v-for="option in param.options" :value="option.database_name">{{ option.label }}</option>
        </select>
        <label v-if="databasejoin_description" style="font-size: small">{{ databasejoin_description }}</label>
      </div>

      <div v-else-if="param.type === 'databasejoin_cascade' && repeat_name !== ''">
        <select v-model="element.params[repeat_name][index_name][param.name]" :key="reloadOptionsCascade" class="em-w-100">
          <option v-for="option in param.options" :value="option.COLUMN_NAME">{{ option.COLUMN_NAME }}</option>
        </select>
      </div>
      <div v-else-if="param.type === 'databasejoin_cascade'">
        <select v-model="element.params[param.name]" :key="reloadOptionsCascade" class="em-w-100">
          <option v-for="option in param.options" :value="option.COLUMN_NAME">{{ option.COLUMN_NAME }}</option>
        </select>
      </div>

      <!-- REPEATABLE -->
      <div v-else-if="param.type === 'repeatable'">
        <div v-for="(repeat_param, key) in Object.entries(element.params[param.name])">
          <hr/>
          <div class="flex justify-between items-center">
            <label>-- {{ (key+1) }} --</label>
            <button v-if="key != 0 && (key+1) == Object.entries(element.params[param.name]).length" type="button" @click="removeRepeatableField(param.name,key)" class="mt-2 w-auto">
              <span class="material-icons-outlined text-red-500">close</span>
            </button>
          </div>

          <form-builder-element-params  :key="param.name+key" :element="element" :params="param.fields" :repeat_name="param.name" :index="key" :databases="databases"></form-builder-element-params>
        </div>

        <div class="flex justify-end">
          <button type="button" @click="addRepeatableField(param.name)" class="em-tertiary-button mt-2 w-auto">{{ translate('COM_EMUNDUS_ONBOARD_PARAMS_ADD_REPEATABLE') }}</button>
        </div>
      </div>

      <!-- INPUT (TEXT,NUMBER) -->
      <input v-else-if="repeat_name !== ''" :type="param.type" v-model="element.params[repeat_name][index_name][param.name]" class="em-w-100" :placeholder="translate(param.placeholder)"/>
      <input v-else :type="param.type" v-model="element.params[param.name]" class="em-w-100" :placeholder="translate(param.placeholder)"/>

      <!-- HELPTEXT -->
      <label v-if="param.helptext !== ''" style="font-size: small">{{ translate(param.helptext) }}</label>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */

/* IMPORT YOUR SERVICES */
import formBuilderService from '../../../services/formbuilder';
import IncrementalSelect from "@/components/IncrementalSelect.vue";
import FormBuilderCreateDocument from "@/components/FormBuilder/FormBuilderCreateDocument.vue";

export default {
  name: "FormBuilderElementParams",
  components: {FormBuilderCreateDocument},
  props: {
    element: {
      type: Object,
      required: false
    },
    params: {
      type: Array,
      required: false
    },
    databases: {
      type: Array,
      required: false
    },
    repeat_name: {
      type: String,
      required: false,
      default: ''
    },
    index: {
      type: Number,
      required: false,
      default: 0
    },
    profile_id: {
      type: Number,
      required: false,
      default: 0
    }
  },
  data: () => ({
    databasejoin_description: null,
    reloadOptions: 0,
    reloadOptionsCascade: 0,

    idElement: 0,
    loading: false,
    //for adding fileupload type
    isActive: false
  }),
  created() {
    this.params.forEach((param) => {
      if(param.type === 'databasejoin'){
        if(this.sysadmin){
          this.loading = true;
          formBuilderService.getAllDatabases().then((response) => {
            param.options = response.data.data;
            this.reloadOptions += 1;
            if(this.element.params['join_db_name'] != ""){
              this.updateDatabasejoinParams();
            }
            this.loading = false;
          });
        } else {
          param.options = this.databases;
          if(this.element.params['join_db_name'] != ""){
            this.updateDatabasejoinParams();
          }
        }
      }

      if (param.type === 'sqldropdown') {
        this.updateSqlDropdownOptions(param);
      }
    })
  },
  methods: {
    updateSqlDropdownOptions(param) {
      this.loading = true;
      formBuilderService.getSqlDropdownOptions(param.table,param.key,param.value,param.translate).then((response) => {
        param.options = response.data;
        this.loading = false;
      });
    },
    updateDatabasejoinParams(){
      if (!this.sysadmin) {
        const index = this.databases.map(e => e.database_name).indexOf(this.element.params['join_db_name']);

        if (index !== -1) {
          let database = this.databases[index];
          this.element.params['join_key_column'] = database.join_column_id;
          this.element.params['database_join_where_sql'] = 'order by {thistable}.' + database.join_column_id;
          if (database.translation == 1) {
            this.element.params['join_val_column'] = database.join_column_val + '_fr';
            this.element.params['join_val_column_concat'] = "{thistable}." + database.join_column_val + "_{shortlang}";
          } else {
            this.element.params['join_val_column'] = database.join_column_val;
            this.element.params['join_val_column_concat'] = "";
          }
          this.databasejoin_description = this.databases[index].description;
        } else {
          let index = this.params.map(e => e.name).indexOf('join_db_name');
          let new_option = {
            label: this.element.params['join_db_name'],
            database_name: this.element.params['join_db_name'],
          }
          this.params[index].options.push(new_option);
          setTimeout(() => {
            document.getElementById('join_db_name').disabled = true;
          },500)
        }
      } else {
        formBuilderService.getDatabaseJoinOrderColumns(this.element.params['join_db_name']).then((response) => {
          let index = this.params.map(e => e.name).indexOf('join_key_column');
          this.params[index].options = response.data.data;
          if(this.element.params['join_key_column'] == '') {
            this.element.params['join_key_column'] = this.params[index].options[0].COLUMN_NAME;
          }

          index = this.params.map(e => e.name).indexOf('join_val_column');
          this.params[index].options = response.data.data;
          if(this.element.params['join_val_column'] == '') {
            this.element.params['join_val_column'] = this.params[index].options[0].COLUMN_NAME;
          }

          this.reloadOptionsCascade += 1;
        });
      }
    },
    EventNewDocForm() {
      this.isActive = !this.isActive;
      this.$emit('openNewDocForm');
    },

    reloadComponent(document) {
      if (document) {
        this.params.forEach((param) => {
          this.updateSqlDropdownOptions(param);
        });
      }
      this.EventNewDocForm();
    },
    addRepeatableField(param) {
      let index = Object.entries(this.element.params[param]).length;
      this.element.params[param][param+index] = {};
      //this.element.params[param][param+index] = this.element.params[param][param+'0'];

      this.$forceUpdate();
    },
    removeRepeatableField(param,key) {
      delete this.element.params[param][param+key];
      this.$forceUpdate();
    },
  },
  computed: {
    sysadmin: function(){
      return parseInt(this.$store.state.global.sysadminAccess);
    },
    index_name: function() {
      return this.repeat_name !== '' ? this.repeat_name+this.index : '';
    }
  }
}

</script>


<style scoped>
.collapsible {
  background-color: #eee;
  color: #444;
  cursor: pointer;
  padding: 18px;
  width: 100%;
  border: none;
  text-align: left;
  outline: none;
  font-size: 15px;
}

.collapsible.active, .collapsible:hover {
  background-color: #ccc;
}

.content {
  padding: 0 18px;
  display: none;
  overflow: hidden;
  background-color: #f1f1f1;
}

.content[style*="display: block"] {
  display: block !important ;
}
</style>