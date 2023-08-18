<template>
  <div class="form-builder-page-section-element"
       :id="'element_'+element.id"
       v-show="(!element.hidden && element.publish !== -2) || (element.hidden && sysadmin)"
       :class="{'unpublished': !element.publish || element.hidden, 'properties-active':propertiesOpened == element.id}">
    <div class="em-flex-row em-flex-space-between em-w-100">
      <label class="em-w-100 em-flex-row fabrikLabel control-label fabrikTip" @click="triggerElementProperties">
        <i v-if="element.FRequire" data-isicon="true" class="icon-star small"></i>
	      <input
			      v-if="element.label_value && element.labelsAbove != 2"
			      :ref="'element-label-' + element.id"
			      :id="'element-label-' + element.id"
			      class="element-title editable-data"
			      :name="'element-label-' + element.id"
			      type="text"
			      v-model="element.label[shortDefaultLang]"
			      @focusout="updateLabel"
			      @keyup.enter="updateLabelKeyup"
	      />
      </label>
      <div id="element-action-icons" class="em-flex-row">
        <span class="material-icons-outlined handle em-grab">drag_indicator</span>
        <span id="delete-element" class="material-icons-outlined em-red-500-color em-pointer" @click="deleteElement">delete</span>
        <span v-if="sysadmin" class="material-icons-outlined em-pointer em-ml-8" @click="openAdmin">content_copy</span>
      </div>
    </div>
    <div :class="'element-field fabrikElement' + element.plugin" @click="triggerElementProperties">
      <form-builder-element-options
          v-if="['radiobutton', 'checkbox'].includes(element.plugin) || displayOptions && element.plugin === 'dropdown'"
          :element="element"
          :type="element.plugin == 'radiobutton' ? 'radio' : element.plugin"
          @update-element="$emit('update-element')"
      ></form-builder-element-options>
      <form-builder-element-wysiwig v-else-if="element.plugin === 'display'" :element="element" type="display" @update-element="$emit('update-element')"></form-builder-element-wysiwig>
      <div v-else v-html="element.element" class="fabrikElement"></div>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';
import formBuilderMixin from "../../mixins/formbuilder";
import mixin from "../../mixins/mixin";
import FormBuilderElementOptions from "./FormBuilderSectionSpecificElements/FormBuilderElementOptions";
import FormBuilderElementWysiwig from "./FormBuilderSectionSpecificElements/FormBuilderElementWysiwig";

export default {
  components: {
    FormBuilderElementWysiwig,
    FormBuilderElementOptions
  },
  props: {
    element: {
      type: Object,
      default: {}
    },
  },
  mixins: [formBuilderMixin,mixin],
  data() {
    return {
      keysPressed: [],
      options_enabled: false,
    }
  },
  methods: {
    updateLabel()
    {
      this.element.label[this.shortDefaultLang] = this.$refs['element-label-' + this.element.id].value.trim().replace(/[\r\n]/gm, "");

      formBuilderService.updateTranslation({value: this.element.id, key: 'element'}, this.element.label_tag, this.element.label).then((response) => {
				if (response.data.status) {
					this.element.label_tag = response.data.data;
					this.updateLastSave();
				} else {
					Swal.fire({
						title: this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR'),
						text: this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR_SAVE_TRANSLATION'),
						type: "error",
						cancelButtonText: this.translate("OK"),
					});
				}
      });
    },
	  updateLabelKeyup()
	  {
		  document.activeElement.blur();
		},
    updateElement()
    {
      formBuilderService.updateParams(this.element).then((response) => {
				if (response.data.status) {
					this.$emit('update-element');
					this.updateLastSave();
				} else {
					Swal.fire({
						title: this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR'),
						text: this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR_UPDATE_PARAMS'),
						type: "error",
						cancelButtonText: this.translate("OK"),
					});
				}
      });
    },
    deleteElement() {
      this.swalConfirm(
          this.translate("COM_EMUNDUS_FORM_BUILDER_DELETE_ELEMENT"),
          this.element.label[this.shortDefaultLang],
          this.translate("COM_EMUNDUS_FORM_BUILDER_DELETE_ELEMENT_CONFIRM"),
          this.translate("JNO"),
          () => {
            formBuilderService.deleteElement(this.element.id);
            this.$emit('delete-element', this.element.id);
            this.updateLastSave();

            this.tip("foo-velocity", this.translate("COM_EMUNDUS_FORM_BUILDER_DELETED_ELEMENT_TEXT"), this.translate("COM_EMUNDUS_FORM_BUILDER_DELETED_ELEMENT_TITLE"));
            window.addEventListener('keydown', this.cancelDelete);
          }
      );
    },
    openAdmin() {
      navigator.clipboard.writeText(this.element.id);
      Swal.fire({
        title: 'Identifiant de l\'élément copié',
        type: "success",
        showCancelButton: false,
        showConfirmButton: false,
        customClass: {
          title: 'em-swal-title',
        },
        timer: 1500
      });
    },
    triggerElementProperties() {
      this.$emit('open-element-properties');
    },
    cancelDelete(event) {
      let elementsPending = this.$parent.$parent.$parent.elementsDeletedPending;
      let index = elementsPending.indexOf(this.element.id)

      if(elementsPending.indexOf(this.element.id) === (elementsPending.length - 1)) {
        event.stopImmediatePropagation();
        this.keysPressed[event.key] = true;

        if ((this.keysPressed['Control'] || this.keysPressed['Meta']) && event.key === 'z') {
          formBuilderService.toggleElementPublishValue(this.element.id);
          this.$emit('cancel-delete-element', this.element.id);
          this.keysPressed = [];

          document.removeEventListener('keydown', this.cancelDelete);
          this.$parent.$parent.$parent.elementsDeletedPending.splice(index,1)
        }
      }
    }
  },
  computed: {
    sysadmin: function(){
      return parseInt(this.$store.state.global.sysadminAccess);
    },
    displayOptions: function(){
      return this.$parent.$parent.$parent.$parent.$parent.$parent.optionsSelectedElement
        && this.$parent.$parent.$parent.$parent.$parent.$parent.selectedElement !== null
        && this.$parent.$parent.$parent.$parent.$parent.$parent.selectedElement.id == this.element.id;
    },
    propertiesOpened: function(){
      if (this.$parent.$parent.$parent.$parent.$parent.$parent.selectedElement !== null) {
        return this.$parent.$parent.$parent.$parent.$parent.$parent.selectedElement.id;
      } else {
        return 0;
      }
    }
  }
}
</script>

<style lang="scss">
.form-builder-page-section-element {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
  margin: 12px 0;
  padding: 12px;
  border-radius: 4px;
  transition: 0.3s all;
  border: 2px solid transparent;

	.element-title {
		border: none !important;
		width: 100% !important;

		&:hover {
			border: none !important;
		}
	}

  .element-field:not(.fabrikElementdisplay) {
    .fabrikgrid_1.btn-default{
      padding: 12px;
      box-shadow: none;
      cursor: pointer;
      border: 1px solid var(--main-500);
      background: var(--main-500);
      border-radius: var(--em-coordinator-br) !important;
      width: 100% !important;
      max-width: 250px;
      display: flex;
      justify-content: center;

      span {
        margin-top: 0;
        color: var(--neutral-50) !important;
      }
    }

    .fabrikgrid_0.btn-default {
      padding: 12px;
      box-shadow: none;
      cursor: pointer;
      border: 1px solid var(--red-500);
      background: var(--red-500);
      border-radius: var(--em-coordinator-br) !important;
      width: 100% !important;
      max-width: 250px;
      display: flex;
      justify-content: center;

      span {
        margin-top: 0;
        color: var(--neutral-50) !important;
      }
    }
  }

  &.unpublished {
    opacity: 0.5;
  }

  &.properties-active{
    border: 2px solid #1C6EF2 !important;
  }

  &:hover {
    border: 2px solid var(--main-500);

    #element-action-icons {
      opacity: 1;
      pointer-events: all;
    }
  }

  #element-action-icons {
    transition: 0.3s all;
    opacity: 0;
    pointer-events: none;
    .icon-handle{
      width: 18px;
      height: 18px;
    }
  }

  .element-field {
    width: 100%;

    &.element-preview-display .fabrikinput {
      height: auto;
      border: 0;
      padding: 4px 8px !important;
      &:hover{
        border: 0;
      }
    }
  }

  .element-required {
    width: 48px;
    height: 24px;
    margin-top:15px;

    input:checked + .em-slider:before {
      transform: translateX(22px);
    }

    .em-slider {
      border-radius: 24px;

      &::before {
        height: 14px;
        width: 14px;
        bottom: 5px;
      }
    }
  }

  input:hover{
    border: 1px solid var(--neutral-600);
    box-shadow: none !important;
  }
}
</style>
