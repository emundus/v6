<template>
  <div class="control-group fabrikElementContainer">
	  <label class="fabrikLabel" v-html="element.label"></label>
	  <div class="fabrikElement">
    <select v-if="!readonly && !advanced_select" class="fabrikinput em-w-100" :id="'input_' + element.id" :value="value" :name="element.name" v-model="value">
      <option v-if="params.database_join_show_please_select == 1" value="">{{ translate('PLEASE_SELECT') }}</option>
      <option v-for="option in options" :value="option.primary_key">{{ option.value }}</option>
    </select>

    <multiselect
        v-else-if="!readonly && advanced_select"
        v-model="object_value"
        label="value"
        track-by="primary_key"
        :options="options"
        :multiple="false"
        :taggable="false"
        select-label=""
        :placeholder="translate('PLEASE_SELECT')"
        selected-label=""
        deselect-label=""
        :close-on-select="true"
        :clear-on-select="false"
        :searchable="true"
        :allow-empty="false"
    ></multiselect>
      <p v-else>{{ value }}</p>
    </div>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */
import multiselect from 'vue-multiselect';
/* IMPORT YOUR SERVICES */
import fabrik from "com_emundus/src/mixins/fabrik.js";

export default {
  name: "databasejoin",
  components: {
    multiselect
  },
  mixins: [fabrik],
  props: {
    element: {
      type: Object,
      required: true
    },
    value: {
      type: String,
      default: ''
    }
  },
  data: () => ({
    params: {},
    options: [],

    // advanced select
    object_value: {}
  }),
  mounted() {
    this.params = JSON.parse(this.element.params);
    this.getDatabasejoinOptions(this.params.join_db_name, this.params.join_key_column, this.params.join_val_column, this.params.join_val_column_concat, this.params.database_join_where_sql).then(response => {
      if(response.status == 1) {
        this.options = response.options;
        if (parseInt(this.params.advanced_behavior) === 1 && this.value !== '' && this.value != 0) {
          this.object_value = this.options.find(option => option.primary_key === this.value);
        }
      }
    });
  },
  computed: {
    readonly: function(){
      return parseInt(this.params.readonly) === 1;
    },
    advanced_select: function(){
      return parseInt(this.params.advanced_behavior) === 1;
    }
  },
  watch: {
    value: function(value) {
      this.$emit('input', {value: value, name: this.element.name})
    },
    object_value: function(value) {
      this.value = value.primary_key;
    }
  }
}
</script>
