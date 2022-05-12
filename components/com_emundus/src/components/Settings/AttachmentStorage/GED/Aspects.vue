<template>
  <section>
    <div class="em-h4 em-mb-16">{{ translate('COM_EMUNDUS_ATTACHMENT_STORAGE_GED_ALFRESCO_ASPECTS') }}</div>
    <div id="no-aspects" v-if="aspects.length < 1 && upload">
      <!--Upload aspect file -->
      <input type="file" id="aspect-file" accept=".xml" />
      <div class="em-primary-button" @click="uploadAspectFile">{{ translate('COM_EMUNDUS_ATTACHMENT_STORAGE_GED_ALFRESCO_ASPECTS_UPLOAD') }}</div>
    </div>
    <div id="aspects" v-else>
      <div class="em-h5 em-mb-16">{{ translate('COM_EMUNDUS_ATTACHMENT_STORAGE_GED_ALFRESCO_ASPECTS_MAPPING') }}</div>
      <div v-for="aspect in aspects" :key="aspect.name" class="em-mb-16">
        <div class="em-flex-row em-flex-space-between">
          <input type="text" v-model="aspect.label" disabled>
          <span class="material-icons">sync_alt</span>
          <select v-model="aspect.mapping" @change="updateAspectMapping">
            <option v-for="tag in tags" :key="tag.id" :value="tag.id">{{ tag.tag }}</option>
          </select>
        </div>
      </div>

      <div v-if="upload">
        <div class="em-h6 em-mb-16">{{ translate('COM_EMUNDUS_ATTACHMENT_STORAGE_GED_ALFRESCO_ASPECTS_UPLOAD_ADD_FROM_FILE') }}</div>
        <div id="add-aspects-from-file" class="em-flex-row">
          <input type="file" id="update-aspect-file" accept=".xml" />
          <div class="em-primary-button" @click="updateAspectListFromFile">{{ translate('COM_EMUNDUS_ATTACHMENT_STORAGE_GED_ALFRESCO_ASPECTS_UPLOAD_ADD') }}</div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import syncService from "../../../../services/sync";

export default {
  name: 'Aspects',
  props: {
    aspects: {
      type: Array,
      default: []
    },
    upload: {
      type: Boolean,
      default: true
    },
  },
  data() {
    return {
      tags: []
    };
  },
  mounted() {
    this.getTags();
  },
  methods: {
    getTags() {
      syncService.getSetupTags().then(response => {
        this.tags = response.data;
      });
    },
    uploadAspectFile() {
      let file = document.getElementById('aspect-file').files[0];
      syncService.uploadAspectFile(file).then(response => {
        this.aspects = response.data.data;
        this.$emit('update-aspects', this.aspects);
      });
    },
    updateAspectListFromFile() {
      let file = document.getElementById('update-aspect-file').files[0];
      syncService.updateAspectListFromFile(file).then(response => {
        this.aspects = response.data.data;
        this.$emit('update-aspects', this.aspects);
      });
    },
    updateAspectMapping(event) {
      this.$emit('update-aspects', this.aspects);
    }
  }
}
</script>

<style lang="scss">
#add-aspects-from-file {
  input {
    margin: 0 10px 0 0;
  }

  .em-primary-button {
    width: fit-content;
  }
}
</style>