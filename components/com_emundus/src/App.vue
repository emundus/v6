<template>
	<div class="com_emundus_vue em-flex-col-center">
		<Attachments
			v-if="component === 'attachments'"
			:fnum="data.fnum"
			:user="data.user"
			:defaultAttachments="data.attachments ? data.attachments : null"
		></Attachments>

    <Files
        v-else-if="component === 'files'"
        :type="data.type"
        :user="data.user"
        :ratio="data.ratio"
    ></Files>

    <ApplicationSingle
        v-else-if="component === 'application'"
        :file="data.fnum"
        :type="data.type"
        :user="data.user"
        :ratio="data.ratio"
    ></ApplicationSingle>

    <transition v-else name="slide-right">
      <component v-bind:is="$props.component"/>
    </transition>
	</div>
</template>

<script>
import moment from "moment";

import Attachments from "./views/Attachments.vue";
import Files from './views/Files/Files.vue';

import fileService from "./services/file.js";
import list_v2 from "./views/list.vue";
import addcampaign from "./views/addCampaign"
import addemail from "./views/addEmail"
import addformnextcampaign from "./views/addFormNextCampaign"
import formbuilder from "./views/formBuilder"
import settings from "./views/globalSettings"
import messagescoordinator from "./components/Messages/MessagesCoordinator";
import messages from "./components/Messages/Messages";

import settingsService from "./services/settings.js";
import ApplicationSingle from "@/components/Files/ApplicationSingle.vue";

export default {
	props: {
    datas: NamedNodeMap,
    currentLanguage: String,
    shortLang: String,
    manyLanguages: String,
    coordinatorAccess: String,
    sysadminAccess: String,
		defaultLang: {
			type: String,
			default: 'fr'
		},
		component: {
			type: String,
			required: true
		},
		data: {
			type: Object,
      default: () => ({})
		},
	},
	components: {
    ApplicationSingle,
		Attachments,
    addcampaign,
    addformnextcampaign,
    addemail,
    formbuilder,
    settings,
    messagescoordinator,
    messages,
    Files,
		list_v2
	},

  created() {
    if (this.$props.component === 'attachments') {
      fileService.isDataAnonymized().then(response => {
        if (response.status !== false) {
          this.$store.dispatch("global/setAnonyme", response.anonyme);
        }
      });
    }

    if (this.data.attachments) {
		  this.data.attachments = JSON.parse(atob(this.data.attachments));
	  }

    if (typeof this.$props.datas != 'undefined') {
      this.$store.commit("global/initDatas", this.$props.datas);
    }
    if (typeof this.$props.currentLanguage != 'undefined') {
      this.$store.commit('global/initCurrentLanguage', this.$props.currentLanguage);
	    moment.locale(this.$store.state.global.currentLanguage);
    } else {
	    this.$store.commit('global/initCurrentLanguage', 'fr');
      moment.locale('fr');
    }
    if (typeof this.$props.shortLang != 'undefined') {
      this.$store.commit('global/initShortLang', this.$props.shortLang);
    }
    if (typeof this.$props.manyLanguages != 'undefined') {
      this.$store.commit("global/initManyLanguages", this.$props.manyLanguages);
    }
	  if (typeof this.$props.defaultLang != 'undefined') {
		  this.$store.commit("global/initDefaultLang", this.$props.defaultLang);
	  }
    if (typeof this.$props.coordinatorAccess != 'undefined') {
      this.$store.commit("global/initCoordinatorAccess", this.$props.coordinatorAccess);
    }
    if (typeof this.$props.coordinatorAccess != 'undefined') {
      this.$store.commit("global/initSysadminAccess", this.$props.sysadminAccess);
    }

    settingsService.getOffset().then(response => {
      if (response.status !== false) {
        this.$store.commit("global/initOffset", response.data.data);
      }
    });
  },

  mounted() {
		if (this.data.base) {
			this.$store.dispatch('attachment/setAttachmentPath', this.data.base + '/images/emundus/files/');
		}
	},
};
</script>

<style lang='scss'>
@import url("./assets/css/main.scss");

.com_emundus_vue {
  margin-bottom: 8px;
  input {
    display: block;
    margin-bottom: 10px;
    padding: var(--em-coordinator-vertical) var(--em-coordinator-horizontal);
    border: 1px solid #cccccc;
    border-radius: 4px;
    -webkit-transition: border-color 200ms linear;
    transition: border-color 200ms linear;
    box-sizing: border-box !important;
    &:hover {
      border-color: #cecece;
    }
    &:focus {
      border-color: #16AFE1;
      -webkit-box-shadow: 0 0 6px #e0f3f8;
      -moz-box-shadow: 0 0 6px #e0f3f8;
      box-shadow: 0 0 6px #e0f3f8;
    }
    &::-webkit-input-placeholder {
      color: #A4A4A4;
    }
    &:-ms-input-placeholder {
      color: #A4A4A4;
    }
    &::-ms-input-placeholder {
      color: #A4A4A4;
    }
    &::placeholder {
      color: #A4A4A4;
    }
  }
}

.view-campaigns.no-layout #g-container-main .g-container,
.view-campaigns.layout-addnextcampaign #g-container-main .g-container,
.view-campaigns.layout-add #g-container-main .g-container,
.view-emails.layout-add #g-container-main .g-container,
.view-emails.no-layout #g-container-main .g-container,
.view-form #g-container-main .g-container,
.view-settings #g-container-main .g-container {
  width: auto;
  left: 38px;
  position: relative;
  padding-left: 5%;
  padding-right: 5%;
}

</style>
