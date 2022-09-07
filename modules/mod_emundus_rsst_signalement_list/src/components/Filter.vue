<template>
    <div>
        <select class="list-vue-select em-mt-4 em-input" v-if="filterType =='dropdown'" v-model="filterValue" style="width: max-content">
            <option value="all" selected > {{translate(columnNameLabel) }}</option>
            <option v-for="(data,index) in filterDatas" :key="data+'_'+index" :value="data"> {{ translate(data.toUpperCase()) }}</option>
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
            require: true
        },
        columnNameLabel: {
            type: String,
            required: true
        }
    },
    data: () => ({
        filterValue: ''
    }),
    created() {
        this.filterValue = this.filterType == 'dropdown' ? 'all' :'';
    },

    method: {
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
    watch: {
        filterValue: function (val) {

            this.$emit('filterValue', val, this.columnName);

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
