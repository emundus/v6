<template>
  <div id="form-builder-page-section">
    <div class="section-card em-flex-column">
      <div
          class="section-identifier em-bg-main-500 em-pointer"
          @click="closedSection = !closedSection"
      >Section {{ index }} / {{ totalSections }}</div>
      <div
          class="section-content"
          :class="{
            'closed': closedSection,
          }"
      >
        <span
            class="section-title editable-data"
            ref="sectionTitle"
            contenteditable="true"
            @focusout="updateTitle"
        >
          {{ section.label.fr }}
        </span>
        <span
          class="section-intro editable-data"
          ref="sectionIntro"
          contenteditable="true"
          @focusout="updateIntro"
          v-html="section.group_intro"
        >
        </span>
        <draggable
          v-model="sectionElementsAsArray"
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
              v-for="element in sectionElementsAsArray"
              :key="element.id"
              :element="element"
              @open-element-properties="$emit('open-element-properties', element)"
            >
            </form-builder-page-section-element>
          </transition-group>
        </draggable>
        <div
            v-if="sectionElementsAsArray.length < 1"
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
  data() {
    return {
      closedSection: false,
      emptySection: [
        {
          "text": "COM_EMUNDUS_FORM_BUILDER_EMPTY_SECTION",
        }
      ]
    };
  },
  methods: {
    updateTitle() {
      this.section.label.fr = this.$refs.sectionTitle.innerText;
      formBuilderService.updateTranslation({
        value: this.section.group_id,
        key: 'group'
      }, this.section.group_tag, this.section.label);
    },
    updateIntro() {

    }
  },
  computed: {
    sectionElementsAsArray() {
      const elements = Object.values(this.section.elements);
      return elements.length > 0 ? elements : [];
    }
  }
}
</script>

<style lang="scss">
#form-builder-page-section {
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

      &.closed {
        max-height: 93px;
        overflow: hidden;
      }

      .section-title {
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