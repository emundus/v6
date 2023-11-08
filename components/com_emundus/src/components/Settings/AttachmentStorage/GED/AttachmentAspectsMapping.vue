<template>
  <div id="attachment-aspects-mapping" class="em-mt-16">
    <div id="default-aspects-toggle" class="em-flex-row em-flex-start">
      <div class="em-toggle">
        <input type="checkbox"
               :true-value="true"
               :false-value="false"
               class="em-toggle-check"
               id="default-aspects"
               name="'default-aspects'"
               v-model="aspectsConfig.default"
               @click="aspectsConfig.default = !aspectsConfig.default; saveAttachmentAspects();"
        />
        <strong class="b em-toggle-switch"></strong>
        <strong class="b em-toggle-track"></strong>
      </div>
      <label for="default-aspects">{{ translate('COM_EMUNDUS_ATTACHMENT_STORAGE_DEFAULT_ASPECTS_MAPPING') }}</label>
    </div>

    <div v-if="aspectsConfig.default == false">
      <Aspects :aspects="aspectsConfig.aspects" :upload="false" @update-aspects="saveAttachmentAspects"></Aspects>
    </div>
  </div>
</template>

<script>
import syncService from '../../../../services/sync';
import Aspects from "./Aspects";

export default {
  name: 'AttachmentAspectsMapping',
  components: {Aspects},
  props: {
    attachment: {
      type: Object,
      required: true
    },
  },
  data() {
    return {
      tags: [],
      aspectsConfig: {
        default: true,
        aspects: [],
      },
    }
  },
  created() {
    this.getAttachmentAspectsConfig();
  },
  methods: {
    getAttachmentAspectsConfig() {
      syncService.getConfig('ged').then((response) => {
        if (response.data.data.aspects) {
          this.aspectsConfig.aspects = response.data.data.aspects;
        }
      }).then(() => {
        syncService.getAttachmentAspectsConfig(this.attachment.id).then(response => {
          const data = typeof response.data === "string" || response.data instanceof String ? JSON.parse(response.data) : false;

          if (data) {
            this.aspectsConfig = data;
          }
        });
      });
    },
    saveAttachmentAspects() {
      syncService.saveAttachmentAspectsConfig(this.attachment.id, this.aspectsConfig);
    }
  },
}
</script>

<style>
#default-aspects-toggle label {
  margin: 0 0 0 10px;
}
</style>