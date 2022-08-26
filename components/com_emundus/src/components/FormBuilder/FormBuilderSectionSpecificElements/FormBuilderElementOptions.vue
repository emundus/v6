<template>
  <div id="form-builder-radio-button">
    <div v-if="loading" class="em-loader"></div>
    <div v-else>
      <draggable
          v-model="arraySubValues"
          handle=".handle-options"
          @end="updateOrder">
        <div class="element-option em-flex-row em-flex-space-between em-mt-8 em-mb-8" v-for="(option, index) in arraySubValues" :key="option" @mouseover="optionHighlight = index;" @mouseleave="optionHighlight = null">
          <div class="em-flex-row em-w-100">
            <div class="em-flex-row">
              <span class="icon-handle" :style="optionHighlight === index ? 'opacity: 1' : 'opacity: 0'">
                <span class="material-icons-outlined handle-options em-grab">drag_indicator</span>
              </span>
            </div>
            <input v-if="type !== 'dropdown'" :type="type" :name="'element-id-' + element.id" :value="optionsTranslations[index]">
            <div v-else>{{ index+1 }}. </div>
            <input
                type="text"
                class="editable-data editable-data-input em-ml-4 em-w-100"
                v-model="optionsTranslations[index]"
                @focusout="updateOption(index, optionsTranslations[index])"
                :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_ADD_OPTION')">
          </div>
          <div class="em-flex-row">
            <span class="material-icons-outlined em-pointer" @click="removeOption(index)" :style="optionHighlight === index ? 'opacity: 1' : 'opacity: 0'">close</span>
          </div>
        </div>
      </draggable>
      <div id="add-option" class="em-flex-row em-flex-start em-s-justify-content-center">
        <span class="icon-handle" style="opacity: 0">
          <span class="material-icons-outlined handle-options">drag_indicator</span>
        </span>
        <input v-if="type !== 'dropdown'" :type="type" :name="'element-id-' + element.id">
        <div v-else>{{ element.params.sub_options.sub_labels.length+1 }}. </div>
        <input
          type="text"
          class="editable-data editable-data-input em-ml-4 em-w-100"
          v-model="newOption"
          @focusout="addOption"
          :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_ADD_OPTION')">
      </div>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../../services/formbuilder';
import draggable from "vuedraggable";

export default {
  props: {
    element: {
      type: Object,
      required: true
    },
    type: {
      type: String,
      required: true
    }
  },
  components: {
    draggable,
  },
  data() {
    return {
      loading: false,
      newOption: '',
      arraySubValues: [],
			optionsTranslations: [],
      optionHighlight: null,
    };
  },
  created () {
	  this.getSubOptionsTranslation();
  },
  methods: {
	  async reloadOptions() {
			this.loading = true;
			formBuilderService.getElementSubOptions(this.element.id).then((response) => {
				if (response.data.status) {
					this.element.params.sub_options = response.data.new_options;
					this.getSubOptionsTranslation();
				} else {
					this.loading = false;
				}
			});
	  },
    async getSubOptionsTranslation() {
      this.loading = true;

      formBuilderService.getJTEXTA(this.element.params.sub_options.sub_labels).then(response => {
				if (response) {
					this.optionsTranslations = Object.values(response.data);
					this.arraySubValues = this.element.params.sub_options.sub_values.map((value, i) => {
						return {
							'sub_value' : value,
							'sub_label' :  this.element.params.sub_options.sub_labels[i],
						};
					});
				}

        this.loading = false;
      });
    },
    addOption() {
			if (this.newOption.trim() == '') {
				return;
			}

			this.loading = true;
	    formBuilderService.addOption(this.element.id, this.newOption, this.shortDefaultLang).then((response) => {
				this.newOption = '';
				if (response.data.status) {
					this.reloadOptions();
				}
				this.loading = false;
	    })
    },
    updateOption(index, option) {
	    this.loading = true;
	    formBuilderService.updateOption(this.element.id, this.element.params.sub_options, index, option, this.shortDefaultLang).then((response) => {
				if (response.data.status) {
					this.reloadOptions();
				} else {
					this.loading = false;
				}
	    });
    },
    updateOrder() {
	    if (this.arraySubValues.length > 1) {
		    let sub_options_in_new_order = {
			    sub_values: [],
			    sub_labels: []
		    };

		    this.arraySubValues.forEach((value, i) => {
					sub_options_in_new_order.sub_values.push(value.sub_value);
					sub_options_in_new_order.sub_labels.push(value.sub_label);
				});

				if (!this.element.params.sub_options.sub_values.every((value, index) => value === sub_options_in_new_order.sub_values[index])) {
					this.loading = true;
					formBuilderService.updateElementSubOptionsOrder(this.element.id, this.element.params.sub_options, sub_options_in_new_order).then((response) => {
						if (response.data.status) {
							this.reloadOptions();
						} else {
							this.loading = false;
						}
					});
				} else {
					console.log('No need to call reorder, same order');
				}
			} else {
				console.log('No need to reorder, only one element');
	    }
    },
    removeOption(index) {
	    this.loading = true;
	    formBuilderService.deleteElementSubOption(this.element.id, index).then((response) => {
		    if (response.data.status) {
					this.reloadOptions();
		    } else {
			    this.loading = false;
		    }
	    });
    }
  }
}
</script>

<style lang="scss">
.editable-data-input {
  padding: 0 !important;
  height: auto !important;
  border: unset !important;
  border-bottom: solid 2px transparent !important;
  border-radius: 0 !important;

  &:focus {
    outline: none !important;
    box-shadow: unset !important;
    border-bottom: solid 2px #20835F !important;
    border-radius: 0 !important;
  }

  &:hover{
    box-shadow: unset !important;
    border-bottom: solid 2px rgba(32, 131, 95, 0.87) !important;
    border-radius: 0 !important;
  }
}
.element-option,#add-option{
  .icon-handle{
    height: 18px;
    width: 18px;
    transition: all .3s;
  }
}
</style>
