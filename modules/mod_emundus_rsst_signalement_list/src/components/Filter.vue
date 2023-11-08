<template>
  <div>
    <select v-if="filterType =='groupBy'" class="list-vue-select em-mt-4 em-mr-16 em-input" style="width: max-content"
            v-model="groupByCriteriaValue">
      <option value="all" selected> Grouper par</option>
      <option v-for="data in filterDataGroupBy" :key="data.id" :value="data.column_name">{{ data.label }}</option>
    </select>
    <select v-else-if="filterType =='dropdown'" class="list-vue-select em-mt-4 em-mr-16 em-input" v-model="filterValue"
            style="width: max-content">
      <option value="all" selected> {{ translate(columnNameLabel) }}</option>
      <option v-for="(data,index) in filterDatas" :key="data+'_'+index" :value="data"> {{
          texteFromValue(data)
        }}
      </option>
    </select>

    <input v-else-if="filterType =='field'" type="text" placeholder="Good day " v-model="filterValue"
           class="list-vue-input em-mt-4 em-mr-16 em-input" :placeholder="translate(columnNameLabel)"/>
  </div>
</template>

<script>
export default {
  name: "Filter",
  props: {
    filterType: {
      type: String,
      required: true,
    },
    filterDatas: {
      type: Array,
      required: true
    },
    columnName: {
      type: String,
      require: false
    },
    columnNameLabel: {
      type: String,
      required: false
    },


  },
  data: () => ({
    filterValue: '',
    groupByCriteriaValue: 'all'
  }),
  created() {
    this.filterValue = this.filterType == 'dropdown' ? 'all' : '';

  },
  methods: {
    texteFromValue(val) {

      let texte = '';
      switch (val) {
        case 'a_faire':
          texte = 'À faire';
          break;
        case 'en_cours':
          texte = 'En cours';
          break;
        case 'fait' :
          texte = 'Fait';
          break;
        case 'sans_objet' :
          texte = 'Sans objet';
          break;
        case '1' :
          texte = 'Publié';
          break;
        case '0' :
          texte = 'Non publié';
          break;
        default:
          texte = val;

      }
      return texte;
    },
  },
  computed: {
    filterDataGroupBy() {
      return this.filterDatas.filter((data) => {
        return JSON.parse(data.params).filter_groupby != -1 && data.data.column_name != 'id';
      });
    }
  },
  watch: {
    filterValue: function (val) {
      this.$emit('filterValue', val, this.columnName);
    },
    groupByCriteriaValue: function (val) {
      this.$emit('groupByCriteriaValue', val)
    }
  }

}
</script>

<style scoped lang="scss">
.list-vue-select, .list-vue-input {
  height: 42px;
}

.list-vue-select {
  max-width: 206px !important;
}
</style>
