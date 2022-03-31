<template>
  <div id="form-builder-elements">
    <p id="form-builder-elements-title" class="em-text-align-center em-w-100 em-p-16"> Éléments </p>
    <draggable
        v-model="elements"
        group="form-builder-elements"
        class="draggables-list"
        @start="onDragStart"
        @end="onDragEnd"
    >
      <transition-group>
        <div
            v-for="element in publishedElements"
            :key="element.id"
            class="form-builder-element em-flex-row em-flex-space-between"
        >
          <span class="material-icons">{{ element.icon }}</span>
          <span>{{ translate(element.name) }}</span>
          <span class="material-icons"> drag_indicator</span>
        </div>
      </transition-group>
    </draggable>
  </div>
</template>

<script>
// external libraries
import draggable from "vuedraggable";

export default {
  components: {
    draggable
  },
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
      this.$emit('drag-end', event);
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
  cursor: grab;
}

#form-builder-elements-title {
  border-bottom: 1px solid black;
}
</style>