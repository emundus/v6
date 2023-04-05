<template>
  <div id="form-builder-pages">
    <p class="form-builder-title em-flex-row em-s-justify-content-center em-flex-space-between em-p-16">
      <span>{{ translate('COM_EMUNDUS_FORM_BUILDER_EVERY_PAGE') }}</span>
      <span class="material-icons em-pointer" @click="$emit('open-page-create')"> add </span>
    </p>
    <draggable v-model="pages" group="form-builder-pages" :sort="true" class="draggables-list" @end="onDragEnd">
      <transition-group>
        <div
            class="em-font-weight-500 em-pointer"
            v-for="page in formPages"
            :key="page.id"
            :class="{selected: page.id === selected}"
        >
          <div class="em-flex-row em-flex-space-between" @mouseover="pageOptionsShown = page.id" @mouseleave="pageOptionsShown = 0">
            <p @click="selectPage(page.id)" class="em-w-100 em-p-16">{{ translate(page.label) }}</p>
            <div class="em-flex-row em-p-16" :style="pageOptionsShown === page.id ? 'opacity:1' : 'opacity: 0'">
	            <v-popover :popoverArrowClass="'custom-popover-arraow'">
                <span class="material-icons">more_horiz</span>

                <template slot="popover">
                  <transition :name="'slide-down'" type="transition">
                    <div>
                      <nav aria-label="action" class="em-flex-col-start">
                        <p @click="deletePage(page)" class="em-p-8-12 em-w-100 em-red-500-color">
                          {{ translate('COM_EMUNDUS_FORM_BUILDER_DELETE_PAGE') }}
                        </p>
                      </nav>
                    </div>
                  </transition>
                </template>
              </v-popover>
            </div>
          </div>
        </div>
      </transition-group>
    </draggable>

    <transition-group>
      <div
        class="em-font-weight-500 em-pointer"
        v-for="page in submissionPages"
        :key="page.id"
        :class="{selected: page.id === selected}"
      >
        <div class="em-flex-row em-flex-space-between">
          <p @click="selectPage(page.id)" class="em-w-100 em-p-16 em-main-500-color">{{ page.label }}</p>
        </div>
      </div>
    </transition-group>
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
      pageOptionsShown: 0,
    };
  },
  created() {},
  methods: {
    selectPage(id) {
      this.$emit('select-page', id);
    },
    deletePage(page) {
      if(this.pages.length > 2) {
        Swal.fire({
          title: this.translate('COM_EMUNDUS_FORM_BUILDER_DELETE_PAGE_CONFIRMATION') + page.label,
          text: this.translate('COM_EMUNDUS_FORM_BUILDER_DELETE_PAGE_CONFIRMATION_TEXT'),
          type: "warning",
          showCancelButton: true,
          confirmButtonText: this.translate('COM_EMUNDUS_ACTIONS_DELETE'),
          cancelButtonText: this.translate('COM_EMUNDUS_ONBOARD_CANCEL'),
          reverseButtons: true,
          customClass: {
            title: 'em-swal-title',
            cancelButton: 'em-swal-cancel-button',
            confirmButton: 'em-swal-delete-button',
          },
        }).then(result => {
          if (result.value) {
            formBuilderService.deletePage(page.id).then(response => {
							if (response.status) {
								let deletedPage = this.pages.findIndex(p => p.id === page.id);
								this.pages.splice(deletedPage, 1);
								if (this.selected == page.id) {
									this.$emit('delete-page');
								}
								this.updateLastSave();
							}
            });
          }
        });
      } else {
        Swal.fire({
          title: this.translate('COM_EMUNDUS_FORM_BUILDER_DELETE_PAGE_ERROR'),
          text: this.translate('COM_EMUNDUS_FORM_BUILDER_DELETE_PAGE_ERROR_TEXT'),
          type: 'error',
          showCancelButton: false,
          confirmButtonText: this.translate('COM_EMUNDUS_ONBOARD_OK'),
          reverseButtons: true,
          customClass: {
            title: 'em-swal-title',
            confirmButton: 'em-swal-confirm-button',
            actions: "em-swal-single-action",
          },
        });
      }
    },
    onDragEnd() {
      const newOrder = this.pages.map((page, index) => {return {rgt: index, link: page.link};});

      formBuilderService.reorderMenu(newOrder, this.$props.profile_id).then((response) => {
				if (response.status == 200 && response.data.status) {
					this.$emit('reorder-pages', this.pages);
				} else {
					Swal.fire({
						title: this.translate('COM_EMUNDUS_FORM_BUILDER_UPDATE_ORDER_PAGE_ERROR'),
						text: result.msg,
						type: 'error',
						showCancelButton: false,
						confirmButtonText: this.translate('COM_EMUNDUS_ONBOARD_OK'),
						reverseButtons: true,
						customClass: {
							title: 'em-swal-title',
							confirmButton: 'em-swal-confirm-button',
							actions: "em-swal-single-action",
						},
					});
				}
      });
    }
  },
  computed: {
    // return all pages but not submission page
    formPages() {
      return this.pages.filter((page) => {
        return page.type != 'submission';
      });
    },
    submissionPages() {
      return this.pages.filter((page) => {
        return page.type == 'submission';
      });
    }
  },
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

	.save {
		&.already-saved {
			color: #20835f;
		}
	}
}
</style>
