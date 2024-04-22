<template>
  <div id="form-builder-document-formats">
    <p id="form-builder-document-title" class="em-text-align-center em-w-100 em-p-16">
      {{ translate('COM_EMUNDUS_FORM_BUILDER_FORMATS') }}
    </p>
	  <input v-if="formats.length > 0" id="search" v-model="search" type="text" class="em-mt-16 em-w-100" placeholder=""/>
    <draggable
        v-model="displayedFormats"
        class="draggables-list"
        :group="{ name: 'form-builder-documents', pull: 'clone', put: false }"
        :sort="false"
        :clone="setCloneFormat"
        @start="$emit('dragging-element')"
        @end="onDragEnd"
    >
      <transition-group>
        <div
		        v-for="format in displayedFormats"
            :key="format.id"
            class="em-flex-row em-flex-space-between draggable-element em-mt-8 em-mb-8 em-p-16"
        >
          <span id="format-name" class="em-w-100 em-p-16" :title="format.name[shortDefaultLang]">{{ format.name[shortDefaultLang] }}</span>
          <span class="material-icons-outlined"> drag_indicator </span>
        </div>
      </transition-group>
    </draggable>
  </div>
</template>

<script>
import draggable from 'vuedraggable';
import formBuilderMixin from '../../mixins/formbuilder';
import formService from '../../services/form';

export default {
  components: {
    draggable
  },
  props: {
    profile_id: {
      type: Number,
      required: true
    }
  },
  mixins: [formBuilderMixin],
  data() {
    return {
      formats: [],
      cloneFormat: null,
	    search: ''
    }
  },
  created() {
    this.getFormats();
  },
  methods: {
    getFormats() {
  formService.getDocumentModels().then(response => {
    if (response.status) {
      this.formats = response.data.filter((format) => {
        return format.params !== "emundus_fileUpload";
      });
    }
  });
},
    setCloneFormat(format) {
      this.cloneFormat = format;
    },
    onDragEnd(event) {
	    const to = event.to;
	    if (to === null) {
		    return;
	    }

			this.cloneFormat.mandatory = to.id == 'required-documents' ? '1' : '0';
			this.$emit('open-create-document', this.cloneFormat);
    }
  },
  computed: {
	  displayedFormats() {
			return this.formats.filter((format) => {
				return this.search.length > 0 && this.formats.length > 0 ? format.name[this.shortDefaultLang].toLowerCase().includes(this.search.toLowerCase()) : true;
			});
	  }
  }
}
</script>

<style lang="scss">
#form-builder-document-formats {
  #form-builder-document-title {
    border-bottom: 1px solid black;
  }

  .draggable-element {
    width: 258px;
    height: 48px;
    font-size: 14px;
    background-color: #fafafa;
    border: 1px solid #f2f2f3;
    cursor: grab;
  }

	#format-name {
		white-space: nowrap;
		max-width: 100%;
		text-overflow: ellipsis;
		overflow: hidden;
	}
}
</style>
