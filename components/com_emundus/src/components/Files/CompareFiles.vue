<template>
  <span :id="'compareFiles'" :class="'full-width-modal'">
    <modal
        :name="'compareFiles'"
        height="auto"
        transition="fade"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
        @closed="beforeClose"
    >
      <div id="compare-files-container">
        <header id="compare-files-container-header-1" class="em-text-align-center em-p-8">
          <div id="close-modal-wrapper" class="em-pointer em-flex-row" @click="closeModal()">
            <span class="material-icons-outlined em-pointer">chevron_left</span>
            <span>{{ translate('COM_EMUNDUS_MODAL_COMPARISON_BACK_BUTTON') }}</span>
          </div>
          <p>{{ translate(title) }}</p>
        </header>
        <div class="em-w-100 em-h-100 em-flex-row">
          <div id="default-file-container" class="left-view em-w-50">
            <header class="em-flex-row em-flex-space-between">
              <div>
                <span>{{ defaultFile.applicant }} - {{ defaultFile.fnum }}</span>
              </div>
              <div class="prev-next-files">

              </div>
            </header>
          </div>
          <div v-if="selectedFileToCompareWith == null" id="files-to-compare-with-container" class="right-view em-w-50">
            <header class="em-text-align-center">
              <span>{{ translate('COM_EMUNDUS_MODAL_COMPARISON_SELECT_A_FILE_TO_COMPARE_TO') }}</span>
            </header>
            <div>
              <slot name="files-to-compare-with">
                <div v-for="file in files" :key="file.id" class="em-flex-row em-flex-space-between">
                  <span>{{ file.applicant }} - {{ file.fnum }}</span>
                  <span class="material-icons-outlined em-pointer"
                        @click="selectedFileToCompareWith = file">arrow_right</span>
                </div>
              </slot>
            </div>
          </div>
          <div v-else id="files-to-compare-with-container" class="right-view em-w-50">
            <header class="em-flex-row em-flex-space-between">
              <div>
                <span>{{ selectedFileToCompareWith.applicant }} - {{ selectedFileToCompareWith.fnum }}</span>
              </div>
              <div class="prev-next-files">

              </div>
            </header>
        </div>
      </div>
      </div>
    </modal>
  </span>
</template>

<script>
export default {
  name: 'CompareFiles',
  props: {
    defaultFile: {
      type: Object,
      required: true
    },
    files: {
      type: Array,
      default: []
    },
    title: {
      type: String,
      default: 'COM_EMUNDUS_MODAL_COMPARISON_HEADER_TITLE'
    },
  },
  data() {
    return {
      defaultFile: this.defaultFile,
      files: this.files,
      selectedFileToCompareWith: null
    }
  },
  created() {

  },
  methods: {
    closeModal() {
      this.$modal.hide('compareFiles');
    },
    beforeClose() {
      this.selectedFileToCompareWith = null;
    }
  }
}

</script>

<style scoped>
#compare-files-container-header-1 {
  position: relative;
  background-color: var(--main-800);
  color: var(--neutral-0) !important;

  p {
    color: var(--neutral-0) !important;
  }
}

#close-modal-wrapper {
  position: absolute;
  top: 8px;
  left: 8px;
}
</style>