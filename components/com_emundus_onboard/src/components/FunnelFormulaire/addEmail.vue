<template>
  <div class="container-evaluation">
    <ModalAddTrigger
            :prog="this.prog"
            :trigger="this.triggerSelected"
            :triggerAction="'candidate'"
            @UpdateTriggers="getTriggers"
            :key="candidate_trigger"
    />
    <ModalAddTrigger
            :prog="this.prog"
            :trigger="this.triggerSelected"
            :triggerAction="'manual'"
            @UpdateTriggers="getTriggers"
            :key="manual_trigger"
    />
    <div class="choices-buttons">
      <h2 style="margin-bottom: 0">{{ CandidateAction }}</h2>
      <a @click="$modal.show('modalAddTriggercandidate'); triggerSelected = null" class="bouton-ajouter bouton-ajouter-green pointer" style="width: max-content">
        <div class="add-button-div">
          <em class="fas fa-plus mr-1"></em>
          {{ addTrigger }}
        </div>
      </a>
    </div>
    <p>{{ TheCandidateDescription }}</p>
    <transition-group :name="'slide-down'" type="transition">
      <div 
        v-for="trigger in candidateTriggers" 
        :key="trigger.trigger_id" 
        class="trigger-item"
      >
        <div style="max-width: 80%">
          <p>{{trigger.subject}}</p>
          <p>
            <span style="font-weight: bold">{{Target}} : </span>
            <span 
              v-for="(user, index) in triggerUsersWithProfile(trigger)" 
              :key="'user_' + index"
            >
              {{user.firstname}} {{user.lastname}}
              <span v-if="index != Object.keys(trigger.users).length - 1">, </span>
            </span>
            <span v-if="trigger.users.length == 0 && trigger.profile != 5 && trigger.profile != 6">{{TheCandidate}}</span>
            <span v-if="trigger.profile == 5">{{Administrators}}</span>
            <span v-if="trigger.profile == 6">{{Evaluators}}</span>
          </p>
          <p>{{Status}} {{trigger.status}}</p>
        </div>
        <div style="display: grid">
          <button type="button" @click="removeTrigger(trigger.trigger_id)" class="buttonDeleteDoc" :title="removeTrig"><em class="fas fa-times"></em></button>
          <a @click="editTrigger(trigger)" class="cta-block pointer">
            <em class="fas fa-pen"></em>
          </a>
        </div>
      </div>
    </transition-group>
    <div class="choices-buttons">
      <h2 style="margin-bottom: 0">{{ ManagerAction }}</h2>
      <a @click="$modal.show('modalAddTriggermanual'); triggerSelected = null" class="bouton-ajouter bouton-ajouter-green pointer" style="width: max-content">
        <div class="add-button-div">
          <em class="fas fa-plus mr-1"></em>
          {{ addTrigger }}
        </div>
      </a>
    </div>
    <p>{{ ManualDescription }}</p>
    <transition-group :name="'slide-down'" type="transition">
      <div v-for="trigger in manualTriggers" :key="trigger.trigger_id" class="trigger-item">
        <div style="max-width: 80%">
          <p>{{trigger.subject}}</p>
          <p>
            <span style="font-weight: bold">{{Target}} : </span>
            <span 
              v-for="(user, index) in triggerUsersNoProfile(trigger)" 
              :key="'user_manual_' + index"
            >
              {{user.firstname}} {{user.lastname}}
              <span v-if="index != Object.keys(trigger.users).length - 1">, </span>
            </span>
            <span v-if="trigger.users.length == 0 && trigger.profile != 5 && trigger.profile != 6">{{TheCandidate}}</span>
            <span v-if="trigger.profile == 5">{{Administrators}}</span>
            <span v-if="trigger.profile == 6">{{Evaluators}}</span>
          </p>
          <p>{{Status}} {{trigger.status}}</p>
        </div>
        <div style="display: grid">
          <button type="button" @click="removeTrigger(trigger.trigger_id)" class="buttonDeleteDoc"><em class="fas fa-times"></em></button>
          <a @click="editTrigger(trigger)" class="cta-block pointer">
            <em class="fas fa-pen"></em>
          </a>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script>
import ModalAddTrigger from "../AdvancedModals/ModalAddTrigger";
import axios from "axios";

const qs = require("qs");

export default {
  name: "addEmail",
  components: {ModalAddTrigger},
  props: {
    funnelCategorie: String,
    prog: Number
  },

  data() {
    return {
      triggers: [],
      triggerSelected: null,
      manual_trigger: 0,
      candidate_trigger: 0,
      addTrigger: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_ADDTRIGGER"),
      removeTrig: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_REMOVETRIGGER"),
      affectTriggers: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_AFFECTTRIGGERS"),
      ChooseEmailTrigger: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSE_EMAIL_TRIGGER"),
      Target: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERTARGET"),
      Status: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERSTATUS"),
      Administrators: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATORS"),
      Evaluators: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATORS"),
      TheCandidate: Joomla.JText._("COM_EMUNDUS_ONBOARD_THE_CANDIDATE"),
      Manual: Joomla.JText._("COM_EMUNDUS_ONBOARD_MANUAL"),
      TheCandidateDescription: Joomla.JText._("COM_EMUNDUS_ONBOARD_THE_CANDIDATE_DESCRIPTION"),
      ManualDescription: Joomla.JText._("COM_EMUNDUS_ONBOARD_MANUAL_DESCRIPTION"),
      CandidateAction: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANDIDATE_ACTION"),
      ManagerAction: Joomla.JText._("COM_EMUNDUS_ONBOARD_MANAGER_ACTION"),
    };
  },
  methods: {
    editTrigger(trigger) {
      this.triggerSelected = trigger.trigger_id;
      this.manual_trigger += 1;
      this.candidate_trigger += 1;
      setTimeout(() => {
        if(trigger.candidate == 1){
          this.$modal.show('modalAddTriggercandidate');
        } else {
          this.$modal.show('modalAddTriggermanual');
        }
      },500);
    },
    removeTrigger(trigger) {
      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=email&task=removetrigger',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          tid: trigger,
        })
      }).then(() => {
        this.getTriggers();
      });
    },
    getTriggers() {
      axios.get("index.php?option=com_emundus_onboard&controller=email&task=gettriggersbyprogram&pid=" + this.prog)
      .then(response => {
        this.triggers = response.data.data;
      });
    },
    triggerUsersWithProfile(trigger) {
      if (trigger.profile !== null) {
        return trigger.users
      }

      return [];
    },
    triggerUsersNoProfile(trigger) {
      if (trigger.profile === null && trigger.users.length > 0) {
        return trigger.users
      }

      return [];
    }
  },
  computed: {
    candidateTriggers() {
      return this.triggers.filter(trigger => trigger.candidate == 1);
    },
    manualTriggers() {
      return this.triggers.filter(trigger => trigger.manual == 1);
    }
  },
  created() {
    this.getTriggers();
  }
};
</script>
<style scoped>
  .create-trigger{
    text-align: center;
    width: 100%;
    margin-bottom: 4em;
  }

  .choices-buttons{
    display: flex;
    align-items: center;
    margin-bottom: 1em;
  }
  .choices-buttons h2{
    margin-right: 1em;
  }
  .choices-buttons .bouton-sauvergarder-et-continuer{
    float: unset;
  }

  .trigger-item{
    display: flex;
    justify-content: space-between;
    padding: 30px;
    background-color: #fff;
    border-radius: 5px;
    align-items: center;
    margin: 1em 0;
    border: solid 2px #ececec;
  }

  .remove-user{
    border-radius: 50%;
    height: 30px;
    width: 30px;
    transition: all 0.3s ease-in-out;
  }
  .remove-user:hover{
    background-color: red;
  }
  .remove-user:hover > .fa-times {
    color: white;
  }
</style>
