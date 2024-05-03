<template>
  <div id="application-tabs">
    <div class="flex flex-row items-center justify-center sticky-tab gap-1 w-100 overflow-auto">
      <div v-for="tab in tabs" v-if="access[tab.access].r" class="cursor-pointer shadow rounded-t-lg px-2.5 py-3" @click="selected = tab.name"
           :class="selected === tab.name ? 'em-bg-main-500 em-text-neutral-300' : ''">
        <span class="em-font-size-14 whitespace-nowrap">{{ translate(tab.label) }}</span>
      </div>
    </div>
    <div class="shadow mr-4 ml-4 mb-4 p-2 rounded-lg em-border-top-neutral-300">
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
      <DecisionForm
          v-if="selected === 'decision'"
          :access="access['29']"
          :fnum="file.fnum"
          :user="user"
      >
      </DecisionForm>
      <AdmissionForm
          v-if="selected === 'admission'"
          :access="access['32']"
          :fnum="file.fnum"
          :user="user"
      >
      </AdmissionForm>
      <IframeTab
          v-if="selectedTab.url"
          :url="selectedTab.url"
          :fnum="file.fnum"
      >

      </IframeTab>
    </div>
  </div>
</template>

<script>
import Attachments from "@/views/Attachments";
import Comments from "@/components/Files/Comments";
import EvaluationForm from "@/components/Files/EvaluationForm.vue";
import DecisionForm from "@/components/Files/DecisionForm.vue";
import AdmissionForm from "@/components/Files/AdmissionForm.vue";
import IframeTab from "@/components/Files/IframeTab.vue";
import filesService from "@/services/files";

export default {
  name: 'ApplicationTabs',
  components: {DecisionForm, EvaluationForm, AdmissionForm, Attachments, Comments, IframeTab},
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
      type: String,
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
      filesService.getApplicationForm(this.file.fnum).then(html => {
        this.applicationform = html;
      });
    },
  },
  computed: {
    selectedTab() {
      return this.tabs.find(tab => tab.name === this.selected);
    },
    urlTabs() {
      return this.tabs.filter((tab) => {
        return tab.url;
      });
    }
  }
}

</script>

<style lang="scss" scoped>
#application-tabs > div {
  width: 100%;
  overflow: scroll;
}
</style>