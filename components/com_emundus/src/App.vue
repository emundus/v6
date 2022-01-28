<template>
	<div class="com_emundus_vue">
		<Attachments
			v-if="componentName === 'attachments'"
			:fnum="data.fnum"
			:user="data.user"
		></Attachments>
	</div>
</template>

<script>
import moment from "moment";
import Attachments from "./views/Attachments.vue";

export default {
	props: {
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
</style>
