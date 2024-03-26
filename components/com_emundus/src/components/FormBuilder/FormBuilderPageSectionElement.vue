<template>
  <div class="form-builder-page-section-element"
       :id="'element_'+element.id"
       v-show="(!element.hidden && element.publish !== -2) || (element.hidden && sysadmin)"
       :class="{'unpublished': !element.publish || element.hidden, 'properties-active':propertiesOpened == element.id}">
    <div class="flex items-start justify-between w-full mb-2">
      <div class="w-11/12">
        <label class="em-w-100 flex items-center fabrikLabel control-label mb-0" @click="triggerElementProperties">
          <span v-if="element.FRequire" class="material-icons text-xxs text-red-500 mr-0" style="top: -5px;position: relative">emergency</span>
          <input
              v-if="element.label_value && element.labelsAbove != 2"
              :ref="'element-label-' + element.id"
              :id="'element-label-' + element.id"
              class="ml-2 element-title editable-data"
              :name="'element-label-' + element.id"
              type="text"
              v-model="element.label[shortDefaultLang]"
              @focusout="updateLabel"
              @keyup.enter="updateLabelKeyup"
          />
        </label>
        <span class="fabrikElementTip fabrikElementTipAbove">{{ element.params.rollover.replace(/(<([^>]+)>)/gi, "") }}</span>
      </div>
      <div id="element-action-icons" class="flex items-end mt-2">
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
      <form-builder-element-phone-number v-else-if="element.plugin === 'emundus_phonenumber'" type="phonenumber" :element="element"></form-builder-element-phone-number>
      <form-builder-element-currency v-else-if="element.plugin === 'currency'" type="currency" :element="element"></form-builder-element-currency>
      <form-builder-element-geolocation v-else-if="element.plugin === 'emundus_geolocalisation'" type="geolocation" :element="element"></form-builder-element-geolocation>
      <form-builder-element-emundus-file-upload v-else-if="element.plugin === 'emundus_fileupload'" type="fileupload" :element="element"></form-builder-element-emundus-file-upload>
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
import FormBuilderElementPhoneNumber
  from "@/components/FormBuilder/FormBuilderSectionSpecificElements/FormBuilderElementPhoneNumber.vue";
import FormBuilderElementCurrency
  from "@/components/FormBuilder/FormBuilderSectionSpecificElements/FormBuilderElementCurrency.vue";
import FormBuilderElementGeolocation
  from "@/components/FormBuilder/FormBuilderSectionSpecificElements/FormBuilderElementGeolocation.vue";
import FormBuilderElementEmundusFileUpload
  from "@/components/FormBuilder/FormBuilderSectionSpecificElements/FormBuilderElementEmundusFileUpload.vue";

export default {
  components: {
    FormBuilderElementEmundusFileUpload,
    FormBuilderElementGeolocation,
    FormBuilderElementCurrency,
    FormBuilderElementPhoneNumber,
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
  },created() {
    console.log('jojo');
    console.log(this.element);
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
      border: 1px solid var(--em-profile-color);
      border-radius: var(--em-coordinator-br) !important;
      width: 100% !important;
      max-width: 250px;
      display: flex;
      justify-content: center;

      span {
        margin-top: 0;
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
        color: var(--neutral-0) !important;
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
    border: 2px solid var(--em-profile-color);

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

  .fabrikElementTip {
    color: var(--em-form-tip-color);
    font-size: 12px;
    line-height: 1.5rem;
    font-weight: 400;
    font-family: var(--em-applicant-font);
    font-style: normal;
    display: flex;
  }

  /* YES / NO */
  /* And radio buttons grouped together */

  .fabrikElementyesno .fabrikSubElementContainer .btn-group {
    width: 48.93617021276595%;
  }

  @media only all and (min-width: 48rem) and (max-width: 59.99rem) {
    .fabrikElementyesno .fabrikSubElementContainer .btn-group {
      width: 48.6187845304% !important;
    }
  }

  @media only all and (max-width: 48rem) {
    .fabrikElementyesno .fabrikSubElementContainer .btn-group {
      width: 100% !important;
    }
  }

  .fabrikElementyesno .fabrikSubElementContainer .btn-group {
    display: flex;
    gap: var(--em-form-yesno-gap);
  }

  label.btn-default.btn.btn-success.active {
    padding: var(--p-12);
    box-shadow: none;
    cursor: pointer;
    background-color: var(--em-form-yesno-bgc-yes);
    border: var(--em-form-yesno-bw) solid var(--em-form-yesno-bc-yes);
    color: var(--neutral-900);
    border-radius: var(--em-applicant-br) !important;
    width: var(--em-form-yesno-width) !important;
    display: flex;
    align-items: center;
    justify-content: center;
    height: var(--em-form-yesno-height);
    font-size: 16px;
    font-style: normal;
    line-height: 24px;
    letter-spacing: 0.0015em;
  }

  label.btn-default.btn.btn-success.active:hover {
    background-color: var(--em-form-yesno-bgc-yes-hover);
    border-color: var(--em-form-yesno-bc-yes-hover) !important;
  }

  label.btn-default.btn.btn-success.active:hover span {
    font-family: var(--em-default-font);
    font-size: 16px;
    font-style: normal;
    line-height: 24px;
    letter-spacing: 0.0015em;
    color: var(--em-form-yesno-color-yes-hover);
    word-wrap: break-word;
  }

  label.btn-default.btn.btn-success.active span {
    font-family: var(--em-default-font);
    font-size: 16px;
    font-style: normal;
    line-height: 24px;
    letter-spacing: 0.0015em;
    color: var(--em-form-yesno-color-yes);
    word-wrap: break-word;
  }

  label.btn-default.btn.btn-danger.active {
    height: var(--em-form-yesno-height);
    padding: var(--p-12);
    box-shadow: none;
    cursor: pointer;
    background-color: var(--em-form-yesno-bgc-no);
    border-color: var(--em-form-yesno-bc-no);
    color: var(--em-form-yesno-color-no);
    border-radius: var(--em-applicant-br) !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-style: normal;
    line-height: 24px;
    letter-spacing: 0.0015em;
    width: var(--em-form-yesno-width) !important;
  }


  label.btn-default.btn.btn-danger.active:hover {
    background-color: var(--em-form-yesno-bgc-no-hover);
    border-color: var(--em-form-yesno-bc-no-hover)!important;
  }

  label.btn-default.btn.btn-danger.active:hover span {
    font-family: var(--em-default-font);
    font-size: 16px;
    font-style: normal;
    line-height: 24px;
    letter-spacing: 0.0015em;
    color: var(--em-form-yesno-color-no-hover);
    word-wrap: break-word;
  }

  label.btn-default.btn.btn-danger.active span {
    font-family: var(--em-default-font);
    font-size: 16px;
    font-style: normal;
    line-height: 24px;
    letter-spacing: 0.0015em;
    color: var(--em-form-yesno-color-no);
    word-wrap: break-word;
  }

  label.btn-default.btn:not(.active) {
    height: var(--em-form-yesno-height);
    padding: var(--p-12);
    box-shadow: none;
    cursor: pointer;
    border: var(--em-form-yesno-bw) solid var(--em-form-yesno-bc-not-active);
    background: var(--em-form-yesno-bgc-not-active);
    color: var(--em-form-yesno-color-not-active);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    line-height: 24px;
    font-style: normal;
    letter-spacing: 0.0015em;
    width: var(--em-form-yesno-width) !important;
  }

  label.btn-default.btn:not(.active):hover {
    background-color: var(--em-form-yesno-bgc-not-active-hover);
    border-color: var(--em-form-yesno-bc-not-active-hover) !important;
  }

  label.btn-default.btn:not(.active):hover span {
    font-family: var(--em-default-font);
    font-size: 16px;
    font-style: normal;
    line-height: 24px;
    letter-spacing: 0.0015em;
    color: var(--em-form-yesno-color-not-active-hover);
    word-wrap: break-word;
  }

  label.btn-default.btn:not(.active) span {
    font-family: var(--em-default-font);
    font-size: 16px;
    font-style: normal;
    line-height: 24px;
    letter-spacing: 0.0015em;
    color: var(--em-form-yesno-color-not-active);
    word-wrap: break-word;
  }

  /** PANEL **/
  .fabrikElementpanel .fabrikElement .fabrikinput {
    display: flex;
    padding: var(--em-spacing-5);
    border-radius: 0.25rem;

    .fabrikElementContent {
      margin-left: var(--em-spacing-3);
      line-height: 24px;
    }
  }
}
</style>
