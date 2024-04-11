<template>
  <div id="incremental-selector">
    <div class="em-w-100 em-flex-row em-mb-16">
	    <div v-if="isNewVal" id="new-value" class="em-w-100">
		    <input type="text" class="em-w-100 em-mb-0-important" v-model="newValue.label" @focusin="showOptions = true" @focusout="emitValueChanges"/>
		    <i class="em-main-500-color">({{ translate('COM_EMUNDUS_FORM_BUILDER_NEW_VALUE') }})</i>
	    </div>
      <div v-if="!isNewVal" id="existing-value" class="em-w-100">
	      <div class="em-w-100 em-flex-row em-flex-space-between">
		      <input type="text" class="em-w-100 em-mb-0-important em-border-main-500 important" v-model="newExistingLabel" @focusout="emitValueChanges"/>
		      <span v-if="!locked" @click="unselectExistingValue" class="material-icons-outlined em-pointer" @mouseenter="hoverUnselect = true" @mouseleave="hoverUnselect = false">close</span>
	      </div>
	      <i class="em-main-500-color">({{ translate('COM_EMUNDUS_FORM_BUILDER_EXISTING_VALUE') }})</i>
      </div>
    </div>
    <ul v-if="existingValues && showOptions" class="em-custom-selector em-border-neutral-300 em-w-100" @mouseenter="hoverOptions = true" @mouseleave="hoverOptions = false">
      <li v-for="option in displayedOptions"
          :key="option.id"
          :value="option.id"
          :selected="selectedExistingValue == option.id"
          class="em-custom-selector-option em-pointer  em-p-8"
          @click="onSelectValue(option.id)"
      >
        {{ option.label }}
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    options: {
      type: Array,
      required: true
    },
    defaultValue: {
      required: false,
      default: null
    },
	  locked: {
			type: Boolean,
		  default: false
	  }
  },
  data() {
    return {
			originalOptions: [],
      newValue: {
        id: 0,
        label: ''
      },
      existingValues: [],
      newExistingLabel: '',
      selectedExistingValue: -1,
      isNewVal: true,
      showOptions: false,
	    hoverOptions: false,
	    hoverUnselect: false
    }
  },
  beforeMount() {
		this.originalOptions = JSON.parse(JSON.stringify(this.options));
    this.existingValues = this.options;
  },
  mounted() {
    if (this.defaultValue != null) {
      this.onSelectValue(this.defaultValue);
    }
  },
  methods: {
    updateDefaultValue() {
      this.onSelectValue(this.defaultValue);
    },
    onSelectValue(value) {
      this.selectedExistingValue = value;
      if (this.selectedExistingValue === -1) {
        this.unselectExistingValue();
      } else {
        this.isNewVal = false;
        let detachedValue = this.existingValues.find((test) => {
          return test.id === this.selectedExistingValue;
        });

        if (detachedValue) {
          detachedValue = JSON.parse(JSON.stringify(detachedValue));
          this.newExistingLabel = detachedValue.label;
          this.newValue = detachedValue;
          this.emitValueChanges();
        } else {
					this.unselectExistingValue();
        }
      }
      this.showOptions = false;
    },
    unselectExistingValue() {
      this.isNewVal = true;
			let foundValue = this.existingValues.find((existingValue) => {
				return existingValue.id == this.selectedExistingValue;
			});
	    this.newExistingLabel = foundValue ? foundValue.label : '';
			this.selectedExistingValue = -1;
      this.newValue.label = '';
      this.newValue.id = 0;
			this.existingValues = JSON.parse(JSON.stringify(this.originalOptions));
      this.showOptions = false;
			this.hoverOptions = false;
			this.hoverUnselect = false;
      this.emitValueChanges();
    },
    emitValueChanges(event = null) {
	    if (this.hoverUnselect) {
				return;
	    }

	    if (this.showOptions && !this.hoverOptions) {
				this.showOptions = false;
			}

      if (this.isNewVal) {
        this.$emit('update-value', this.newValue);
      } else {
        const newValue = this.existingValues.find((value) => {
          return value.id === this.selectedExistingValue;
        });

        if (newValue.label !== this.newExistingLabel) {
          newValue.label = this.newExistingLabel;
        }
	      this.$emit('update-value', newValue);
      }
    }
  },
  computed: {
    displayedOptions() {
      return !this.isNewVal ? this.existingValues : this.existingValues.filter((existingDoc) => {
        return existingDoc.id !== -1 ? existingDoc.label.toLowerCase().includes(this.newValue.label.toLowerCase()) : true;
      });
    }
  },
  watch: {
    defaultValue: {
      handler(newValue) {
        this.updateDefaultValue();
      }
    }
  }
}
</script>

<style lang="scss">
#incremental-selector {
	position: relative;

	.em-custom-selector {
		margin: 0;
		list-style: none;
		background: white;
		position: absolute;
		top: 42px;
		max-height: 33vh;
		overflow-y: auto;

		li {
			transition: .3s all;
			&:hover {
				background: #f8f8f8;
			}
		}
	}

	#existing-value {
		position: relative;

		input {
			padding-right: 25px;
		}

		span {
			position: absolute;
			right: 5px;
			top: 12px;
		}
	}
}
</style>