<template>
	<div class="com_emundus_vue">
		<Attachements :fnum="data.fnum" :user="data.user"></Attachements>
	</div>
</template>

<script>
import moment from "moment";
import Attachements from "./views/Attachments.vue";

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
		Attachements,
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
</style>
