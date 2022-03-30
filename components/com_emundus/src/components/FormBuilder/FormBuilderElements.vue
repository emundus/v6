<template>
  <div id="form-builder-elements">
    <span class="em-h5 em-text-align-center"> Éléments </span>
    <draggable
        :list="elements"
        group="form-builder-elements"
        class="draggables-list"
        @end="onDragEnd"
    >
      <div
          v-for="element in publishedElements"
          :key="element.id"
          class="form-builder-element em-flex-row em-flex-space-between"
      >
        <span class="material-icons">{{ element.icon }}</span>
        <span>{{ translate(element.name) }}</span>
        <span class="material-icons"> drag_indicator</span>
      </div>
    </draggable>
  </div>
</template>

<script>
// external libraries
import draggable from "vuedraggable";

export default {
  data() {
    return {
      elements: [],
    }
  },
  created() {
    this.elements = this.getElements();
  },
  methods: {
    getElements() {
      return require('@/data/form-builder-elements.json');
    },
    onDragEnd(event) {
      console.log(event);
      this.$emit('dragged', event);
    }
  },
  computed: {
    publishedElements() {
      return this.elements.filter(element => element.published);
    },
    dragOptions() {
      return {
        group: {
          name: "items",
          pull: "clone",
          put: false
        },
        sort: false,
        disabled: false,
        ghostClass: "ghost"
      };
    }
  }
}
</script>

<style lang="scss">
.form-builder-element {
  width: 258px;
  height: 48px;
  font-size: 14px;
  padding: 15px;
  margin: 8px 0px;
  background-color: #FAFAFA;
  border: 1px solid #F2F2F3;
}
</style>