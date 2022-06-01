<template>
  <div id="form-builder-pages">
    <p class="form-builder-title em-flex-row em-s-justify-content-center em-flex-space-between em-p-16">
      <span>{{ translate('COM_EMUNDUS_FORM_BUILDER_EVERY_PAGE') }}</span>
      <span class="material-icons em-pointer" @click="$emit('open-page-properties')"> add </span>
    </p>
    <draggable
        v-model="pages"
        group="form-builder-pages"
        :sort="true"
        class="draggables-list"
        @end="onDragEnd"
    >
      <transition-group>
        <div
            class="em-font-weight-500 em-pointer"
            v-for="page in pages"
            :key="page.id"
            :class="{
              selected: page.id === selected,
            }"
        >
          <div class="em-flex-row em-flex-space-between">
            <p  @click="selectPage(page.id)" class="em-w-100 em-p-16">{{ page.label }}</p>
            <div class="em-flex-row em-p-16">
              <v-popover :popoverArrowClass="'custom-popover-arraow'">
                <span class="material-icons">more_horiz</span>

                <template slot="popover">
                  <transition :name="'slide-down'" type="transition">
                    <div>
                      <nav aria-label="action" class="em-flex-col-start">
                        <p @click="" class="em-p-8-12 em-w-100">
                          {{ translate('COM_EMUNDUS_FORM_BUILDER_DUPLICATE_PAGE') }}
                        </p>
                        <p @click="deletePage(page)" class="em-p-8-12 em-w-100 em-red-500-color">
                          {{ translate('COM_EMUNDUS_FORM_BUILDER_DELETE_PAGE') }}
                        </p>
                      </nav>
                    </div>
                  </transition>
                </template>
              </v-popover>

<!--              <span :style="page.id === selected ? 'opacity:1' : 'opacity: 0'" class="material-icons em-ml-16" @click="sectionsShown = !sectionsShown">
                {{ sectionsShown ? 'keyboard_arrow_up' : 'keyboard_arrow_down' }}
              </span>-->
            </div>
          </div>
          <ul
              id="form-builder-pages-sections-list"
              class="em-font-size-12 em-mb-8 em-mr-8 em-ml-8 em-mt-8"
              v-if="page.id === selected  && sectionsShown"
          >
            <li v-for="section in selectedPageSections" :key="section.group_id" class="em-mb-4">
              {{ section.label.fr }}
            </li>
          </ul>
        </div>
      </transition-group>
    </draggable>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';
import formBuilderMixin from '../../mixins/formbuilder';
import formService from '../../services/form';
import draggable from "vuedraggable";
import Swal from "sweetalert2";

export default {
  name: 'FormBuilderPages',
  components: {
    draggable,
  },
  props: {
    pages: {
      type: Array,
      required: true
    },
    selected: {
      type: Number,
      default: 0
    },
    profile_id: {
      type: Number,
      required: true
    }
  },
  mixins: [formBuilderMixin],
  data() {
    return {
      sectionsShown: false,
      selectedPageSections: [],
    };
  },
  created() {
    this.getPageSections();
  },
  methods: {
    getPageSections() {
      formService.getPageObject(this.selected).then(response => {
        this.selectedPageSections = Object.values(response.data.Groups);
      });
    },
    selectPage(id) {
      this.$emit('select-page', id);
    },
    addPage() {
      formBuilderService.addPage({
        label: 'Nouvelle page',
        intro: '',
        prid: this.profile_id,
        modelid: -1,
        template: 0
      }).then(response => {
        this.$emit('add-page');
        this.updateLastSave();
      });
    },
    deletePage(page) {
      Swal.fire({
        title: this.translate('COM_EMUNDUS_FORM_BUILDER_DELETE_PAGE_CONFIRMATION') + page.label,
        text: this.translate('COM_EMUNDUS_FORM_BUILDER_DELETE_PAGE_CONFIRMATION_TEXT'),
        type: "warning",
        showCancelButton: true,
        confirmButtonText: this.translate("COM_EMUNDUS_ACTIONS_DELETE"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-delete-button',
        },
      }).then(result => {
        if (result.value) {
          formBuilderService.deletePage(page.id).then(response => {
            let deletedPage = this.pages.findIndex(p => p.id === page.id);
            this.pages.splice(deletedPage, 1);
            if(this.selected == page.id) {
              this.$emit('delete-page');
            }
            this.updateLastSave();
          });
        }
      });
    },
    onDragEnd() {
      const newOrder = this.pages.map((page, index) => {
        return {
          id: page.id,
          order: index
        };
      });

      formBuilderService.reorderMenu(newOrder);
    }
  },
  watch: {
    selected() {
      this.getPageSections();
    },
  }
}
</script>

<style lang="scss">
#form-builder-pages {
  p {
    font-weight: 400;
    font-size: 14px;
    line-height: 18px;

    &:last-child {
      margin-bottom: 0 !important;
    }
  }

  .selected {
    background: #f8f8f8;
    p {
      font-weight: 600;
    }
  }

  #form-builder-pages-sections-list {
    list-style: none;
  }
}
</style>
