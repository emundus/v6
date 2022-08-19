<template>
  <div id="form-builder-document-formats">
    <p id="form-builder-document-title" class="em-text-align-center em-w-100 em-p-16">
      {{ translate('COM_EMUNDUS_FORM_BUILDER_FORMATS') }}
    </p>
    <draggable
        v-model="formats"
        class="draggables-list"
        :group="{ name: 'form-builder-documents', pull: 'clone', put: false }"
        :sort="false"
        :clone="setCloneFormat"
        @end="onDragEnd"
    >
      <transition-group>
        <div
            v-for="format in publishedFormats"
            :key="format.id"
            class="em-flex-row em-flex-space-between draggable-element em-mt-8 em-mb-8 em-p-16"
            :style="format.value == 'other' ? 'cursor: pointer' : ''"
            @click="onClickOnFormat(format)"
        >
          <span class="material-icons-outlined">{{ format.icon }}</span>
          <span class="em-w-100 em-p-16">{{ translate(format.name) }}</span>
          <span v-show="format.value != 'other'" class="material-icons-outlined"> drag_indicator </span>
        </div>
      </transition-group>
    </draggable>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import campaignService from "../../services/campaign";
import formBuilderMixin from "../../mixins/formbuilder";

export default {
  components: {
    draggable
  },
  props: {
    profile_id: {
      type: Number,
      required: true
    }
  },
  mixins: [formBuilderMixin],
  data() {
    return {
      formats: [],
      cloneFormat: null
    }
  },
  created() {
    this.formats = this.getFormats();
  },
  methods: {
    getFormats() {
      return require('../../../data/form-builder-formats.json');
    },
    onClickOnFormat(format) {
      if (format.value == 'other') {
        const title = this.translate('COM_EMUNDUS_FORM_BUILDER_ADD_FORMAT');
        const text = this.translate('COM_EMUNDUS_FORM_BUILDER_ADD_FORMAT_DESC');
        const cancel = this.translate('COM_EMUNDUS_FORM_BUILDER_CANCEL');
        const confirm = this.translate('COM_EMUNDUS_FORM_BUILDER_CONTACT_ADD_FORMAT');

        this.swalConfirm(title, text, confirm, cancel, () => {
          // TODO: specify contact mail
          const contact = "support@emundus.fr";
          window.open("mailto:" + contact);
        });
      }
    },
    setCloneFormat(format) {
      this.cloneFormat = format;
    },
    onDragEnd(event) {
      if (this.cloneFormat.value === 'other') {
        return;
      }

      const to = event.to;
      if (to === null) {
        return;
      }

      const newDocument = {
        id: null,
        type: {},
        mandatory: to.id == "required-documents",
        nbmax: 1,
        description: {
          fr: '',
          en: ''
        },
        name: {
          fr: 'Nouveau Document',
          en: 'New document'
        },
        selectedTypes: {},
        minResolution: {
          width: 0,
          height: 0
        },
        maxResolution: {
          width: 0,
          height: 0
        }
      };

      const data = {
        document: JSON.stringify(newDocument),
        types: JSON.stringify([this.cloneFormat.extensions]),
        pid: this.profile_id,
        isModeleAndUpdate: true
      }

      campaignService.updateDocument(data, true).then((response) => {
        if (response.status) {
          this.$emit('document-created');
        }
      });
    }
  },
  computed: {
    publishedFormats() {
      return this.formats.filter(format => format.published);
    }
  }
}
</script>

<style lang="scss">
#form-builder-document-formats {
  #form-builder-document-title {
    border-bottom: 1px solid black;
  }

  .draggable-element {
    width: 258px;
    height: 48px;
    font-size: 14px;
    background-color: #fafafa;
    border: 1px solid #f2f2f3;
    cursor: grab;
  }
}
</style>
