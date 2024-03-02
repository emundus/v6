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
        <header id="compare-files-container-header-1" class="em-text-align-center">
          <div id="close-modal-wrapper" class="em-pointer em-flex-row" @click="closeModal()">
            <span class="material-icons-outlined em-pointer">chevron_left</span>
            <span>{{ translate('COM_EMUNDUS_MODAL_COMPARISON_BACK_BUTTON') }}</span>
          </div>
          <p>{{ translate(title) }}</p>
        </header>
        <div class="em-w-100 em-h-100 em-flex-row">
          <div id="default-file-container" class="left-view em-w-50">
            <header class="em-flex-row em-flex-space-between compare-files-container-header-2">
              <div>
                <span>{{ defaultFile.applicant }} - {{ defaultFile.fnum }}</span>
              </div>
              <div class="prev-next-files">
                <span class="material-icons-outlined em-pointer">arrow_back</span>
                <span class="material-icons-outlined em-pointer">arrow_forward</span>
              </div>
            </header>
            <div class="scrollable">
              <application-tabs :user="user" :file="defaultFile" :access="access"></application-tabs>
            </div>
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
            <header class="em-flex-row em-flex-space-between compare-files-container-header-2">
              <div>
                <span>{{ selectedFileToCompareWith.applicant }} - {{ selectedFileToCompareWith.fnum }}</span>
              </div>
              <div class="actions">
                <span class="material-icons-outlined em-pointer">arrow_back</span>
                <span class="material-icons-outlined em-pointer">arrow_forward</span>
                <span class="material-icons-outlined em-pointer" @click="selectedFileToCompareWith = null">
                  close
                </span>
              </div>
            </header>
            <div class="scrollable">
              <application-tabs :user="user" :file="selectedFileToCompareWith" :access="access"></application-tabs>
            </div>
        </div>
      </div>
      </div>
    </modal>
  </span>
</template>

<script>
import ApplicationTabs from "./ApplicationTabs.vue";

export default {
  name: 'CompareFiles',
  components: {ApplicationTabs},
  props: {
    user: {
      type: Number,
      required: true
    },
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
      selectedFileToCompareWith: null,
      access: {
        1: {
          'c': true,
          'r': true,
          'u': true,
          'd': false
        },
        4: {
          'c': true,
          'r': true,
          'u': true,
          'd': false
        },
        10: {
          'c': true,
          'r': true,
          'u': true,
          'd': false
        }
      }
    }
  },
  created() {

  }
  ,
  methods: {
    closeModal() {
      this.$modal.hide('compareFiles');
    }
    ,
    beforeClose() {
      this.selectedFileToCompareWith = null;
    }
  }
}

</script>

<style scoped>
#compare-files-container-header-1 {
  padding: 16px 8px;
  position: relative;
  background-color: var(--main-800);
  color: var(--neutral-0) !important;

  p, span, .material-icons-outlined {
    color: var(--neutral-0) !important;
  }

  #close-modal-wrapper {
    position: absolute;
    top: 16px;
    left: 8px;
  }
}

.compare-files-container-header-2 {
  background-color: var(--main-600);
  color: var(--neutral-0) !important;
  padding: 16px 8px;
  height: 54px;

  span, .material-icons-outlined {
    color: var(--neutral-0) !important;
  }
}

.left-view {
  border-right: 1px solid var(--neutral-200);
}
</style>