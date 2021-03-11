<template>
  <div>
    <div :class="finish ? 'finished' : ''" style="padding: 10px">
      <label>{{ question.question }}</label>
      <div>
        <div v-for="(proposal,index) in proposals" :class="'proposals'">
          <input type="checkbox" style="margin-right: 10px" :id="'proposal'+index" :name="question.code" v-model="answer" :value="proposal" :disabled="finish">
          <label :for="'proposal'+index">{{ proposals_text[index] }}</label><br/>
        </div>
      </div>
      <k-progress
          v-if="!finish"
          status="success"
          type="line"
          :showText="false"
          :percent="percent" >
      </k-progress>
    </div>
    <div class="awnswer-sended" v-if="finish">
      <label>{{ translations.answerSended }}</label>
      <div class="em-print-button">
        <a class="btn btn-info btn-xs" @click="nextQuestion">{{ translations.next }}</a>
      </div>
    </div>
  </div>
</template>

<script>
import KProgress from 'k-progress';

export default {
  name: "Question",
  props: {
    question: Object,
    updateProposal: Number,
    pending: Number,
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
        answerSended: Joomla.JText._("MOD_EM_QCM_ANSWER_SENDED")
      }
    };
  },
  methods: {
    check_timer_completed(){
      if(this.timer <= 0) {
        clearInterval(this.interval);
        if(!this.finish) {
          this.$emit('saveAnswer', this.answer);
        }
        this.finish = true;
      }
    },

    initTimerProposals(){
      this.finish = false;
      this.proposals = this.question.proposals_id.split(',');
      this.proposals_text = this.question.proposals_text.split(',');
      this.answer = [];
      let total_time = this.question.time;
      if(this.tierstemps == 1){
        total_time = parseInt(this.question.time)+(parseInt(this.question.time)*(1/3));
      }
      this.timer = total_time;
      this.interval = setInterval(() => {
        this.timer--;
        this.percent = (this.timer / total_time)*100;
        this.check_timer_completed();
      },1000);
    },

    nextQuestion(){
      if(!this.finish){
        this.$emit('saveAnswer');
      }
      this.$emit('nextQuestion');
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
