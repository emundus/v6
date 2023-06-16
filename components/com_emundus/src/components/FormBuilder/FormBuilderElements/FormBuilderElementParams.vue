<template>
  <div>
    <div v-for="param in params" v-if="(param.published && !param.sysadmin_only) || (sysadmin && param.sysadmin_only && param.published)" class="form-group em-mb-8">
      <label>{{ translate(param.label) }}</label>

      <!-- DROPDOWN -->
      <div v-if="param.type === 'dropdown'">
        <select v-model="element.params[param.name]" class="em-w-100">
          <option v-for="option in param.options" :value="option.value">{{ translate(option.label) }}</option>
        </select>
      </div>

      <!-- TEXTAREA -->
      <textarea v-else-if="param.type === 'textarea'" v-model="element.params[param.name]" class="em-w-100"></textarea>

      <!-- DATABASEJOIN -->
      <div v-else-if="param.type === 'databasejoin'">
        <select v-model="element.params[param.name]" :key="reloadOptions" :id="param.name" @change="updateDatabasejoinParams" class="em-w-100" :class="databasejoin_description ? 'em-mb-4' : ''">
          <option v-for="option in param.options" :value="option.database_name">{{ option.label }}</option>
        </select>
        <label v-if="databasejoin_description" style="font-size: small">{{ databasejoin_description }}</label>
      </div>
      <div v-else-if="param.type === 'databasejoin_cascade'">
        <select v-model="element.params[param.name]" :key="reloadOptionsCascade" class="em-w-100">
          <option v-for="option in param.options" :value="option.COLUMN_NAME">{{ option.COLUMN_NAME }}</option>
        </select>
      </div>

      <!-- SQL -->
      <div v-else-if="param.type === 'sql'">
          <select v-model="element.params[param.name]" :key="param.id + '-sqlSelect'" class="em-w-100">
            <option v-for="option in param.options" :value="option.value">{{ option.label}}</option>
          </select>
      </div>

      <!-- SUBFORM -->
      <div v-else-if="param.type === 'subform'">
        <button @click="addRow(param.name)" :key="param.id + '-addButton' ">Add</button>
        <div v-for="(element_sub_parameters, element_sub_name) in element.params[param.name]" :key="element.id + '-' + param.name" class="subform">
          <FormBuilderElementParams :key="element_sub_name" :element="{params:element_sub_parameters}" :databases="null" :params="param.subparams"/>
          <button @click="deleteRow(param.name, element_sub_name)" :key="element_sub_name+ '-deleteButton'">Delete</button>
        </div>
      </div>

      <!-- INPUT (TEXT,NUMBER) -->
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

export default {
  name: "FormBuilderElementParams",
  components: {},
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
    }
  },
  data: () => ({
    databasejoin_description: null,
    reloadOptions: 0,
    reloadOptionsCascade: 0,

    loading: false,
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
      else if (param.type === 'sql')
      {
        if (param.key_field && param.value_field && param.plugin_name)
        {
          this.prepareSqlFieldData(param);
        }
      }
    })
  },
  methods: {
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

    addRow(paramName) {

      const subformObject = this.getSubObject(paramName);

      const new_object = this.prepareNewSubObject(subformObject.subparams);
      const length = Object.keys(this.element.params[paramName]).length;
      const element_name = paramName+length;

      this.$set(this.element.params[paramName], element_name, new_object);
    },

    deleteRow(paramName, element_sub_name) {

      const subObject = this.getSubObject(paramName);

      if (Object.keys(this.element.params[paramName]).length > subObject.min)
      {
        this.$delete(this.element.params[paramName], element_sub_name);
      }
    },

    getSubObject(name)
    {
      let subObject = {};

      this.params.forEach((element) =>
      {
        if (element.name === name)
        {
          subObject = element;
        }
      })

      return subObject;
    },

    prepareNewSubObject(allParams)
    {
      const new_object = {};

      allParams.forEach((param) =>
      {
        if (param.type === 'sql')
        {
          new_object[param.name] = param.default ? param.default : param.options[0].value;
        }
        else
        {
          new_object[param.name] = param.default ? param.default : '';
        }
      });

      return new_object;
    },

    prepareSqlFieldData(params)
    {
      const valueColumn = params.key_field;
      const labelColumn = params.value_field;
      const plugin = params.plugin_name;
      const field_name = params.name;

      formBuilderService.getDataForSqlField(plugin, field_name).then((response) =>
      {
        if (response.data.data)
        {
          this.$set(params, 'options', []);
          response.data.data.forEach((row, index) =>
          {
            let option = {};
            option.value = row[valueColumn];
            option.label = row[labelColumn];
            this.$set(params.options, index, option);
          });
        }
      });
    },
  },
  computed: {
    sysadmin: function(){
      return parseInt(this.$store.state.global.sysadminAccess);
    }
  }
}
</script>

<style scoped lang="css">

</style>
