<template>
  <div id="form-builder-page">
    <span class="em-h3"> {{ title }} </span>
    <span v-html="description"></span>

    <div class="form-builder-page-sections">
      <form-builder-page-section
          v-for="(section, index) in sections"
          :key="section.group_id"
          :profile_id="profile_id"
          :page_id="page.id"
          :section="section"
          :index="index+1"
          :totalSections="sections.length"
      >
      </form-builder-page-section>
    </div>
    <button
        id="add-section"
        class="em-secondary-button"
        @click="addSection()"
    > Ajouter une section </button>
  </div>
</template>

<script>
import formService from '../../services/form';
import formBuilderService from '../../services/formbuilder';

import FormBuilderPageSection from "./FormBuilderPageSection";

export default {
  components: {
    FormBuilderPageSection
  },
  props: {
    profile_id: {
      type: Number,
      default: 0
    },
    page: {
      type: Object,
      default: {}
    },
  },
  data() {
    return {
      title: 'Nouvelle Page',
      description: 'Ajouter une description',
      sections: [],
    };
  },
  mounted() {
    if (this.page.id) {
      this.title = this.page.label;
      this.getSections();
    }
  },
  methods: {
    getSections() {
      formService.getPageObject(this.page.id).then(response => {
        if (response.status) {
          this.description = response.data.intro.fr;
          this.sections = Object.values(response.data.Groups);
        }
      });
    },
    addSection() {
      formBuilderService.createSimpleGroup(this.page.id, 'Nouvelle section').then(response => {
        if (response.status) {
          this.getSections();
        }
      });
    },
  },
}
</script>

<style lang="scss">

#form-builder-page {
  width: calc(100% - 80px);
  margin: 40px 40px;

  #add-section {
    width: fit-content;
    padding: 24px;
    margin: auto;
    background-color: white;
  }
}

</style>