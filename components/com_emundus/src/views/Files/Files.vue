<template>
  <div class="em-mt-16 em-ml-32 em-files">
    <p class="em-h4">{{ translate('COM_EMUNDUS_FILES_'+type.toUpperCase()) }}</p>

    <tabs :type="$props.type"></tabs>
  </div>
</template>

<script>
import Tabs from "@/views/Files/Tabs";

/** SERVICES **/
import filesService from 'com_emundus/src/services/files';

export default {
  name: "Files",
  components: {Tabs},
  props: {
    type: String
  },
  data: () => ({
    count: null,
    files: [],
  }),
  created(){
    if(this.$props.type === 'evaluation') {
      filesService.getFilesToEvaluate().then((response) => {
        console.log(response);
      })
    }
  },
  methods: {}
}
</script>

<style scoped>
.em-files{
  width: calc(100% - 75px) !important;
  margin-left: auto;
}
</style>