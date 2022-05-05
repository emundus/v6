<template>
  <section>
    <div class="em-h4 em-mb-16">{{ translate('COM_EMUNDUS_ATTACHMENT_STORAGE_GED_ALFRESCO_ASPECTS') }}</div>
    <div id="no-aspects" v-if="aspects.length < 1">
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

      <input type="file" id="update-aspect-file" accept=".xml" />
      <div class="em-primary-button" @click="updateAspectListFromFile">{{ translate('COM_EMUNDUS_ATTACHMENT_STORAGE_GED_ALFRESCO_ASPECTS_UPLOAD_UPDATE') }}</div>
    </div>
  </section>
</template>

<script>
import storageService from "../../../../services/storage";

export default {
  name: 'Aspects',
  props: {
    aspects: {
      type: Array,
      default: []
    }
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
      storageService.getSetupTags().then(response => {
        this.tags = response.data;
      });
    },
    uploadAspectFile() {
      let file = document.getElementById('aspect-file').files[0];
      storageService.uploadAspectFile(file).then(response => {
        this.aspects = response.data.data;
        this.$emit('update-aspects', this.aspects);
      });
    },
    updateAspectListFromFile() {
      let file = document.getElementById('update-aspect-file').files[0];
      storageService.updateAspectListFromFile(file).then(response => {
        this.aspects = response.data.data;
        this.$emit('update-aspects', this.aspects);
      });
    },
    updateAspectMapping(event) {
      console.log('udpate aspects');
      this.$emit('update-aspects', this.aspects);
    }
  }
}
</script>