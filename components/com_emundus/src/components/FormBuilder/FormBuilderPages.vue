<template>
  <div id="form-builder-pages">
    <span class="form-builder-title">Toutes les pages</span>
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
    }
  },
  methods: {
    selectPage(id) {
      this.$emit('select-page', id);
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