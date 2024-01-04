<template>
  <!-- modalC -->
  <span :id="'modalUpdateColors'">
    <modal
        :name="'modalUpdateColors'"
        height="auto"
        :min-width="200"
        :min-height="200"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
    >

      <div class="em-modal-header">
        <div class="em-flex-space-between em-flex-row em-pointer" @click.prevent="$modal.hide('modalUpdateColors')">
          <div class="em-w-max-content em-flex-row">
            <span class="material-icons-outlined">arrow_back</span>
            <span class="em-ml-8">{{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-5 gap-y-0 gap-x-6">
        <div v-for="(preset) in presets" :key="preset.id" class="preset-presentation"
             :class="preset.selected ? 'outline-green-500' : ''"
             :style="'background-color:' + preset.primary + ';border-right: 50px solid' + preset.secondary"
             @click="!preset.custom ? changeColors(preset) : openCustomPalette()">
          <span class="material-icons-outlined text-white p-3" v-if="preset.custom">color_lens</span>
          <span class="material-icons-outlined text-yellow-500" v-if="!preset.rgaa">warning</span>
        </div>
        <div class="preset-presentation flex items-center justify-center" v-if="presets[presets.length-1].custom === false" @click="openCustomPalette">
          <span class="material-icons-outlined !text-6xl">add_circle_outline</span>
        </div>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

const qs = require("qs");

export default {
  name: "modalUpdateColors",
  props: {
    primary: String,
    secondary: String,
  },
  components: {},
  data() {
    return {
      presets: [
        {id: 1, primary: '#1b1f3c', secondary: '#de6339', selected: false, custom: false, rgaa: true},
        {id: 2, primary: '#DA3832', secondary: '#204382', selected: false, custom: false, rgaa: true},
        {id: 3, primary: '#727378', secondary: '#A0BD51', selected: false, custom: false, rgaa: true},
        {id: 4, primary: '#727378', secondary: '#000000', selected: false, custom: false, rgaa: true},
        {id: 5, primary: '#000000', secondary: '#5AA6DC', selected: false, custom: false, rgaa: true},
        {id: 6, primary: '#A0BD51', secondary: '#204382', selected: false, custom: false, rgaa: true},
      ],
      updateColors: this.translate("COM_EMUNDUS_ONBOARD_UPDATE_COLORS"),
      Retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      Error: this.translate("COM_EMUNDUS_ONBOARD_ERROR"),
    };
  },

  mounted() {
    let new_preset = {id: 7, primary: this.$props.primary, secondary: this.$props.secondary, selected: true, custom: true, rgaa: true};
    this.presets.forEach((preset) => {
      if(preset.primary == this.primary && preset.secondary == this.secondary) {
        new_preset = null;
        preset.selected = true;
      }
    });

    if(new_preset) {
      const primary_contrast = checkContrast('#FFFFFF', this.primary);
      const secondary_contrast = checkContrast('#FFFFFF', this.secondary);
      const similiraty = checkSimilarity(this.primary, this.secondary);

      if(!primary_contrast || !secondary_contrast || !similiraty) {
        new_preset.rgaa = false;
      }

      this.presets.push(new_preset);
    }
  },

  methods: {
    changeColors(preset) {
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=settings&task=updatecolor",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          preset: preset,
        })
      }).then(() => {
        this.$emit("UpdateColors", preset);
        this.$modal.hide('modalUpdateColors');
      });
    },

    openCustomPalette() {
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_CUSTOM_PALETTE"),
        html: `<div><label>`+this.translate("COM_EMUNDUS_ONBOARD_PRIMARY_COLOR")+`</label><div class="flex items-center"><input type="color" onchange="document.querySelector('#hexacode-primary').innerHTML = event.srcElement.value;checkContrast('#FFFFFF',event.srcElement.value,'#swal2-content');checkSimilarity(event.srcElement.value,document.querySelector('#secondary_color').value,'#swal2-content')" class="custom-color-picker" value="`+this.primary+`" id="primary_color" /><span id="hexacode-primary" class="ml-4">`+this.primary+`</span></div></div><div class="mt-4 mb-3"><label>`+this.translate("COM_EMUNDUS_ONBOARD_SECONDARY_COLOR")+`</label><div><input type="color" value="`+this.secondary+`" class="custom-color-picker" id="secondary_color" onchange="document.querySelector('#hexacode-secondary').innerHTML = event.srcElement.value;checkContrast('#FFFFFF',event.srcElement.value,'#swal2-content');checkSimilarity(event.srcElement.value,document.querySelector('#primary_color').value,'#swal2-content')" /><span id="hexacode-secondary" class="ml-4">`+this.secondary+`</span></div></div></div>`,
        showCancelButton: true,
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
        showLoaderOnConfirm: false,
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
        preConfirm: () => {
          let primary = document.querySelector('#primary_color').value;
          let secondary = document.querySelector('#secondary_color').value;

          if(primary == secondary) {
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_ERROR"),
              text: this.translate("COM_EMUNDUS_ONBOARD_ERROR_COLORS_SAME"),
              type: "error",
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              customClass: {
                title: 'em-swal-title',
                actions: "em-swal-single-action",
                confirmButton: 'em-swal-confirm-button',
              },
            });
            return false;
          }

          let preset = {id: 7, primary: primary, secondary: secondary};
          return axios({
            method: "post",
            url: "index.php?option=com_emundus&controller=settings&task=updatecolor",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              preset: preset,
            })
          }).then(() => {
            this.$emit("UpdateColors", preset);
          });
        },
        allowOutsideClick: () => !Swal.isLoading()
      }).then((result) => {
        if (result.value) {
          Swal.fire({
            title: this.translate("COM_EMUNDUS_ONBOARD_COLOR_SUCCESS"),
            type: "success",
            showConfirmButton: false,
            customClass: {
              title: 'em-swal-title',
            },
            timer: 2000,
          }).then(() => {
            this.$modal.hide('modalUpdateColors');
          });
        }
      });

      checkContrast('#FFFFFF',this.primary,'#swal2-content');
      checkContrast('#FFFFFF',this.secondary,'#swal2-content');
      checkSimilarity(this.primary,this.secondary,'#swal2-content');
    },
  }
};
</script>

<style>
.fa-file-image {
  font-size: 25px;
  margin-right: 20px;
}

.preset-presentation {
  height: 150px;
  margin: 24px 0;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
  outline: solid 6px var(--neutral-400);
}

.preset-presentation:hover {
  transform: scale(1.02);
}

.custom-color-picker {
  width: 48px !important;
  height: 52px !important;
  border: none !important;
  padding: 0 !important;
  outline: none;
  cursor: pointer;
}
.custom-color-picker::-webkit-color-swatch {
  border-radius: 100%;
}

.modalC-content {
  margin-top: 20%;
}
</style>
