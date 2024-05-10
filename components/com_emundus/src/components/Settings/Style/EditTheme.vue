<template>
  <div>
    <div class="flex items-center gap-2" v-if="primary && secondary">
      <div class="w-full flex flex-col gap-2">
        <div class="flex justify-between items-center">
          <label class="font-medium">{{ translate("COM_EMUNDUS_ONBOARD_PRIMARY_COLOR") }}</label>
          <div class="flex items-center">
            <input type="color"
                   class="custom-color-picker"
                   v-model="primary"
                   id="primary_color"/>
          </div>
        </div>

        <div class="flex justify-between items-center">
          <label class="font-medium">{{ translate("COM_EMUNDUS_ONBOARD_SECONDARY_COLOR") }}</label>
          <div>
            <input type="color" v-model="secondary" class="custom-color-picker" id="secondary_color"/>
          </div>
        </div>
      </div>

      <div
          class="w-32 rounded-md p-3 text-center cursor-help"
          :class="contrastRatio > 3.1 && rgaaState === 1 ? 'bg-main-50' : 'bg-red-50'"
      >
        <div :class="rgaaState === 1 ? 'text-green-500' : 'text-red-500'">
          <label class="text-xs !mb-0 cursor-help">RGAA</label>
          <span class="material-icons-outlined text-green-500" v-if="rgaaState === 1">check_circle</span>
          <span class="material-icons-outlined text-red-500" v-else>report_problem</span>
        </div>

        <div :class="contrastRatio > 3.1 ? 'text-green-500' : 'text-red-500'">
          <label class="text-xs !mb-0 cursor-help">Contrast ratio</label>
          <span class="text-xs font-medium">{{ Math.round(contrastRatio * 100) / 100 }}</span>
        </div>

      </div>
    </div>

    <button class="mt-3 btn btn-primary float-right" v-if="changes" @click="saveColors">
      {{ translate("COM_EMUNDUS_ONBOARD_SETTINGS_GENERAL_SAVE") }}
    </button>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import settingsService from "@/services/settings";
import axios from "axios";
import qs from "qs";
import Swal from "sweetalert2";

export default {
  name: "global",
  props: {},
  components: {},
  data() {
    return {
      RED: 0.2126,
      GREEN: 0.7152,
      BLUE: 0.0722,
      GAMMA: 2.4,

      loading: false,

      primary: null,
      secondary: null,
      changes: false,

      rgaaState: 0,
      contrastRatio: 0.00
    }
  },

  async created() {
    this.loading = true;
    this.changes = false;

    await this.getAppColors();
    //await this.getVariable();

    this.loading = false;
  },

  methods: {
    getVariable() {
      return new Promise((resolve) => {
        axios({
          method: "get",
          url: 'index.php?option=com_emundus&controller=settings&task=getappVariablegantry',
        }).then((rep) => {
          console.log(rep.data);

          resolve(true);
        });
      });
    },
    changeVariables(preset) {
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=settings&task=updateVariablegantry",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          preset: preset,
        })
      }).then(() => {
        console.log("jojo");
      });
    },


    getAppColors() {
      return new Promise((resolve) => {
        axios({
          method: "get",
          url: 'index.php?option=com_emundus&controller=settings&task=getappcolors',
        }).then((rep) => {
          this.primary = rep.data.primary;
          this.secondary = rep.data.secondary;

          this.rgaaState = this.checkSimilarity(this.primary, this.secondary);
          this.contrastRatio = this.checkContrast('#FFFFFF', this.primary);
          if(this.contrastRatio > 3.1) {
            this.contrastRatio = this.checkContrast('#FFFFFF', this.secondary);
          }

          resolve(true);
        });
      });
    },

    async saveColors() {
      let preset = {id: 7, primary: this.primary, secondary: this.secondary};
      settingsService.saveColors(preset).then((response) => {
        if(response.status === true) {
          Swal.fire({
            title: this.translate("COM_EMUNDUS_ONBOARD_SUCCESS"),
            text: this.translate("COM_EMUNDUS_ONBOARD_SETTINGS_THEME_SAVE_SUCCESS"),
            showCancelButton: false,
            showConfirmButton: false,
            customClass: {
              title: 'em-swal-title'
            },
            timer: 2000
          });
        }
      });
    },

    async saveMethod() {
      await this.saveColors();
      return true;
    },

    checkSimilarity(hex1, hex2, container) {
      let rgb1 = this.hexToRgb(hex1);
      let rgb2 = this.hexToRgb(hex2);
      const deltaECalc = this.deltaE(rgb1, rgb2);

      if (deltaECalc < 11) {
        return 0;
      } else {
        return 1;
      }
    },

    checkContrast(hex1, hex2, container) {
      let rgb1 = this.hexToRgb(hex1);
      let rgb2 = this.hexToRgb(hex2);
      return this.contrast(rgb1, rgb2);
    },

    /* Utilities function */
    deltaE(rgbA, rgbB) {
      let labA = this.rgb2lab(rgbA);
      let labB = this.rgb2lab(rgbB);
      let deltaL = labA[0] - labB[0];
      let deltaA = labA[1] - labB[1];
      let deltaB = labA[2] - labB[2];
      let c1 = Math.sqrt(labA[1] * labA[1] + labA[2] * labA[2]);
      let c2 = Math.sqrt(labB[1] * labB[1] + labB[2] * labB[2]);
      let deltaC = c1 - c2;
      let deltaH = deltaA * deltaA + deltaB * deltaB - deltaC * deltaC;
      deltaH = deltaH < 0 ? 0 : Math.sqrt(deltaH);
      let sc = 1.0 + 0.045 * c1;
      let sh = 1.0 + 0.015 * c1;
      let deltaLKlsl = deltaL / (1.0);
      let deltaCkcsc = deltaC / (sc);
      let deltaHkhsh = deltaH / (sh);
      let i = deltaLKlsl * deltaLKlsl + deltaCkcsc * deltaCkcsc + deltaHkhsh * deltaHkhsh;
      return i < 0 ? 0 : Math.sqrt(i);
    },
    rgb2lab(rgb) {
      let r = rgb[0] / 255, g = rgb[1] / 255, b = rgb[2] / 255, x, y, z;
      r = (r > 0.04045) ? Math.pow((r + 0.055) / 1.055, 2.4) : r / 12.92;
      g = (g > 0.04045) ? Math.pow((g + 0.055) / 1.055, 2.4) : g / 12.92;
      b = (b > 0.04045) ? Math.pow((b + 0.055) / 1.055, 2.4) : b / 12.92;
      x = (r * 0.4124 + g * 0.3576 + b * 0.1805) / 0.95047;
      y = (r * 0.2126 + g * 0.7152 + b * 0.0722) / 1.00000;
      z = (r * 0.0193 + g * 0.1192 + b * 0.9505) / 1.08883;
      x = (x > 0.008856) ? Math.pow(x, 1 / 3) : (7.787 * x) + 16 / 116;
      y = (y > 0.008856) ? Math.pow(y, 1 / 3) : (7.787 * y) + 16 / 116;
      z = (z > 0.008856) ? Math.pow(z, 1 / 3) : (7.787 * z) + 16 / 116;
      return [(116 * y) - 16, 500 * (x - y), 200 * (y - z)]
    },
    hexToRgb(hex) {
      return hex.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i
          , (m, r, g, b) => '#' + r + r + g + g + b + b)
          .substring(1).match(/.{2}/g)
          .map(x => parseInt(x, 16));
    },
    luminance(r, g, b) {
      var a = [r, g, b].map((v) => {
        v /= 255;
        return v <= 0.03928
            ? v / 12.92
            : Math.pow((v + 0.055) / 1.055, GAMMA);
      });
      return a[0] * RED + a[1] * GREEN + a[2] * BLUE;
    },
    contrast(rgb1, rgb2) {
      var lum1 = this.luminance(...rgb1);
      var lum2 = this.luminance(...rgb2);
      var brightest = Math.max(lum1, lum2);
      var darkest = Math.min(lum1, lum2);
      return (brightest + 0.05) / (darkest + 0.05);
    },
  },
  watch: {
    primary: function(val,oldVal) {
      if(oldVal !== null) {
        this.$emit('needSaving', true)
        this.changes = true;
        this.rgaaState = this.checkSimilarity(val, this.secondary);
        this.contrastRatio = this.checkContrast('#FFFFFF', val);
      }
    },
    secondary: function(val,oldVal) {
      if(oldVal !== null) {
        this.$emit('needSaving', true)
        this.changes = true;
        this.rgaaState = this.checkSimilarity(val, this.primary);
        this.contrastRatio = this.checkContrast('#FFFFFF', val);
      }
    },
  }
}
</script>

<style scoped>
.custom-color-picker {
  width: 44px !important;
  height: 48px !important;
  border: none !important;
  padding: 0 !important;
  outline: none;
  cursor: pointer;
}
.custom-color-picker::-webkit-color-swatch {
  border-radius: 100%;
}
</style>
