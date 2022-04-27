<template>
  <div :id="'form-builder-page-section-' + section.group_id" class="form-builder-page-section">
    <div class="section-card em-flex-column">
      <div
          class="section-identifier em-bg-main-500 em-pointer"
          @click="closedSection = !closedSection"
      > {{ translate('COM_EMUNDUS_FORM_BUILDER_SECTION') }} {{ index }} / {{ totalSections }}</div>
      <div
          class="section-content"
          :class="{
            'closed': closedSection,
          }"
      >
        <div class="em-flex-row em-flex-space-between em-w-100 ">
          <span
              id="section-title"
              class="editable-data"
              ref="sectionTitle"
              contenteditable="true"
              @focusout="updateTitle"
          >
            {{ section.label.fr }}
          </span>
          <span id="delete-section" class="material-icons em-red-500-color em-pointer delete" @click="deleteSection">delete</span>
        </div>
        <p id="section-intro"
          class="editable-data"
          ref="sectionIntro"
          contenteditable="true"
          @focusout="updateIntro"

          v-html="section.group_intro"
        >
        </p>
        <draggable
          v-model="elements"
          group="form-builder-section-elements"
          :sort="true"
          class="draggables-list"
          @end="onDragEnd"
        >
          <transition-group
              :data-prid="profile_id"
              :data-page="page_id"
              :data-sid="section.group_id"
          >
            <form-builder-page-section-element
              v-for="element in elements"
              :key="element.id"
              :element="element"
              @open-element-properties="$emit('open-element-properties', element)"
              @delete-element="deleteElement"
            >
            </form-builder-page-section-element>
          </transition-group>
        </draggable>
        <div
            v-if="elements.length < 1"
            class="empty-section-element"
        >
          <draggable
              :list="emptySection"
              group="form-builder-section-elements"
              :sort="false"
              class="draggables-list"
          >
            <transition-group
                :data-prid="profile_id"
                :data-page="page_id"
                :data-sid="section.group_id"
            >
              <p
                  class="em-w-100 em-text-align-center"
                  v-for="(item, index) in emptySection"
                  :key="index"
              >
                {{ translate(item.text) }}
              </p>
            </transition-group>
          </draggable>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';
import formBuilderMixin from "../../mixins/formbuilder";

import FormBuilderPageSectionElement from "./FormBuilderPageSectionElement";
import draggable from "vuedraggable";

export  default {
  components: {
    FormBuilderPageSectionElement,
    draggable
  },
  props: {
    profile_id: {
      type: Number,
      required: true
    },
    page_id: {
      type: Number,
      required: true
    },
    section: {
      type: Object,
      required: true
    },
    index: {
      type: Number,
      default: 0
    },
    totalSections: {
      type: Number,
      default: 0
    },
  },
  mixins: [formBuilderMixin],
  data() {
    return {
      closedSection: false,
      elements: [],
      emptySection: [
        {
          "text": "COM_EMUNDUS_FORM_BUILDER_EMPTY_SECTION",
        }
      ],
    };
  },

  created() {
    this.getElements();
  },
  methods: {
    getElements() {
      const elements = Object.values(this.section.elements);
      this.elements = elements.length > 0 ? elements : [];
    },
    updateTitle() {
      this.section.label.fr = this.$refs.sectionTitle.innerText;
      formBuilderService.updateTranslation({
        value: this.section.group_id,
        key: 'group'
      }, this.section.group_tag, this.section.label);
      this.updateLastSave();
    },
    updateIntro() {
      this.section.group_intro = this.$refs.sectionIntro.innerHTML;
      formBuilderService.updateGroupParams(this.section.group_id, {
        'intro': this.section.group_intro
      });
      this.updateLastSave();
    },
    onDragEnd(e) {
      const toGroup = e.to.getAttribute('data-sid');

      if (toGroup == this.section.group_id) {
        const elements = this.elements.map((element, index) => {
          return { id: element.id, order: index + 1 };
        });
        const movedElement = this.elements[e.newIndex];
        formBuilderService.updateOrder(elements, this.section.group_id, movedElement);
        this.updateLastSave();
      } else {
        this.$emit('move-element', e, this.section.group_id, toGroup);
      }
    },
    deleteElement(elementId) {
      delete this.section.elements[elementId];
      this.elements = this.elements.filter(element => element.id !== elementId);
      this.updateLastSave();
    },
    deleteSection() {
      this.swalConfirm(
          this.translate("COM_EMUNDUS_FORM_BUILDER_DELETE_SECTION"),
          this.section.label.fr,
          this.translate("COM_EMUNDUS_FORM_BUILDER_DELETE_SECTION_CONFIRM"),
          this.translate("JNO"),
          () => {
            formBuilderService.deleteGroup(this.section.group_id);
            this.$emit('delete-section', this.section.group_id);
            this.updateLastSave();
          }
      );
    },
  },
  watch: {
    section: {
      handler() {
        this.getElements();
      },
      deep: true
    }
  }
}
</script>

<style lang="scss">
.form-builder-page-section {
  margin: 32px 0;

  .section-card {
    .section-identifier {
      color: white;
      padding: 8px 24px;
      border-radius: 4px 4px 0px 0px;
      display: flex;
      align-self: flex-end;
    }

    .section-content {
      padding: 32px;
      border-top: 4px solid #20835F;
      background-color: white;
      width: 100%;
      transition: all 0.3s ease-in-out;

      &:hover {
        #delete-section {
          opacity: 1;
          pointer-events: all;
        }
      }

      &.closed {
        max-height: 93px;
        overflow: hidden;

        #section-intro {
          display: none;
        }
      }

      #delete-section {
        opacity: 0;
        pointer-events: none;
        transition: all .3s;
      }

      #section-title {
        font-weight: 800;
        font-size: 20px;
        line-height: 25px;
      }

      .empty-section-element {
        border: 1px dashed;
        opacity: 0.2;
        padding: 11px;
        margin: 32px 0 0 0;
      }
    }
  }
}
</style>