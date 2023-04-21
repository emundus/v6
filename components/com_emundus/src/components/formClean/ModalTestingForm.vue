<template>
  <!-- modalC -->
  <span :id="'modalTestingForm'">
    <modal
      :name="'modalTestingForm'"
      height="auto"
      transition="little-move-left"
      :min-width="200"
      :min-height="200"
      :delay="100"
      :adaptive="true"
      :clickToClose="true"
      @closed="beforeClose"
      @before-open="beforeOpen"
    >
      <div class="fixed-header-modal">
        <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalTestingForm')">
              <em class="fas fa-times"></em>
            </button>
          </div>
        <div class="update-field-header">
          <h2 class="update-title-header">
             {{testingForm}}
          </h2>
        </div>
      </div>
      <div class="modalC-content">
        <div class="form-group" v-if="campaigns.length > 1">
          <label>{{ChooseCampaign}} :</label>
          <select v-model="cid" class="dropdown-toggle">
            <option v-for="(campaign, index) in campaigns" :value="campaign.id">{{campaign.label}}</option>
          </select>
        </div>

        <div class="form-group" v-if="filesExist">
          <p>{{ FileExistsBeforeTesting }}</p>
        </div>
      </div>
      <div class="em-flex-row em-flex-space-between mb-1">
        <button type="button" v-if="filesExist"
                @click="goExistingFile"
                class="bouton-sauvergarder-et-continuer"
        >{{ ContinueFile }}</button>
        <button type="button" @click="createNewFile"
          class="bouton-sauvergarder-et-continuer ml-10px"
        >{{ CreateFile }}</button>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalTestingForm",
  components: {},
  props: {
    profileId: String,
    actualLanguage: String,
    campaigns: Object,
    currentForm: Number,
    currentMenu: Number,
  },
  data() {
    return {
      cid: -1,
      filesExist: false,
      ContinueFile: this.translate("COM_EMUNDUS_ONBOARD_CONTINUE_FILE"),
      CreateFile: this.translate("COM_EMUNDUS_ONBOARD_CREATE_FILE"),
      ChooseCampaign: this.translate("COM_EMUNDUS_ONBOARD_CHOOSE_CAMPAIGN"),
      FileExistsBeforeTesting: this.translate("COM_EMUNDUS_ONBOARD_FILE_EXIST_BEFORE_TESTING"),
      testingForm: this.translate("COM_EMUNDUS_ONBOARD_TESTING_FORM"),
    };
  },
  methods: {
    beforeClose(event) {
      this.$emit("modalClosed");
    },
    beforeOpen(event) {
      setTimeout(() => {
        this.cid = this.campaigns[0].id
      },100);
    },
    goExistingFile(){
      window.open('/index.php?option=com_fabrik&view=form&formid=' + this.currentForm + '&Itemid=' + this.currentMenu +'&usekey=fnum&rowid=' + this.filesExist + '&r=1#em-panel');
      this.$modal.hide('modalTestingForm');
    },
    createNewFile(){
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=formbuilder&task=deletetestingfile",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          file: this.filesExist,
        })
      }).then((result) => {
          if(result.data.status == true){
            axios({
              method: "post",
              url: "index.php?option=com_emundus&controller=formbuilder&task=createtestingfile",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({
                cid: this.cid,
              })
            }).then((rep) => {
              window.open('/index.php?option=com_emundus&task=openfile&fnum=' + rep.data.fnum + '&redirect=1==&Itemid=1079#em-panel');
              this.$modal.hide('modalTestingForm');
            });
          }
      });
    }
  },

  watch: {
    cid: function(value){
      this.campaigns.forEach((campaign) => {
        if(campaign.id == value){
          if(campaign.files.length > 0){
            this.filesExist = campaign.files[0].fnum;
          } else {
            this.filesExist = false;
          }
        }
      });
    }
  }
};
</script>

<style scoped>
</style>
