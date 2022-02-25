<template>
  <div class="em-settings-menu">
    <div class="em-w-80">

      <div class="em-grid-3 em-mb-16">
        <button @click="pushStatus" class="em-primary-button em-mb-24" style="width: max-content">
          <div class="add-button-div">
            <em class="fas fa-plus em-mr-4"></em>
            {{ translate('COM_EMUNDUS_ONBOARD_ADD_STATUS') }}
          </div>
        </button>
      </div>

      <draggable
          handle=".handle"
          v-model="status"
          :class="'draggables-list'"
          @end="updateStatusOrder"
      >
        <div v-for="(statu, index) in status" class="em-mb-24" :title="'step_' + statu.step" :id="'step_' + statu.step" @mouseover="enableGrab(index)" @mouseleave="disableGrab()">
          <div class="em-flex-row em-flex-row-start em-w-100">
            <span class="handle" :style="grab && indexGrab == index ? 'opacity: 1' : 'opacity: 0'">
              <span class="material-icons">drag_indicator</span>
            </span>
            <div class="status-field">
              <div>
                <p class="em-p-8-12 em-editable-content" contenteditable="true" :id="'status_label_' + statu.step" @focusout="updateStatus(statu)" @keyup.enter="updateStatus(statu)">{{statu.label[actualLanguage]}}</p>
              </div>
              <input type="hidden" :class="'label-' + statu.class">
            </div>
            <div class="em-flex-row">
              <v-swatches
                  v-model="statu.class"
                  @input="updateStatus(statu)"
                  :swatches="swatches"
                  shapes="circles"
                  row-length="8"
                  show-border
                  popover-x="left"
                  popover-y="top"
              ></v-swatches>
              <a type="button" :title="translate('COM_EMUNDUS_ONBOARD_DELETE_STATUS')" :style="statu.edit == 1 && statu.step != 0 && statu.step != 1 ? 'opacity: 1' : 'opacity: 0'" @click="removeStatus(statu,index)" class="em-flex-row em-ml-8 em-pointer">
                <span class="material-icons em-red-500-color">delete_outline</span>
              </a>
            </div>
          </div>
          <hr/>
        </div>
      </draggable>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
/* COMPONENTS */
import draggable from "vuedraggable";
import axios from "axios";

import VSwatches from 'vue-swatches'
import 'vue-swatches/dist/vue-swatches.css'

/* SERVICES */
import client from "com_emundus/src/services/axiosClient";
import mixin from "com_emundus/src/mixins/mixin";

const qs = require("qs");

export default {
  name: "editStatus",

  components: {
    VSwatches,
    draggable
  },

  props: {},

  mixins: [mixin],

  data() {
    return {
      index: "",
      indexGrab: "0",

      grab: 0,
      loading: false,

      status: [],
      show: false,
      actualLanguage : '',
      swatches: [
        '#DCC6E0', '#947CB0', '#663399', '#6BB9F0', '#19B5FE', '#013243', '#7BEFB2', '#3FC380', '#1E824C', '#FFFD7E',
        '#FFFD54', '#F7CA18', '#FABE58', '#E87E04', '#D35400', '#EC644B', '#CF000F', '#E5283B', '#E08283', '#D2527F',
        '#DB0A5B', '#999999'
      ],
    };
  },

  created() {
    this.getStatus();
    this.actualLanguage = this.$store.getters['global/actualLanguage'];
  },

  methods: {
    getStatus() {
      axios.get("index.php?option=com_emundus&controller=settings&task=getstatus")
          .then(response => {
            this.status = response.data.data;
            setTimeout(() => {
              this.status.forEach(element => {
                this.getHexColors(element);
              });
            }, 100);
          });
    },

    async updateStatus(status) {
      this.$emit('updateSaving',true);

      const formData = new FormData();
      formData.append('status', status.step);
      formData.append('label', document.getElementById(('status_label_' + status.step)).textContent);
      formData.append('color', status.class);

      await client().post('index.php?option=com_emundus&controller=settings&task=updatestatus',
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
      ).then(() => {
        this.$emit('updateSaving',false);
        this.$emit('updateLastSaving',this.formattedDate('','LT'));
      });
    },

    async updateStatusOrder() {
      let status_steps = [];
      this.status.forEach((statu) => {
        status_steps.push(statu.step);
      })

      this.$emit('updateSaving',true);

      const formData = new FormData();
      formData.append('status', status_steps.join(','));

      await client().post('index.php?option=com_emundus&controller=settings&task=updatestatusorder',
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
      ).then(() => {
        this.$emit('updateSaving',false);
        this.$emit('updateLastSaving',this.formattedDate('','LT'));
      });
      console.log(status_steps);
    },

    pushStatus() {
      this.$emit('updateSaving',true);

      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=settings&task=createstatus',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then((newstatus) => {
        this.status.push(newstatus.data);
        setTimeout(() => {
          this.getHexColors(newstatus.data);
        }, 100);

        this.$emit('updateSaving',false);
        this.$emit('updateLastSaving',this.formattedDate('','LT'));
      });
    },

    removeStatus(status, index) {
      if(statu.edit == 1 && statu.step != 0 && statu.step != 1) {
        this.$emit('updateSaving',true);

        axios({
          method: "post",
          url: 'index.php?option=com_emundus&controller=settings&task=deletestatus',
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            id: status.id,
            step: status.step
          })
        }).then(() => {
          this.status.splice(index, 1);

          this.$emit('updateSaving',false);
          this.$emit('updateLastSaving',this.formattedDate('','LT'));
        });
      }
    },

    getHexColors(element) {
      element.translate = false;
      let status_class = document.querySelector('.label-' + element.class);
      let style = getComputedStyle(status_class);
      let rgbs = style.backgroundColor.split('(')[1].split(')')[0].split(',');
      element.class = this.rgbToHex(parseInt(rgbs[0]),parseInt(rgbs[1]),parseInt(rgbs[2]));
    },

    rgbToHex(r, g, b) {
      return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
    },

    enableGrab(index){
      if(this.status.length !== 1){
        this.indexGrab = index;
        this.grab = true;
      }
    },
    disableGrab(){
      this.indexGrab = 0;
      this.grab = false;
    },
  },
};
</script>
<style scoped>
.status-field{
  border-radius: 5px;
  width: 100%;
  margin-right: 1em;
  display: flex;
}

.status-item{
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 95%;
  width: 100%;
}
</style>
