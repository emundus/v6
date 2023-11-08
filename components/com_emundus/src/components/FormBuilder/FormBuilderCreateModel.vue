<template>
  <div id='form-builder-create-model' class="em-flex-column em-flex-space-between em-w-100">
    <div class="em-w-100">
      <div class="em-flex-row em-flex-space-between em-p-16">
        <p>{{ translate('COM_EMUNDUS_FORM_BUILDER_MODEL_PROPERTIES') }}</p>
        <span class="material-icons-outlined em-pointer" @click="$emit('close')">close</span>
      </div>

      <div v-if="!loading" id="model-properties" class="em-flex-column em-flex-start em-p-16 em-text-align-left">
        <p class="em-main-500-color">{{ translate('COM_EMUNDUS_FORM_BUILDER_MODEL_PROPERTIES_INTRO') }}</p>
        <label for="page-model-title" class="em-mt-16 em-text-align-left em-w-100">{{
            translate('COM_EMUNDUS_FORM_BUILDER_MODEL_INPUT_LABEL')
          }}</label>
        <input id="page-model-title" class="em-w-100 em-mb-16" type="text" v-model="modelTitle"/>
        <p v-if="alreadyExists" class="em-red-500-color">
          {{ translate('COM_EMUNDUS_FORM_BUILDER_MODEL_WITH_SAME_TITLE_EXISTS') }}</p>
      </div>
      <div v-else class="em-w-100 em-flex-row em-flex-center">
        <div class="em-loader"></div>
      </div>
    </div>
    <div class="em-flex-row em-flex-space-between actions em-w-100">
      <button
          class="em-primary-button em-m-16"
          @click="addFormModel()"
          :disabled="modelTitle.length < 1 || loading"
          :class="{'em-color-white em-gray-bg em-w-100 em-p-8-12 em-border-radius': modelTitle.length < 1 || loading,}"
      >
        {{ translate('COM_EMUNDUS_FORM_BUILDER_SECTION_PROPERTIES_SAVE') }}
      </button>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';
import Swal from "sweetalert2";

export default {
  props: {
    page: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      modelTitle: '',
      models: [],
      alreadyExists: false,
      loading: false
    }
  },
  mounted() {
    this.getModels();
  },
  methods: {
    getModels() {
      formBuilderService.getModels().then((response) => {
        if (response.status) {
          this.models = response.data;
        }
      });
    },
    checkTitleNotAlreadyExists() {
      const modelExists = this.models.filter((model) => {
        return model.label[this.shortDefaultLang] === this.modelTitle.trim();
      });

      this.alreadyExists = modelExists.length > 0;
    },
    addFormModel() {
      this.loading = true;
      this.modelTitle = this.modelTitle.trim();

      if (this.modelTitle.length < 1) {
        Swal.fire({
          type: 'warning',
          title: this.translate('COM_EMUNDUS_FORM_BUILDER_MODEL_MUST_HAVE_TITLE'),
          reverseButtons: true,
          customClass: {
            title: 'em-swal-title',
            confirmButton: 'em-swal-confirm-button',
            actions: 'em-swal-single-action',
          }
        });

        this.loading = false;
        return;
      }

      const modelExists = this.models.filter((model) => {
        return model.label[this.shortDefaultLang] === this.modelTitle;
      });

      this.alreadyExists = modelExists.length > 0;

      if (!this.alreadyExists) {
        formBuilderService.addFormModel(this.page, this.modelTitle).then((response) => {
          if (!response.status) {
            this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_SAVE_AS_MODEL_FAILURE'), response.msg);
          } else {
            Swal.fire({
              type: 'success',
              title: this.translate('COM_EMUNDUS_FORM_BUILDER_SAVE_AS_MODEL_SUCCESS'),
              reverseButtons: true,
              customClass: {
                title: 'em-swal-title',
                confirmButton: 'em-swal-confirm-button',
                actions: 'em-swal-single-action',
              }
            });
          }
          this.loading = false;
          this.$emit('close');
        });
      } else {
        this.replaceFormModel(modelExists[0].id, this.modelTitle);
      }
    },
    replaceFormModel(model_id, label) {
      formBuilderService.addFormModel(this.page, label).then((response) => {
        if (!response.status) {
          this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_SAVE_AS_MODEL_FAILURE'), response.msg);

          this.$emit('close');
        } else {
          const modelIds = [model_id];
          formBuilderService.deleteFormModelFromId(modelIds).then(() => {
            this.$emit('close');
          })
        }

        if (this.loading) {
          this.loading = false;
        }
      });
    }
  },
  watch: {
    modelTitle: function (val, oldVal) {
      if (val != oldVal) {
        this.checkTitleNotAlreadyExists();
      }
    }
  }
}
</script>

<style lang="scss">
#form-builder-create-model {
  #model-properties {
    height: fit-content;
  }

  .em-primary-button:disabled {
    cursor: not-allowed;
    border-color: var(--grey-color);
    background: var(--grey-color);

    &:hover {
      color: white;
    }
  }
}
</style>
