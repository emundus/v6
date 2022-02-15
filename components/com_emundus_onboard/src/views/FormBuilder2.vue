<template>
	<div id="form-builder" class="em-flex-column">
		<FormBuilderHead :mode="mode" :profile_id="profile_id" :campaign_id="campaign_id"></FormBuilderHead>
		<FormBuilderView :mode="mode" :profile_id="profile_id" :campaign_id="campaign_id"></FormBuilderView>
	</div>
</template>

<script>
import FormBuilderHead from "@/components/FormBuilder/FormBuilderHead.vue";
import FormBuilderView from "@/components/FormBuilder/FormBuilderView.vue";
import formBuilder from "@/store/formBuilder";
import { global } from "@/store/global";

export default {
	name: "FormBuilder",
	components: {
		FormBuilderHead,
		FormBuilderView,
	},
	data() {
		return {
			profile_id: 0,
			campaign_id: 0,
			mode: 'add'
		}
	},
	created() {
		this.setDatas();
		this.setMode();
		this.initForm();
	},
	methods: {
		setDatas() {
			this.profile_id = global.getters.datas.profile_id.value;
			this.campaign_id = global.getters.datas.campaign_id.value;

			if (typeof this.profile_id === undefined || typeof this.campaign_id === undefined) {
				console.log(error);
				// TODO redirect 
			}

			console.log(this.profile_id);
			console.log(this.campaign_id);
		},
		setMode() {
			if (this.id) {
				this.mode = 'edit';
			} else {
				this.mode = 'add';
			}
		},
		initForm() {
			formBuilder.dispatch('initForm', {
				profile_id: this.profile_id,
				campaign_id: this.campaign_id,
				mode: this.mode,
			});
		},
	}
}
</script>
