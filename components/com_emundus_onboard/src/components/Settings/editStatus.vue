<template>
    <div class="container-evaluation">
      <a @click="pushStatus" class="bouton-ajouter-green bouton-ajouter pointer mb-1" style="width: max-content">
        <div class="add-button-div">
          <em class="fas fa-plus mr-1"></em>
          {{ addStatus }}
        </div>
      </a>
        <div v-for="(statu, index) in status" class="status-item" :id="'step_' + statu.step">
            <div class="status-field">
                <div style="width: 100%">
                    <input type="text" v-model="statu.label[actualLanguage]" @keyup="verifyIfStatusNotExist">

                    <translation :label="statu.label" :actualLanguage="actualLanguage" v-if="statu.translate" ></translation>
                    <!--<transition :name="'slide-down'" type="transition">
                        <div class="translate-block" v-if="statu.translate">
                            <label class="translate-label">
                                {{TranslateEnglish}}
                            </label>
                            <em class="fas fa-sort-down"></em>
                        </div>
                    </transition>
                    <transition :name="'slide-down'" type="transition">
                        <input type="text" v-model="statu.value.en" v-if="statu.translate">
                    </transition>-->
                </div>
                <button class="translate-icon" v-if="manyLanguages !== '0'" v-bind:class="{'translate-icon-selected': statu.translate}" type="button" @click="statu.translate = !statu.translate; $forceUpdate()"></button>
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
          <button type="button" :title="Delete" v-if="statu.edit == 1 && statu.step != 0 && statu.step != 1" @click="removeStatus(statu,index)" class="remove-tag"><i class="fas fa-times"></i></button>
        </div>
      <p v-if="existInActualLanguage" class="text-error">{{ CannotDuplicateStatus }}</p>
    </div>
</template>

<script>
    import axios from "axios";
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

        props: {
          actualLanguage: String,
          manyLanguages: Number,
        },

        data() {
            return {
                status: [],
                show: false,
                existInActualLanguage: false,
                swatches: [
                    '#DCC6E0', '#947CB0', '#663399', '#6BB9F0', '#19B5FE', '#013243', '#7BEFB2', '#3FC380', '#1E824C', '#FFFD7E',
                     '#F7CA18', '#FABE58', '#E87E04', '#D35400', '#EC644B', '#CF000F', '#E5283B', '#E08283', '#D2527F',
                    '#DB0A5B', '#999999'
                ],
                TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
                addStatus: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_STATUS"),
                Delete: Joomla.JText._("COM_EMUNDUS_ONBOARD_DELETE_STATUS"),
                CannotDuplicateStatus: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANNOT_DUPLICATE_STATUS"),
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
            },
          verifyIfStatusNotExist(event){
            if((this.status.filter(statu=>statu.label[this.actualLanguage] == event.target.value)).length >1){
                  this.existInActualLanguage=true;

            } else{
              this.existInActualLanguage=false;
            }

          }
        },

        created() {
            this.getStatus();

        }
    };
</script>
<style scoped>
    .translate-block{
        display: flex;
        margin: 10px;
        color: white
    }
    .translate-icon-selected{
      margin-top: 10px;
      height: max-content;
    }
    .bouton-sauvergarder-et-continuer{
      justify-content: center;
        right: 10%;
        margin-bottom: 14px;
    }
    .create-tag{
      width: max-content;
      margin-bottom: 20px;
    }
    .loading-form-save{
      right: 21%;
    }

</style>
