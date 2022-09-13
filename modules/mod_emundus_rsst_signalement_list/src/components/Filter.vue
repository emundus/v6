<template>
    <div>
        <select class="list-vue-select em-mt-4 em-input" v-if="filterType =='groupBy'"  style="width: max-content" v-model="groupByCriteriaValue">
            <option value="all" selected > Grouper par</option>
            <option v-for="data in filterDataGroupBy" :key="data.id" :value="data.column_name">{{ data.label }}</option>
        </select>
        <select class="list-vue-select em-mt-4 em-input" v-if="filterType =='dropdown'" v-model="filterValue" style="width: max-content">
            <option value="all" selected > {{translate(columnNameLabel) }}</option>
            <!-- la key contient data car il peut y arriver que data.id n'extiste pas et deplus data est forcément un string string -->
            <option v-for="(data,index) in filterDatas" :key="data+'_'+index" :value="data"> {{ texteFromValue(data) }}</option>
        </select>

        <input type="text" placeholder="Good day " v-if="filterType =='field'" v-model="filterValue" class="list-vue-input em-input" :placeholder="translate(columnNameLabel)"/>
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
        groupByCriteriaValue:'all'
    }),
    created() {
        this.filterValue = this.filterType == 'dropdown' ? 'all' :'';

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
                return JSON.parse(data.params).filter_groupby != -1;
            });
        }
    },
    watch: {
        filterValue: function (val) {
            this.$emit('filterValue', val, this.columnName);
        },
        groupByCriteriaValue:function (val) {
            this.$emit('groupByCriteriaValue',val)
        }
    }

}
</script>

<style scoped lang="scss">
.list-vue-select, .list-vue-input {
    height: 35px;
}
.list-vue-select{
    width: 206px!important;

}
</style>
