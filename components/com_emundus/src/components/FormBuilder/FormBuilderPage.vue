<template>
  <div id="form-builder-page">
    <span class="em-h3"> {{ title }} </span>
    <p> {{ description }} </p>

    <div class="form-builder-page-sections">
      <form-builder-page-section
          v-for="(section, index) in sections"
          :key="section.group_id"
          :section="section"
          :index="index+1"
          :totalSections="sections.length"
      >
      </form-builder-page-section>
    </div>
    <button class="em-secondary-button"> Ajouter une section </button>
  </div>
</template>

<script>
import formService from '../../services/form';

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
          console.log(response.data);
          this.sections = Object.values(response.data.Groups);
        }
      });
    },
    addSection() {
    },
  },
}
</script>

<style lang="scss">

#form-builder-page {
  width: calc(100% - 80px);
  margin: 40px 40px;
}

</style>