<template>
  <div id="form-builder-elements">
    <div class="flex items-center justify-around">
      <div v-for="menu in menus" :key="menu.id" id="form-builder-elements-title" class="em-light-tabs em-pointer" @click="selected = menu.id" :class="selected === menu.id ? 'em-light-selected-tab' : ''">
        {{ translate(menu.name) }}
      </div>
    </div>

    <div v-if="selected === 1">
      <draggable
          v-model="publishedElements"
          class="draggables-list"
          :group="{ name: 'form-builder-section-elements', pull: 'clone', put: false }"
          :sort="false"
          :clone="setCloneElement"
          @end="onDragEnd"
      >
        <transition-group>
          <div
              v-for="element in publishedElements"
              :key="element.value"
              class="form-builder-element flex justify-between items-start gap-3 p-3"
          >
            <span class="material-icons-outlined">{{ element.icon }}</span>
            <p class="w-full flex flex-col">
              {{ translate(element.name) }}
              <span class="text-neutral-600 text-xs">{{ translate(element.description) }}</span>
            </p>
            <span class="material-icons-outlined self-center">drag_indicator</span>
          </div>
        </transition-group>
      </draggable>
    </div>

    <div v-if="selected === 2">
      <div
          v-for="group in publishedGroups"
          :key="group.id"
          class="draggables-list"
          @click="addGroup(group)"
      >
          <div
              class="form-builder-element flex items-center justify-between cursor-pointer"
          >
            <span class="material-icons-outlined">{{ group.icon }}</span>
            <span class="em-w-100 em-p-16">{{ translate(group.name) }}</span>
            <span class="material-icons-outlined">add_circle_outline</span>
          </div>
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
// external libraries
import draggable from 'vuedraggable';

import formBuilderService from '../../services/formbuilder';
import formBuilderMixin from '../../mixins/formbuilder';
import errorsMixin from '../../mixins/errors';

export default {
  components: {
    draggable
  },
  mixins: [formBuilderMixin, errorsMixin],
  props: {
    form: {
      type: Object,
      required: false
    }
  },
  data() {
    return {
      selected: 1,
      menus: [
        {
          id: 1,
          name: 'COM_EMUNDUS_FORM_BUILDER_ELEMENTS'
        },
        {
          id: 2,
          name: 'COM_EMUNDUS_FORM_BUILDER_SECTIONS'
        }
      ],
      elements: [],
      groups: [],
      cloneElement: {},
      loading: false,
    }
  },
  created() {
    this.elements = this.getElements();
    this.groups = this.getSections();
  },
  methods: {
    addGroup(group) {
      this.loading = true;

      const data = this.$store.getters['global/datas'];
      const mode = typeof data.mode !== 'undefined' ? data.mode.value : 'forms';

      formBuilderService.createSectionSimpleElements({
        gid: group.id,
        fid: this.form.id,
        mode: mode
      }).then(response => {
        if (response.status && response.data.data.length > 0) {
          this.$emit('element-created');
          this.updateLastSave();
          this.loading = false;
        } else {
          this.displayError(response.msg);
          this.loading = false;
        }
      }).catch((error) => {
        console.warn(error);
        this.loading = false;
      });
    },
    getElements() {
      return require('../../../data/form-builder-elements.json');
    },
    getSections() {
      return require('../../../data/form-builder-sections.json');
    },
    onDragEnd: function (event) {
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

      const data = this.$store.getters['global/datas'];
      const mode = typeof data.mode !== 'undefined' ? data.mode.value : 'forms';

      formBuilderService.createSimpleElement({
        gid: group_id,
        plugin: this.cloneElement.value,
        mode: mode
      }).then(response => {
        if (response.status && response.data > 0) {
          formBuilderService.updateElementOrder(group_id, response.data, event.newDraggableIndex).then(() => {
            this.$emit('element-created');
            this.updateLastSave();
            this.loading = false;
          });
          if (this.cloneElement.value === 'emundus_fileupload') {
            this.swalParameter(this.translate("COM_EMUNDUS_ATTACHMENTS_SWAL_PARAM"));

          }


        } else {
          this.displayError(response.msg);
          this.loading = false;
        }
      }).catch((error) => {
        console.warn(error);
        this.loading = false;
      });
    },
    setCloneElement(element) {
      this.cloneElement = element;
    }
  },
  computed: {
    publishedElements() {
      return this.elements.filter(element => element.published);
    },
    publishedGroups() {
      return this.groups.filter(group => group.published);
    }
  }
}
</script>

<style lang="scss">
.form-builder-element {
  width: 258px;
  height: auto;
  font-size: 14px;
  margin: 8px 0px;
  background-color: #FAFAFA;
  border: 1px solid #F2F2F3;
  cursor: grab;
  border-radius: calc(var(--em-default-br)/2);
  &:hover {
    background-color: var(--neutral-200);
  }
}
</style>
