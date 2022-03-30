<template>
  <div id="form-builder-page-section">
    <div class="section-card em-flex-column">
      <div class="section-identifier em-bg-main-500">Section {{ index }} / {{ totalSections }}</div>
      <div class="section-content">
        <p>{{ section.label.fr }}</p>
        <form-builder-page-section-element
          v-for="(element, index) in section.elements"
          :key="element.id"
          :element="element"
        >

        </form-builder-page-section-element>
      </div>
    </div>
  </div>
</template>

<script>
import FormBuilderPageSectionElement from "./FormBuilderPageSectionElement";
export  default {
  components: {
    FormBuilderPageSectionElement
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
    }
  }
}
</style>