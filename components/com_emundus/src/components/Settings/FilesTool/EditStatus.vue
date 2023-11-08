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
        <div v-for="(statu, index) in status" class="em-mb-24" :title="'step_' + statu.step" :key="statu.step"
             :id="'step_' + statu.step" @mouseover="enableGrab(index)" @mouseleave="disableGrab()">
          <div class="em-flex-row em-flex-row-start em-w-100">
            <span class="handle em-grab" :style="grab && indexGrab == index ? 'opacity: 1' : 'opacity: 0'">
              <span class="material-icons-outlined">drag_indicator</span>
            </span>
            <div class="status-field">
              <div>
                <p class="em-p-8-12 em-editable-content" contenteditable="true" :id="'status_label_' + statu.step"
                   @focusout="updateStatus(statu)" @keyup.enter="manageKeyup(statu)" @keydown="checkMaxlength">
                  {{ statu.label[actualLanguage] }}</p>
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
              <a type="button" v-if="statu.edit == 1 && statu.step != 0 && statu.step != 1"
                 :title="translate('COM_EMUNDUS_ONBOARD_DELETE_STATUS')" @click="removeStatus(statu,index)"
                 class="em-flex-row em-ml-8 em-pointer">
                <span class="material-icons-outlined em-red-500-color">delete_outline</span>
              </a>
              <a type="button" v-else :title="translate('COM_EMUNDUS_ONBOARD_CANNOT_DELETE_STATUS')"
                 class="em-flex-row em-ml-8 em-pointer">
                <span class="material-icons-outlined em-text-neutral-600">delete_outline</span>
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
      actualLanguage: '',
      swatches: [],
      colors: [],
      variables: null,
    };
  },

  created() {
    let root = document.querySelector(':root');
    this.variables = getComputedStyle(root);

    this.prepareSwatchesColor();
    this.getStatus();
    this.actualLanguage = this.$store.getters['global/shortLang'];
  },

  methods: {
    prepareSwatchesColor() {
      this.swatches.push(this.variables.getPropertyValue('--em-red-1'));
      this.colors.push({name: 'red-1', value: this.variables.getPropertyValue('--em-red-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-red-2'));
      this.colors.push({name: 'red-2', value: this.variables.getPropertyValue('--em-red-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-pink-1'));
      this.colors.push({name: 'pink-1', value: this.variables.getPropertyValue('--em-pink-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-pink-2'));
      this.colors.push({name: 'pink-2', value: this.variables.getPropertyValue('--em-pink-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-purple-1'));
      this.colors.push({name: 'purple-1', value: this.variables.getPropertyValue('--em-purple-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-purple-2'));
      this.colors.push({name: 'purple-2', value: this.variables.getPropertyValue('--em-purple-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-light-blue-1'));
      this.colors.push({name: 'light-blue-1', value: this.variables.getPropertyValue('--em-light-blue-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-light-blue-2'));
      this.colors.push({name: 'light-blue-2', value: this.variables.getPropertyValue('--em-light-blue-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-blue-1'));
      this.colors.push({name: 'blue-1', value: this.variables.getPropertyValue('--em-blue-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-blue-2'));
      this.colors.push({name: 'blue-2', value: this.variables.getPropertyValue('--em-blue-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-blue-3'));
      this.colors.push({name: 'blue-3', value: this.variables.getPropertyValue('--em-blue-3')})
      this.swatches.push(this.variables.getPropertyValue('--em-green-1'));
      this.colors.push({name: 'green-1', value: this.variables.getPropertyValue('--em-green-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-green-2'));
      this.colors.push({name: 'green-2', value: this.variables.getPropertyValue('--em-green-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-yellow-1'));
      this.colors.push({name: 'yellow-1', value: this.variables.getPropertyValue('--em-yellow-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-yellow-2'));
      this.colors.push({name: 'yellow-2', value: this.variables.getPropertyValue('--em-yellow-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-orange-1'));
      this.colors.push({name: 'orange-1', value: this.variables.getPropertyValue('--em-orange-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-orange-2'));
      this.colors.push({name: 'orange-2', value: this.variables.getPropertyValue('--em-orange-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-beige'));
      this.colors.push({name: 'beige', value: this.variables.getPropertyValue('--em-beige')})
      this.swatches.push(this.variables.getPropertyValue('--em-brown'));
      this.colors.push({name: 'brown', value: this.variables.getPropertyValue('--em-brown')})
      this.swatches.push(this.variables.getPropertyValue('--em-grey-1'));
      this.colors.push({name: 'grey-1', value: this.variables.getPropertyValue('--em-grey-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-grey-2'));
      this.colors.push({name: 'grey-2', value: this.variables.getPropertyValue('--em-grey-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-black'));
      this.colors.push({name: 'black', value: this.variables.getPropertyValue('--em-black')})
    },

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
      this.$emit('updateSaving', true);

      let index = this.colors.findIndex(item => item.value === status.class);
      const formData = new FormData();
      formData.append('status', status.step);
      formData.append('label', document.getElementById(('status_label_' + status.step)).textContent);
      formData.append('color', this.colors[index].name);

      await client().post('index.php?option=com_emundus&controller=settings&task=updatestatus',
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
      ).then(() => {
        this.$emit('updateSaving', false);
        this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
      });
    },

    async updateStatusOrder() {
      let status_steps = [];
      this.status.forEach((statu) => {
        status_steps.push(statu.step);
      })

      this.$emit('updateSaving', true);

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
        this.$emit('updateSaving', false);
        this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
      });
    },

    pushStatus() {
      this.$emit('updateSaving', true);

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

        this.$emit('updateSaving', false);
        this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
      });
    },

    removeStatus(status, index) {
      if (status.edit == 1 && status.step != 0 && status.step != 1) {
        this.$emit('updateSaving', true);

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

          this.$emit('updateSaving', false);
          this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
        });
      }
    },

    manageKeyup(status) {
      document.getElementById(('status_label_' + status.step)).textContent = document.getElementById(('status_label_' + status.step)).textContent.trim();
      document.activeElement.blur();
    },

    getHexColors(element) {
      element.translate = false;
      element.class = this.variables.getPropertyValue('--em-' + element.class);
    },

    checkMaxlength(event) {
      if (event.target.textContent.length === 50 && event.keyCode != 8) {
        event.preventDefault();
      }
    },

    enableGrab(index) {
      if (this.status.length !== 1) {
        this.indexGrab = index;
        this.grab = true;
      }
    },
    disableGrab() {
      this.indexGrab = 0;
      this.grab = false;
    },
  },
};
</script>
<style scoped lang="scss">
.status-field {
  border-radius: 5px;
  width: 100%;
  margin-right: 1em;
  display: flex;
}

.status-item {
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 95%;
  width: 100%;
}
</style>
