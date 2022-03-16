<template>
  <div class="em-mt-16">
    <div class="em-flex-row em-flex-space-between">
      <div class="em-h4 em-mb-16">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_NAME_WRITING') }}</div>
      <div class="em-flex-row">
        <div class="em-mr-16">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_NAME_TAGS_LIST') }}</div>
        <div class="em-pointer em-blue-500-color" @click="resetName">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_NAME_RESET') }}</div>
      </div>
    </div>

    <div class="em-name-preview em-mb-16 em-flex-row">
      <div v-for="(tag,index) in selectedTags" class="em-flex-row">
        <span>{{tag.value}}</span>
        <div v-if="(index + 1) !== selectedTags.length">{{selectedSeparator}}</div>
      </div>
    </div>

    <div class="em-flex-row">
      <multiselect
          v-model="new_tag"
          label="label"
          track-by="value"
          :options="tags"
          :multiple="true"
          :taggable="true"
          :tag-placeholder="'Ajouter une balise'"
          @tag="addTag"
          select-label=""
          selected-label=""
          deselect-label=""
          :placeholder="'Ajouter une balise'"
      ></multiselect>

      <div v-for="(tag,index) in selectedTags" :key="index" class="em-ml-16 em-flex-row em-tag-preview">
        <span>{{tag.label}}</span>
        <span class="material-icons em-pointer em-ml-8" @click="removeTag(tag.value)">close</span>
      </div>
    </div>

    <div class="em-flex-row em-mt-16">
      <span>{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_SEPARATOR') }} : </span>
      <div class="em-ml-16 em-separator em-pointer" :class="selectedSeparator === separator ? 'em-selected-separator' : ''" v-for="separator in separators" @click="selectedSeparator = separator">
        <span>{{ separator }}</span>
      </div>
    </div>

  </div>
</template>

<script>
import Multiselect from 'vue-multiselect';

export default {
  name: "FilesName",
  components: {
    Multiselect
  },
  data() {
    return {
      loading: false,

      new_tag: '',
      tags: [
          {
            label: 'N° de dossier',
            value: '[FNUM]'
          },
          {
            label: 'Nom du candidat',
            value: '[APPLICANT_NAME]'
          },
          {
            label: 'Type de document',
            value: '[DOCUMENT_TYPE]'
          }
      ],
      separators: [
          '-',
          '_'
      ],
      selectedTags: [],
      selectedSeparator: ''
    }
  },
  created() {
    if(this.selectedTags.length === 0){
      this.resetName();
    }
    if(this.selectedSeparator === ''){
      this.selectedSeparator = '-';
    }
  },
  methods: {
    addTag(newTag){
      const tag = {
        label: newTag,
        value: newTag.toLowerCase()
      }
      this.tags.push(tag);
      this.selectedTags.push(tag);
    },

    removeTag(tag){
      let tag_found = this.selectedTags.findIndex(function(element, index) {
        if(element.value === tag)
          return true;
      });

      this.selectedTags.splice(tag_found,1);
    },

    resetName(){
      this.selectedTags = [];
      this.selectedTags.push(
          {
            label: 'N° de dossier',
            value: '[FNUM]'
          },
          {
            label: 'Type de document',
            value: '[DOCUMENT_TYPE]'
          }
      );
    }
  },

  watch:{
    new_tag: function(value){
      if(value !== '') {
        const tag = value[0];
        this.selectedTags.push(tag);
      }

      this.new_tag = '';
    }
  }
}
</script>

<style scoped>
.em-name-preview{
  background: rgba(121, 182, 251, 0.25);
  padding: 16px 8px;
  border-radius: 4px;
}
.multiselect{
  width: 20%;
}
.em-tag-preview{
  padding: 16px;
  background: rgba(121,182,251,.25);
  border-radius: 4px;
  height: 50px;
  display: flex;
  align-items: center;
}
.em-separator{
  border-radius: 4px;
  padding: 4px 8px;
  background: #cecece;
  height: auto;
}
.em-selected-separator{
  background: rgba(121,182,251,.25);
}
</style>
