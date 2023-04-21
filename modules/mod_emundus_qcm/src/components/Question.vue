<template>
  <div>
    <div :class="finish ? 'finished' : ''" style="padding: 10px">
      <label v-html="question.question" class="em-mb-24"></label>
      <div>
        <div v-for="(proposal,index) in proposals" :class="'proposals'">
          <input type="checkbox" style="margin-right: 10px" :id="'proposal'+index" :name="question.code" v-model="answer" :value="proposal" :disabled="finish">
          <label class="em-flex-row" :for="'proposal'+index" v-html="proposals_text[index]"></label><br/>
        </div>
      </div>
      <k-progress
          v-if="!finish"
          status="success"
          type="line"
          :showText="false"
          :percent="percent" >
      </k-progress>
      <p v-if="!finish && timer" style="text-align: center">{{ timer }}</p>
    </div>
    <div class="awnswer-sended" v-if="finish">
      <label>{{ translations.answerSended }}</label>
      <div class="em-print-button">
        <a class="btn btn-info btn-xs" @click="nextQuestion">{{ translations.next }}</a>
      </div>
    </div>
    <div class="awnswer-sended" v-if="!finish">
      <div class="em-print-button">
        <a class="btn btn-info btn-xs" @click="nextQuestion">{{ translations.confirmQuestion }}</a>
      </div>
    </div>
  </div>
</template>

<script>
import KProgress from 'k-progress';
import axios from "axios";

const qs = require("qs");

export default {
  name: "Question",
  props: {
    question: Object,
    updateProposal: Number,
    pending: Number,
    formid: Number,
    tierstemps: Number,
  },
  components: {
    KProgress
  },
  data() {
    return {
      proposals: [],
      proposals_text: [],
      answer: [],
      timer: null,
      percent: 100,
      interval: '',
      finish: false,
      translations:{
        next: Joomla.JText._("MOD_EM_QCM_NEXT_QUESTION"),
        answerSended: Joomla.JText._("MOD_EM_QCM_ANSWER_SENDED"),
        confirmQuestion: Joomla.JText._("MOD_EM_QCM_CONFIRM_ANSWER")
      }
    };
  },
  methods: {
    check_timer_completed(){
      if(this.timer <= 0) {
        clearInterval(this.interval);
        this.timer = 0;
        if(!this.finish) {
          this.$emit('saveAnswer', this.answer);
        }
        this.finish = true;
        this.$emit('resetPending');
        this.pending = 0;
      }
      this.updatePending();
    },

    initTimerProposals(){
      this.finish = false;
      this.proposals = this.question.proposals_id.split(',');
      this.proposals_text = this.question.proposals_text.split('|');
      this.answer = [];
      let total_time = this.question.time;
      if(parseInt(this.pending) != 0) {
        total_time = this.pending;
      }
      if(this.tierstemps == 1){
        if(this.pending == 0) {
          total_time = parseInt(this.question.time) + (parseInt(this.question.time) * (1 / 3));
        }
      }
      this.timer = total_time;
      this.updatePercent();
      this.interval = setInterval(() => {
        this.timer--;
        this.updatePercent();
        this.check_timer_completed();
      },1000);
    },

    updatePercent(){
      if(this.tierstemps == 1) {
        let time_tiers = parseInt(this.question.time) + (parseInt(this.question.time) * (1 / 3));
        this.percent = (this.timer / time_tiers) * 100;
      } else {
        this.percent = (this.timer / this.question.time) * 100;
      }
    },

    nextQuestion(){
      if(!this.finish){
        clearInterval(this.interval);
        this.timer = 0;
        this.$emit('saveAnswer', this.answer);
        this.finish = true;
        this.$emit('resetPending');
        this.pending = 0;
      }
      this.$emit('nextQuestion');
    },

    updatePending(){
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=qcm&task=updatepending",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          pending: this.timer,
          formid: this.formid,
        })
      });
    }
  },
  created() {
    this.initTimerProposals();
  },

  watch: {
    updateProposal: function(){
      this.initTimerProposals();
    }
  }
}
</script>

<style scoped>
.finished{
  filter: grayscale(1);
  background: repeating-linear-gradient( -45deg, #b8bedf, #a3aad5 5px, #b4b9db 5px, #babed5 10px );
  border-radius: 5px;
}
.awnswer-sended{
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 15px;
}
.awnswer-sended label{
    color: #21ba45;
    cursor: unset;
}
.em-print-button{
  width: max-content;
}
.proposals{
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}
.proposals input{
  margin-top: 0;
}
.proposals label{
  margin-bottom: 0;
}
</style>
