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
      <div class="d-flex justify-content-between mb-1">
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
    profileId: Number,
    actualLanguage: String,
    campaigns: Object,
    currentForm: Number,
    currentMenu: Number,
  },
  data() {
    return {
      cid: -1,
      filesExist: false,
      ContinueFile: Joomla.JText._("COM_EMUNDUS_ONBOARD_CONTINUE_FILE"),
      CreateFile: Joomla.JText._("COM_EMUNDUS_ONBOARD_CREATE_FILE"),
      ChooseCampaign: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSE_CAMPAIGN"),
      FileExistsBeforeTesting: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILE_EXIST_BEFORE_TESTING"),
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
        url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=deletetestingfile",
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
              url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=createtestingfile",
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
.modalC-content {
  height: 100%;
  box-sizing: border-box;
  padding: 10px;
  font-size: 15px;
  overflow: auto;
}

.b {
  display: block;
}

.toggle {
  vertical-align: middle;
  position: relative;

  left: 20px;
  width: 45px;
  border-radius: 100px;
  background-color: #ddd;
  overflow: hidden;
  box-shadow: inset 0 0 2px 1px rgba(0, 0, 0, 0.05);
}

.check {
  position: absolute;
  display: block;
  cursor: pointer;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  z-index: 6;
}

.check:checked ~ .track {
  box-shadow: inset 0 0 0 20px #4bd863;
}

.check:checked ~ .switch {
  right: 2px;
  left: 22px;
  transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
  transition-property: left, right;
  transition-delay: 0.05s, 0s;
}

.switch {
  position: absolute;
  left: 2px;
  top: 2px;
  bottom: 2px;
  right: 22px;
  background-color: #fff;
  border-radius: 36px;
  z-index: 1;
  transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
  transition-property: left, right;
  transition-delay: 0s, 0.05s;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.track {
  position: absolute;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
  box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.05);
  border-radius: 40px;
}
.inlineflex {
  display: flex;
  align-content: center;
  align-items: center;
  height: 30px;
}
.titleType {
  font-size: 45%;
  margin-left: 1em;
}
.topright {
  font-size: 25px;
  float: right;
}
.btnCloseModal {
  background-color: inherit;
}
  .update-field-header{
    margin-bottom: 1em;
  }

  .update-title-header{
    margin-top: 0;
    display: flex;
    align-items: center;
  }
</style>
