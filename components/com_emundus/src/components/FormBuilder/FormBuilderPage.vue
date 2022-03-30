<template>
  <div id="form-builder-page">
    <span class="em-h2"> {{ title }} </span>
    <p> {{ description }} </p>

    <div class="form-builder-page-sections">
      <form-builder-page-section
          v-for="section in sections"
          :key="section.id"
          :section="section"
      >
      </form-builder-page-section>
    </div>
    <button class="em-secondary-button"> {{ translate("COM_EMUNDUS_ONBOARD_BUILDER_ADDITEM") }} </button>
  </div>
</template>

<script>
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
    campaign_id: {
      type: Number,
      default: 0
    },
  },
  data() {
    return {
      title: 'Nouvelle Page',
      description: 'Ajouter une description',
      sections: [],
    };
  }
}
</script>