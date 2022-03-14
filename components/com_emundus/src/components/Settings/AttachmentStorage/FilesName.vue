<template>
  <div class="em-mt-16">
    <div class="em-name-preview em-mb-16">FNUM-DOCUMENT_TYPE-APPLICANT_NAME</div>

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

      <div v-for="tag in selectedTags">
        <span class="em-tag-preview em-ml-16">{{tag.label}}</span>
      </div>
    </div>

    <div class="em-flex-row em-mt-16">
      <span>{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_SEPARATOR') }} : </span>
      <div class="em-ml-16 em-separator em-pointer" :class="selectedSeparator == separator ? 'em-selected-separator' : ''" v-for="separator in separators" @click="selectedSeparator = separator">
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
            label: 'FNUM',
            value: 'fnum'
          }
      ],
      separators: [
          '-',
          '_'
      ],
      selectedTags: [],
      selectedSeparator: '-'
    }
  },
  created() {},
  methods: {
    addTag(newTag){
      const tag = {
        label: newTag,
        value: newTag.toLowerCase()
      }
      this.tags.push(tag);
      this.selectedTags.push(tag);
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
.multiselect__select::before{
  display: none !important;
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
