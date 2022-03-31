<template>
  <div id="form-builder-pages">
    <p class="form-builder-title em-flex-row em-s-justify-content-center em-flex-space-between">
      <span>Toutes les pages</span>
      <span
          class="material-icons"
          @click="addPage"
      >
        add
      </span>
    </p>
    <div
        v-for="page in pages"
        :key="page.id"
    >
      <p
          :class="{
            selected: page.id === selected,
          }"
          @click="selectPage(page.id)"
      >
        {{ page.label }}
      </p>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';

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
  methods: {
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
      });
    }
  }
}
</script>

<style lang="scss">
#form-builder-pages {
  p {
    cursor: pointer;
    margin: 15px 0;
    font-weight: 400;
    font-size: 14px;
    line-height: 18px;
  }

  p.selected {
    color: var(--success-color);
  }
}
</style>