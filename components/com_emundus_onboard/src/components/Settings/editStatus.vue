<template>
    <div class="em-settings-menu em-flex-center">
      <div class="em-flex-col-start em-w-80">
        <button @click="pushStatus" class="em-primary-button em-mb-24" style="width: max-content">
          <div class="add-button-div">
            <em class="fas fa-plus mr-1"></em>
            {{ translate('COM_EMUNDUS_ONBOARD_ADD_STATUS') }}
          </div>
        </button>
        <div v-for="(statu, index) in status" class="status-item em-mb-24" :id="'step_' + statu.step">
            <div class="status-field">
                <div style="width: 100%">
                    <input type="text" v-model="statu.label[actualLanguage]">
                </div>
                <input type="hidden" :class="'label-' + statu.class">
            </div>
            <v-swatches
                    v-model="statu.class"
                    :swatches="swatches"
                    shapes="circles"
                    row-length="8"
                    show-border
                    popover-x="left"
                    popover-y="top"
            ></v-swatches>
          <button type="button" :title="translate('COM_EMUNDUS_ONBOARD_DELETE_STATUS')" v-if="statu.edit == 1 && statu.step != 0 && statu.step != 1" @click="removeStatus(statu,index)" class="remove-tag"><i class="fas fa-times"></i></button>
        </div>
      </div>
    </div>
</template>

<script>
    import axios from "axios";
    import {global} from "../../store/global";
    import VSwatches from 'vue-swatches'
    import 'vue-swatches/dist/vue-swatches.css'
    import Translation from "@/components/translation";

    const qs = require("qs");

    export default {
        name: "editStatus",

        components: {
          VSwatches,
          Translation
        },

        props: {},

        data() {
            return {
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

        methods: {
            getStatus() {
                axios.get("index.php?option=com_emundus_onboard&controller=settings&task=getstatus")
                    .then(response => {
                        this.status = response.data.data;
                        setTimeout(() => {
                          this.status.forEach(element => {
                            this.getHexColors(element);
                          });
                        }, 100);
                    });
            },

            pushStatus() {
              this.$emit("LaunchLoading");
              axios({
                method: "post",
                url: 'index.php?option=com_emundus_onboard&controller=settings&task=createstatus',
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded"
                },
              }).then((newstatus) => {
                this.status.push(newstatus.data);
                setTimeout(() => {
                  this.getHexColors(newstatus.data);
                }, 100);
                this.$emit("StopLoading");
              });
            },

            removeStatus(status, index) {
              this.$emit("LaunchLoading");
              axios({
                method: "post",
                url: 'index.php?option=com_emundus_onboard&controller=settings&task=deletestatus',
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded"
                },
                data: qs.stringify({
                  id: status.id,
                  step: status.step
                })
              }).then(() => {
                this.status.splice(index,1);
                this.$emit("StopLoading");
              });
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
            }
        },

        created() {
            this.getStatus();
            this.actualLanguage = global.getters.actualLanguage;
        }
    };
</script>
<style scoped>
</style>
