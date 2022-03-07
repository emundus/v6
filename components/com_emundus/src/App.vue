<template>
	<div class="com_emundus_vue em-flex-column em-flex-col-center">
		<Attachments
			v-if="componentName === 'attachments'"
			:fnum="data.fnum"
			:user="data.user"
		></Attachments>

    <transition v-else name="slide-right">
      <component v-bind:is="$props.component"/>
    </transition>
	</div>
</template>

<script>
import moment from "moment";

//Register my components
import Attachments from "./views/Attachments.vue";
import list from "./views/list";
import addcampaign from "./views/addCampaign"
import addemail from "./views/addEmail"
import addformnextcampaign from "./views/addFormNextCampaign"
import formbuilder from "./views/formBuilder"
import evaluationbuilder from "./views/evaluationBuilder"
import settings from "./views/globalSettings"

export default {
	props: {
    component: String,
    datas: Object,
    actualLanguage: String,
    manyLanguages: String,
    coordinatorAccess: String,
		componentName: {
			type: String,
			required: true,
		},
		data: {
			type: Object,
			default: {},
		},
	},
	components: {
		Attachments,
    list,
    addcampaign,
    addformnextcampaign,
    addemail,
    formbuilder,
    evaluationbuilder,
    settings,
	},

  created() {
    if(typeof this.$props.datas != 'undefined') {
      this.$store.commit("global/initDatas", this.$props.datas);
    }
    if(typeof this.$props.actualLanguage != 'undefined') {
      this.$store.commit("global/initCurrentLanguage", this.$props.actualLanguage);
    }
    if(typeof this.$props.manyLanguages != 'undefined') {
      this.$store.commit("global/initManyLanguages", this.$props.manyLanguages);
    }
    if(typeof this.$props.coordinatorAccess != 'undefined') {
      this.$store.commit("global/initCoordinatorAccess", this.$props.coordinatorAccess);
    }
  },

	mounted() {
		if (this.data.lang) {
			this.$store.dispatch("global/setLang", this.data.lang.split("-")[0]);
		} else {
			this.$store.dispatch("global/setLang", "fr");
		}

		moment.locale(this.$store.state.global.lang);

		// baseUrl
		if (this.data.base) {
			this.$store.dispatch(
				"attachment/setAttachmentPath",
				this.data.base + "/images/emundus/files/"
			);
		}
	},
};
</script>

<style lang='scss'>
@import url("./assets/css/main.scss");

.com_emundus_vue {
  input {
    display: block;
    margin-bottom: 10px;
    padding: 8px 12px;
    border: 2px solid #cccccc;
    border-radius: 4px;
    -webkit-transition: border-color 200ms linear;
    transition: border-color 200ms linear;
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
      color: dimgrey;
    }
    &:-ms-input-placeholder {
      color: dimgrey;
    }
    &::-ms-input-placeholder {
      color: dimgrey;
    }
    &::placeholder {
      color: dimgrey;
    }
  }

  @media (max-width: 1440px) {
    .v--modal-box.v--modal {
      width: 90vw !important;
      height: 90vh !important;
      top: 0 !important;
      left: 0 !important;
      margin: 5vh auto 5vh auto !important;
    }
  }
}

.view-campaigns #g-container-main .g-container,
.view-emails #g-container-main .g-container,
.view-form #g-container-main .g-container,
.view-settings #g-container-main .g-container{
  width: 90%;
}


</style>
