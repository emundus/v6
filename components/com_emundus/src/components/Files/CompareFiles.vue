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
              <div class="prev-next-files" v-if="this.files.length > 0">
                <span class="material-icons-outlined em-pointer" @click="selectNewFile('default', 'previous')">arrow_back</span>
                <span class="material-icons-outlined em-pointer" @click="selectNewFile('default', 'next')">arrow_forward</span>
              </div>
            </header>
            <div class="scrollable">
              <slot name="before-default-file-tabs">
              </slot>
              <application-tabs :key="defaultFile.id" :user="user" :file="defaultFile" :access="access" :tabs="tabs"></application-tabs>
            </div>
          </div>
          <div v-if="selectedFileToCompareWith == null" id="files-to-compare-with-container" class="right-view em-w-50">
            <header class="em-text-align-center compare-files-container-header-2">
              <span>{{ translate('COM_EMUNDUS_MODAL_COMPARISON_SELECT_A_FILE_TO_COMPARE_TO') }}</span>
            </header>
            <div id="files-to-compare-with-selection" class="scrollable em-p-16">
              <slot name="files-to-compare-with" @open-file="selectedFileToCompareWith = $event">
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
              <div class="actions" v-if="this.files.length > 0">
                <span class="material-icons-outlined em-pointer" @click="selectNewFile('compare', 'previous')">arrow_back</span>
                <span class="material-icons-outlined em-pointer" @click="selectNewFile('compare', 'next')">arrow_forward</span>
                <span class="material-icons-outlined em-pointer" @click="selectedFileToCompareWith = null">
                  close
                </span>
              </div>
            </header>
            <div class="scrollable">
              <slot name="before-compare-file-tabs">
              </slot>
              <application-tabs :key="selectedFileToCompareWith.id" :user="user" :file="selectedFileToCompareWith" :access="access" :tabs="tabs"></application-tabs>
            </div>
        </div>
      </div>
      </div>
    </modal>
  </span>
</template>

<script>
import usersService from "../../services/user";
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
    defaultComparisonFile: {
      type: Object,
      default: null
    },
    files: {
      type: Array,
      default: []
    },
    title: {
      type: String,
      default: 'COM_EMUNDUS_MODAL_COMPARISON_HEADER_TITLE'
    },
    tabs: {
      type: Array,
      default: () => ([
        {
          label: 'COM_EMUNDUS_FILES_APPLICANT_FILE',
          name: 'application',
          access: '1'
        },
        {
          label: 'COM_EMUNDUS_FILES_ATTACHMENTS',
          name: 'attachments',
          access: '4'
        },
        {
          label: 'COM_EMUNDUS_FILES_COMMENTS',
          name: 'comments',
          access: '10'
        },
      ])
    }
  },
  data() {
    return {
      defaultFile: this.defaultFile,
      files: this.files,
      selectedFileToCompareWith: null,
      // TODO: get access from the server
      access: null
    }
  },
  created() {
    usersService.getAllAccessRights().then((response) => {
      this.access = response.data;
    });
    this.addEventListeners();

    if (this.defaultComparisonFile) {
      this.selectedFileToCompareWith = this.defaultComparisonFile;
    }
  },
  methods: {
    addEventListeners() {
      window.addEventListener('openSecondaryFile', (e) => {
        this.selectedFileToCompareWith = e.detail.file;
      });
    },
    removeEventListeners() {
      window.removeEventListener('openSecondaryFile', (e) => {
        this.selectedFileToCompareWith = e.detail.file;
      });
    },
    selectNewFile(fileType, direction = 'next') {
      if (fileType === 'default') {
        const index = this.files.findIndex(file => file.id === this.defaultFile.id);

        if (direction === 'previous') {
          // get previous file
          if (index > 0) {
            this.defaultFile = this.files[index - 1];
          } else {
            this.defaultFile = this.files[this.files.length - 1];
          }
        } else {
          // get next file
          if (index < this.files.length - 1) {
            this.defaultFile = this.files[index + 1];
          } else {
            this.defaultFile = this.files[0];
          }
        }
      } else {
        const index = this.files.findIndex(file => file.id === this.selectedFileToCompareWith.id);

        if (direction === 'previous') {
          // get previous file
          if (index > 0) {
            this.selectedFileToCompareWith = this.files[index - 1];
          } else {
            this.selectedFileToCompareWith = this.files[this.files.length - 1];
          }
        } else {
          // get next file
          if (index < this.files.length - 1) {
            this.selectedFileToCompareWith = this.files[index + 1];
          } else {
            this.selectedFileToCompareWith = this.files[0];
          }
        }
      }

      this.$emit('comparison-file-changed', this.defaultFile, this.selectedFileToCompareWith);
    },

    closeModal() {
      this.$modal.hide('compareFiles');
    },
    beforeClose() {
      this.selectedFileToCompareWith = null;
      this.removeEventListeners();
    }
  }
}

</script>

<style scoped>
#compare-files-container-header-1 {
  height: 54px;
  padding: 16px 8px;
  position: relative;
  background-color: var(--main-900);
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
  background-color: var(--main-700);
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

#files-to-compare-with-container {
  height: calc(100% - 54px);
}
</style>