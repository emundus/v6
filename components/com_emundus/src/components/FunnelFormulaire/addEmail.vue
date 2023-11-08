<template>
  <div>
    <ModalAddTrigger
        :prog="this.prog"
        :trigger="this.triggerSelected"
        :triggerAction="'candidate'"
        @UpdateTriggers="getTriggers"
        :key="'candidate' + candidate_trigger"
    />
    <ModalAddTrigger
        :prog="this.prog"
        :trigger="this.triggerSelected"
        :triggerAction="'manual'"
        @UpdateTriggers="getTriggers"
        :key="'manual-' + manual_trigger"
    />
    <div class="em-flex-row">
      <h4>{{ CandidateAction }}</h4>
    </div>
    <p>{{ TheCandidateDescription }}</p>

    <button class="em-primary-button em-w-auto em-mt-8"
            @click="$modal.show('modalAddTriggercandidate'); triggerSelected = null">
      {{ addTrigger }}
    </button>

    <transition-group :name="'slide-down'" type="transition" class="em-grid-2 em-m-16" style="margin-left: 0">
      <div
          v-for="trigger in candidateTriggers"
          :key="trigger.trigger_id"
          class="em-email-card"
      >
        <div class="em-flex-row em-align-start em-flex-space-between em-w-100">
          <div>
            <span class="em-mb-8">{{ trigger.subject }}</span>
            <div class="em-mt-8 em-mb-8">
              <span style="font-weight: bold">{{ Target }} : </span>
              <span v-for="(user, index) in triggerUsersWithProfile(trigger)" :key="'user_' + index">
                {{ user.firstname }} {{ user.lastname }}
                <span v-if="index != Object.keys(trigger.users).length - 1">, </span>
              </span>
              <span
                  v-if="trigger.users.length == 0 && trigger.profile != 5 && trigger.profile != 6">{{ TheCandidate }}</span>
              <span v-if="trigger.profile == 5">{{ Administrators }}</span>
              <span v-if="trigger.profile == 6">{{ Evaluators }}</span>
            </div>
            <span>{{ Status }} {{ trigger.status }}</span>
          </div>

          <div class="em-flex-row em-flex-end">
            <a class="em-mr-8 em-pointer" @click="editTrigger(trigger)">
              <span class="material-icons-outlined">edit</span>
            </a>
            <a class="em-pointer" @click="removeTrigger(trigger.trigger_id)" :title="removeTrig">
              <span class="material-icons-outlined em-red-500-color">close</span>
            </a>
          </div>
        </div>
      </div>
    </transition-group>

    <div class="em-flex-row">
      <h4 class="em-mt-16">{{ ManagerAction }}</h4>
    </div>
    <p>{{ ManualDescription }}</p>

    <button class="em-primary-button em-w-auto em-mt-8"
            @click="$modal.show('modalAddTriggermanual'); triggerSelected = null">
      {{ addTrigger }}
    </button>

    <transition-group :name="'slide-down'" type="transition" class="em-grid-2 em-m-16" style="margin-left: 0">
      <div v-for="trigger in manualTriggers" :key="trigger.trigger_id" class="em-email-card">

        <div class="em-flex-row em-align-start em-flex-space-between em-w-100">
          <div>
            <span class="em-mb-8">{{ trigger.subject }}</span>
            <div class="em-mt-8 em-mb-8">
              <span style="font-weight: bold">{{ Target }} : </span>
              <span
                  v-for="(user, index) in triggerUsersNoProfile(trigger)"
                  :key="'user_manual_' + index"
              >
              {{ user.firstname }} {{ user.lastname }}
              <span v-if="index != Object.keys(trigger.users).length - 1">, </span>
            </span>
              <span
                  v-if="trigger.users.length == 0 && trigger.profile != 5 && trigger.profile != 6">{{ TheCandidate }}</span>
              <span v-if="trigger.profile == 5">{{ Administrators }}</span>
              <span v-if="trigger.profile == 6">{{ Evaluators }}</span>
            </div>
            <p>{{ Status }} {{ trigger.status }}</p>
          </div>

          <div class="em-flex-row em-flex-end">
            <a class="em-pointer em-mr-8" @click="editTrigger(trigger)">
              <span class="material-icons-outlined">edit</span>
            </a>
            <a class="em-pointer" @click="removeTrigger(trigger.trigger_id)">
              <span class="material-icons-outlined">close</span>
            </a>
          </div>
        </div>
      </div>
    </transition-group>

    <div class="em-page-loader" v-if="loading"></div>
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
      loading: false,

      addTrigger: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_ADDTRIGGER"),
      removeTrig: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_REMOVETRIGGER"),
      affectTriggers: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_AFFECTTRIGGERS"),
      ChooseEmailTrigger: this.translate("COM_EMUNDUS_ONBOARD_CHOOSE_EMAIL_TRIGGER"),
      Target: this.translate("COM_EMUNDUS_ONBOARD_TRIGGERTARGET"),
      Status: this.translate("COM_EMUNDUS_ONBOARD_TRIGGERSTATUS"),
      Administrators: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATORS"),
      Evaluators: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATORS"),
      TheCandidate: this.translate("COM_EMUNDUS_ONBOARD_THE_CANDIDATE"),
      Manual: this.translate("COM_EMUNDUS_ONBOARD_MANUAL"),
      TheCandidateDescription: this.translate("COM_EMUNDUS_ONBOARD_THE_CANDIDATE_DESCRIPTION"),
      ManualDescription: this.translate("COM_EMUNDUS_ONBOARD_MANUAL_DESCRIPTION"),
      CandidateAction: this.translate("COM_EMUNDUS_ONBOARD_CANDIDATE_ACTION"),
      ManagerAction: this.translate("COM_EMUNDUS_ONBOARD_MANAGER_ACTION"),
    };
  },
  methods: {
    editTrigger(trigger) {
      this.triggerSelected = trigger.trigger_id;
      this.manual_trigger += 1;
      this.candidate_trigger += 1;
      setTimeout(() => {
        if (trigger.candidate == 1) {
          this.$modal.show('modalAddTriggercandidate');
        } else {
          this.$modal.show('modalAddTriggermanual');
        }
      }, 500);
    },
    removeTrigger(trigger) {
      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=email&task=removetrigger',
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
      axios.get("index.php?option=com_emundus&controller=email&task=gettriggersbyprogram&pid=" + this.prog)
          .then(response => {
            this.triggers = response.data.data;
            this.loading = false;
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
    this.loading = true;
    this.getTriggers();
  }
};
</script>
<style scoped>
.em-email-card {
  background: white;
  border-radius: 5px;
  padding: 16px 24px;
}

a.em-pointer:nth-child(2) .material-icons {
  color: #DB333E;
}

a.em-pointer:nth-child(2):hover .material-icons {
  color: #C31924;
}
</style>
