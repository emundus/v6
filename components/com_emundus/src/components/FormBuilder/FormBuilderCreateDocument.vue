<template>
  <div id="form-builder-create-document">
    <select id="document-model">
      <option v-for="(document, index) in models" :value="document.id">{{ document.name.fr }}</option>
    </select>
    <input type="text" id="title" v-model="title">
    <input type="textarea" id="description" v-model="description">
    <input type="number" id="nbmax" v-model="maxFiles">
    <select id="file-types">
      <option></option>
    </select>
  </div>
</template>

<script>
import formService from '../../services/form';

export default {
  name: 'FormBuilderCreateDocument',
  data() {
    return {
      models: [],
      title: "",
      description: "",
      maxFiles: 1,
    };
  },
  created(){
    this.getDocumentModels();
  },
  methods: {
    getDocumentModels() {
      formService.getDocumentModels().then(response => {
        if (response.status) {
          this.models = response.data;
        }
      });
    }
  }
}
</script>