<template>
  <div id="form-builder-pages">
    <p class="form-builder-title em-flex-row em-s-justify-content-center em-flex-space-between em-p-16">
      <span>{{ translate('COM_EMUNDUS_FORM_BUILDER_EVERY_PAGE') }}</span>
      <span
          class="material-icons"
          @click="addPage"
      >
        add
      </span>
    </p>
    <div
        class="em-p-16 em-font-weight-500"
        v-for="page in pages"
        :key="page.id"
        :class="{
          selected: page.id === selected,
        }"
        @click="selectPage(page.id)"
    >
      <p>{{ page.label }}</p>
      <ul
          id="form-builder-pages-sections-list"
          class="em-font-size-12 em-mb-8 em-mr-8 em-ml-8"
          v-if="page.id === selected"
      >
        <li
            v-for="section in selectedPageSections"
            :key="section.group_id"
            class="em-mb-4"
        >
          {{ section.label.fr }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';
import formBuilderMixin from '../../mixins/formbuilder';
import formService from '../../services/form';

export default {
  name: 'FormBuilderPages',
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
      selectedPageSections: [],
    };
  },
  created() {
    this.getPageSections();
  },
  methods: {
    getPageSections() {
      formService.getPageObject(this.selected).then(response => {
        this.selectedPageSections = Object.values(response.data.Groups);
      });
    },
    selectPage(id) {
      this.$emit('select-page', id);
    },
    addPage() {
      formBuilderService.addPage({
        label: 'Nouvelle page',
        intro: '',
        prid: this.profile_id,
        modelid: -1,
        template: 0
      }).then(response => {
        this.$emit('add-page');
        this.updateLastSave();
      });
    }
  },
  watch: {
    selected() {
      this.getPageSections();
    }
  }
}
</script>

<style lang="scss">
#form-builder-pages {
  p {
    cursor: pointer;
    margin-bottom: 15px !important;
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
    margin-top: 0;
  }
}
</style>