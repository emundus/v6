<template>
  <div id="form-builder-elements">
    <p id="form-builder-elements-title" class="em-text-align-center em-w-100 em-p-16"> {{ translate('COM_EMUNDUS_FORM_BUILDER_ELEMENTS') }} </p>
    <draggable
        v-model="elements"
        class="draggables-list"
        :group="{ name: 'form-builder-section-elements', pull: 'clone', put: false }"
        :sort="false"
        :clone="setCloneElement"
        @end="onDragEnd"
    >
      <transition-group>
        <div
            v-for="element in publishedElements"
            :key="element.id"
            class="form-builder-element em-flex-row em-flex-space-between"
        >
          <span class="material-icons-outlined">{{ element.icon }}</span>
          <span class="em-w-100 em-p-16">{{ translate(element.name) }}</span>
          <span class="material-icons-outlined"> drag_indicator</span>
        </div>
      </transition-group>
    </draggable>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
// external libraries
import draggable from "vuedraggable";

import formBuilderService from "../../services/formbuilder";
import formBuilderMixin from "../../mixins/formbuilder";

export default {
  components: {
    draggable
  },
  mixins: [formBuilderMixin],
  data() {
    return {
      elements: [],
      cloneElement: {},

      loading: false,
    }
  },
  created() {
    this.elements = this.getElements();
  },
  methods: {
    getElements() {
      return require('../../../data/form-builder-elements.json');
    },
    setCloneElement(element) {
      this.cloneElement = element;
    },
    onDragEnd(event) {
      this.loading = true;
      const to = event.to;
      if (to === null) {
        this.loading = false;
        return;
      }

      const group_id = to.dataset.sid;
      if (!group_id) {
        this.loading = false;
        return;
      }

      formBuilderService.createSimpleElement({
        gid: group_id,
        plugin: this.cloneElement.value,
      }).then(response => {
        formBuilderService.updateElementOrder(group_id, response.data.scalar, event.newDraggableIndex).then((response) => {
          this.$emit('element-created');
          this.updateLastSave();
          this.loading = false;
        });
      }).catch((error) => {
        this.loading = false;
      });
    },
  },
  computed: {
    publishedElements() {
      return this.elements.filter(element => element.published);
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
