<template>
  <div>
      <label >{{translate(columnNameLabel)}}</label>
       <select class="list-vue-select em-mt-4" v-if="filterType =='dropdown'" v-model="filterValue" >
           <option value="all"> {{translate('COM_EMUNDUS_ALL')}}</option>
           <option v-for="(data,index) in filterDatas" :key="data+'_'+index" :value="data"> {{data}}</option>
       </select>

       <input type="text"  placeholder="Good day " v-if="filterType =='field'" v-model="filterValue"/>
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
            type:String,
            require:true
        },
        columnNameLabel:{
            type:String,
            required:true
        }
    },
    data: ()=>({
        filterValue:''
    }),
    created() {
    },
    watch : {
        filterValue: function(val){

            this.$emit('filterValue', val,this.columnName);

        }
    }

}
</script>

<style scoped>

</style>
