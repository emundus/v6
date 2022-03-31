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
            <
          </transition-group>
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
  mounted() {
    console.log(this.section);
  },
  methods: {
    onDragEnd(event) {
      console.log('drag end');
      // get new order of elements


    }
  },
  computed: {
    sectionElementsAsArray() {
      return Object.values(this.section.elements);
    }
  }
}
</script>

<style lang="scss">
#form-builder-page-section {
  margin-bottom: 16px;

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
      min-height: 340px;

      .section-title {
        font-weight: 800;
        font-size: 20px;
        line-height: 25px;
      }
    }
  }
}
</style>