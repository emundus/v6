<template>
  <div id="form-builder-page">
    <span
        class="em-h3 editable-data"
        ref="pageTitle"
        @focusout="updateTitle"
        contenteditable="true"
    >
      {{ title }}
    </span>
    <span
        class="description editable-data"
        ref="pageDescription"
        v-html="description"
        @focusout="updateDescription"
        contenteditable="true"
      >
    </span>

    <div class="form-builder-page-sections">
      <form-builder-page-section
          v-for="(section, index) in sections"
          :key="section.group_id"
          :profile_id="profile_id"
          :page_id="page.id"
          :section="section"
          :index="index+1"
          :totalSections="sections.length"
          :ref="'section-'+section.group_id"
          @open-element-properties="$emit('open-element-properties', $event)"
          @move-element="updateElementsOrder"
          @delete-section="deleteSection"
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
      fabrikPage: {},
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
          this.fabrikPage = response.data;
          this.title = this.fabrikPage.show_title.label.fr;
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
    updateTitle()
    {
      this.fabrikPage.show_title.label.fr = this.$refs.pageTitle.innerText;
      formBuilderService.updateTranslation(null, this.fabrikPage.show_title.titleraw, this.fabrikPage.show_title.label).then(response => {
        if (response.status) {
          this.$emit('update-page-title', {
            page: this.page.id,
            new_title: this.$refs.pageTitle.innerText
          });
        }
      });
    },
    updateDescription()
    {
      this.fabrikPage.intro.fr = this.$refs.pageDescription.innerText;
      formBuilderService.updateTranslation(null, this.fabrikPage.intro_raw, this.fabrikPage.intro);
    },
    updateElementsOrder(event, fromGroup, toGroup) {
      const sectionFrom = this.sections.find(section => section.group_id === fromGroup);
      const fromElements = Object.values(sectionFrom.elements);
      const movedElement = fromElements[event.oldIndex];
      const toElements = this.$refs['section-'+toGroup][0].elements.map((element, index) => {
        return { id: element.id, order: index + 1 };
      });

      if (movedElement.id) {
        formBuilderService.updateOrder(toElements, toGroup, movedElement);
      }
    },
    deleteSection(sectionId) {
      this.sections = this.sections.filter(section => section.group_id !== sectionId);
    }
  },
}
</script>

<style lang="scss">

#form-builder-page {
  width: calc(100% - 80px);
  margin: 40px 40px;

  .description {
    display: block;
  }

  #add-section {
    width: fit-content;
    padding: 24px;
    margin: auto;
    background-color: white;
  }
}

</style>