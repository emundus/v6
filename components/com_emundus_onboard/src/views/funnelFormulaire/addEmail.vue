<template>
  <div class="container-evaluation">
    <ModalAddTrigger
            :prog="this.prog"
            :trigger="this.triggerSelected"
            :triggerAction="'candidate'"
            @UpdateTriggers="getTriggers"
    />
    <ModalAddTrigger
            :prog="this.prog"
            :trigger="this.triggerSelected"
            :triggerAction="'manual'"
            @UpdateTriggers="getTriggers"
    />
    <div class="choices-buttons">
      <h2 style="margin-bottom: 0">{{ TheCandidate }}</h2>
      <a @click="$modal.show('modalAddTriggercandidate'); triggerSelected = null" class="bouton-sauvergarder-et-continuer-3">{{ addTrigger }}</a>
    </div>
    <p>{{ TheCandidateDescription }}</p>
    <transition-group :name="'slide-down'" type="transition">
      <div v-for="(trigger, index) in triggers" :key="index" class="trigger-item" v-if="trigger.candidate == 1">
        <div style="max-width: 80%">
          <p>{{trigger.subject}}</p>
          <p>
            <span style="font-weight: bold">{{Target}} : </span>
            <span v-if="trigger.profile == null" v-for="(user, index) in trigger.users">
              {{user.firstname}} {{user.lastname}}
              <span v-if="index != Object.keys(trigger.users).length - 1">, </span>
            </span>
            <span v-if="trigger.profile == 5">{{Administrators}}</span>
            <span v-if="trigger.profile == 6">{{Evaluators}}</span>
          </p>
          <p>{{Status}} {{trigger.status}}</p>
        </div>
        <div>
          <button type="button" @click="removeTrigger(trigger.trigger_id)" class="remove-user"><em class="fas fa-times"></em></button>
          <button type="button" @click="editTrigger(trigger)"><em class="fas fa-edit"></em></button>
        </div>
      </div>
    </transition-group>
    <div class="choices-buttons">
      <h2 style="margin-bottom: 0">{{ Manual }}</h2>
      <a @click="$modal.show('modalAddTriggermanual'); triggerSelected = null" class="bouton-sauvergarder-et-continuer-3">{{ addTrigger }}</a>
    </div>
    <p>{{ ManualDescription }}</p>
    <transition-group :name="'slide-down'" type="transition">
      <div v-for="(trigger, index) in triggers" :key="index" class="trigger-item" v-if="trigger.manual == 1">
        <div style="max-width: 80%">
          <p>{{trigger.subject}}</p>
          <p>
            <span style="font-weight: bold">{{Target}} : </span>
            <span v-if="trigger.profile == null" v-for="(user, index) in trigger.users">
              {{user.firstname}} {{user.lastname}}
              <span v-if="index != Object.keys(trigger.users).length - 1">, </span>
            </span>
            <span v-if="trigger.profile == 5">{{Administrators}}</span>
            <span v-if="trigger.profile == 6">{{Evaluators}}</span>
          </p>
          <p>{{Status}} {{trigger.status}}</p>
        </div>
        <div>
          <button type="button" @click="removeTrigger(trigger.trigger_id)" class="remove-user"><em class="fas fa-times"></em></button>
          <button type="button" @click="editTrigger(trigger)"><em class="fas fa-edit"></em></button>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script>
import ModalAddTrigger from "../advancedModals/ModalAddTrigger";
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
      addTrigger: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_ADDTRIGGER"),
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
    };
  },

  methods: {
    editTrigger(trigger) {
      this.triggerSelected = trigger.trigger_id;
      if(trigger.candidate == 1){
        this.$modal.show('modalAddTriggercandidate');
      } else {
        this.$modal.show('modalAddTriggermanual');
      }
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
      }).then((rep) => {
        this.getTriggers();
      });
    },
    getTriggers() {
      axios.get("index.php?option=com_emundus_onboard&controller=email&task=gettriggersbyprogram&pid=" + this.prog)
              .then(response => {
                this.triggers = response.data.data;
              });
    },
  },

  created() {
    this.getTriggers();
  }
};
</script>
<style>
  .create-trigger{
    text-align: center;
    width: 100%;
    margin-bottom: 4em;
  }

  .choices-buttons .bouton-sauvergarder-et-continuer-3{
    float: unset;
  }

  .trigger-item{
    display: flex;
    justify-content: space-between;
    padding: 30px;
    background-color: #f0f0f0;
    border-radius: 5px;
    align-items: center;
    margin-bottom: 1em;
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

  .fa-times{
    color: red;
    cursor: pointer;
    width: 15px;
    height: 15px;
  }

  .fa-edit{
    cursor: pointer;
    width: 15px;
    height: 15px;
  }
</style>
