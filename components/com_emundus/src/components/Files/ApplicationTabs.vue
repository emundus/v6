<template>
  <div id="application-tabs">
    <div class="em-flex-row em-flex-center em-gap-16 em-border-bottom-neutral-300 sticky-tab">
      <div v-for="tab in tabs" v-if="access[tab.access].r" class="em-light-tabs em-pointer" @click="selected = tab.name"
           :class="selected === tab.name ? 'em-light-selected-tab' : ''">
        <span class="em-font-size-14">{{ translate(tab.label) }}</span>
      </div>
    </div>
    <div v-if="selected === 'application'" v-html="applicationform"></div>
    <Attachments
        v-if="selected === 'attachments'"
        :fnum="file.fnum"
        :user="user"
        :columns="['name','date','category','status']"
        :displayEdit="false"
    />
    <Comments
        v-if="selected === 'comments'"
        :fnum="file.fnum"
        :user="user"
        :access="access['10']"
    />
    <EvaluationForm
        v-if="selected === 'evaluation'"
        :access="access['5']"
        :fnum="file.fnum"
        :user="user"
    >
    </EvaluationForm>
  </div>
</template>

<script>
import Attachments from "@/views/Attachments";
import Comments from "@/components/Files/Comments";
import axios from "axios";
import EvaluationForm from "@/components/Files/EvaluationForm.vue";

export default {
  name: 'ApplicationTabs',
  components: {EvaluationForm, Attachments, Comments},
  props: {
    tabs: {
      type: Array,
      default: () => ([
        {
          label: 'COM_EMUNDUS_FILES_APPLICANT_FILE',
          name: 'application',
          access: '1'
        },
        {
          label: 'COM_EMUNDUS_FILES_ATTACHMENTS',
          name: 'attachments',
          access: '4'
        },
        {
          label: 'COM_EMUNDUS_FILES_COMMENTS',
          name: 'comments',
          access: '10'
        },
      ])
    },
    access: {
      type: Object,
      required: true
    },
    file: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      selected: 'application',
      applicationform: ''
    }
  },
  mounted() {
    if (this.tabs.some(tab => tab.name === 'application')) {
      this.getApplicationForm();
    }
  },
  methods: {
    getApplicationForm(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&view=application&format=raw&layout=form&fnum="+this.file.fnum,
      }).then(response => {
        this.applicationform = response.data;
      });
    },
  },
  computed: {
    selectedTab() {
      return this.tabs.find(tab => tab.name === this.selected);
    },
  }
}

</script>

<style scoped>

</style>