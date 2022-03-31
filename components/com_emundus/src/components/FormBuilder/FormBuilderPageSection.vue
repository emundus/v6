<template>
  <div id="form-builder-page-section">
    <div class="section-card em-flex-column">
      <div class="section-identifier em-bg-main-500">Section {{ index }} / {{ totalSections }}</div>
      <div class="section-content">
        <p class="section-title">{{ section.label.fr }}</p>
        <draggable
          v-model="sectionElementsAsArray"
          group="form-builder-elements"
          class="draggables-list"
          @end="onDragEnd"
        >
          <transition-group>
            <form-builder-page-section-element
              v-for="element in sectionElementsAsArray"
              :key="element.id"
              :element="element"
            >
            </form-builder-page-section-element>
          </transition-group>
          <div
              v-if="sectionElementsAsArray.length < 1"
              class="empty-section-element"
          >
            <p class="em-w-100 em-text-align-center">{{ translate("COM_EMUNDUS_FORM_BUILDER_EMPTY_SECTION") }}</p>
          </div>
        </draggable>
      </div>
    </div>
  </div>
</template>

<script>
import FormBuilderPageSectionElement from "./FormBuilderPageSectionElement";
import draggable from "vuedraggable";

export  default {
  components: {
    FormBuilderPageSectionElement,
    draggable
  },
  props: {
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
  methods: {
    onDragEnd(event) {
      // get new order of elements


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