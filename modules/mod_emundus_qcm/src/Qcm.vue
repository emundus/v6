<template>
  <div id="qcm" v-if="!loading">
    <div v-if="!finishedQcm">
      <div v-if="!quizStarting && !quizTesting">
        <p v-html="intro"></p>
        <div class="em-print-button" v-if="step == 0">
          <a class="btn btn-info btn-xs" @click="startQcm">{{ translations.startingQcm }}</a>
        </div>
        <div class="em-print-button" v-else>
          <a class="btn btn-info btn-xs" @click="quizStarting = true;">{{ translations.restartQcm }}</a>
        </div>
      </div>
      <div v-if="testPassed && quizTesting">
        <p class="em-mb-8">{{ translations.ready }}</p>
        <div class="em-print-button">
          <a class="btn btn-info btn-xs" @click="startQcm">{{ translations.startingQcm }}</a>
        </div>
      </div>
      <div v-if="quizTesting && !testPassed">
        <question :question="testing_question" :updateProposal="updateProposal" :tierstemps="tierstemps" :pending="pending" :formid="formid" @nextQuestion="testPassed = true;"></question>
      </div>
      <div v-if="quizStarting">
        <p style="text-align: center;">{{parseInt(step)+1}} / {{count}}</p>
        <question :question="applicant_questions[step]" :updateProposal="updateProposal" :pending="pending" :formid="formid" :tierstemps="tierstemps" @nextQuestion="nextQuestion" @resetPending="pending = 0" @saveAnswer="saveAnswer"></question>
      </div>
    </div>
    <div v-else>
      <label>{{ translations.qcmCompleted }}</label>
      <p>{{ translations.qcmSuccesfull }}</p>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Question from "@/components/Question";

const qs = require("qs");

export default {
  name: 'Qcm',
  props: {
    questions: String,
    formid: Number,
    step: Number,
    intro: String,
    pending: Number,
    module: Number,
    tierstemps: Number,
  },
  components: {Question},
  data() {
    return {
      applicant_questions: [],
      count: 0,
      testing_question: {
        code: "TEST",
        proposals: "Noir|Blanc|Rouge|Vert",
        proposals_id: "1,2,3,4",
        proposals_text: "Noir|Blanc|Rouge|Vert",
        question: "De quelle couleur est le cheval blanc d'Henri IV ?",
        time: "10",
        type: "radiobutton"
      },
      quizStarting: false,
      quizTesting: false,
      testPassed: false,
      finishedQcm: false,
      updateProposal: 0,
      loading: true,
      translations: {
        startingQcm: Joomla.JText._("MOD_EM_QCM_STARTING"),
        restartQcm: Joomla.JText._("MOD_EM_QCM_RESTART"),
        ready: Joomla.JText._("MOD_EM_QCM_ARE_YOU_READY"),
        qcmCompleted: Joomla.JText._("MOD_EM_QCM_COMPLETED"),
        qcmSuccesfull: Joomla.JText._("MOD_EM_QCM_SUCCESSFULL"),
      }
    };
  },
  methods: {
    startQcm(){
      if(!this.quizTesting){
        this.quizTesting = true;
      } else {
        this.quizTesting = false;
        this.quizStarting = true;
      }
    },
    getQuestions(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=qcm&task=getQuestions",
        params: {
          questions: this.questions,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.applicant_questions = response.data;
        this.count = Object.keys(this.applicant_questions).length;
        if(this.step >= this.count){
          this.finishedQcm = true;
        }
        this.loading = false;
      });
    },
    nextQuestion(){
      this.step++;
      if(this.step < Object.keys(this.applicant_questions).length){
        this.updateProposal++;
      } else {
        this.finishedQcm = true;
        setTimeout(() => {
          window.location.reload();
        },3000);
      }
    },
    saveAnswer(answer){
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=qcm&task=saveanwser",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          answer: answer,
          question: this.applicant_questions[this.step].id,
          formid: this.formid,
          module: this.module,
        })
      }).then((result) => {

      });
    }
  },
  created() {
    const elem = document.querySelector('form[name="form_' + this.formid + '"');
    elem.remove();
    this.getQuestions();
  }
}
</script>

<style scoped>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
.em-print-button{
  width: max-content;
  margin: 0 auto;
}
</style>
