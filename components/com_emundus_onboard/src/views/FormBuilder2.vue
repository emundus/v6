<template>
	<div id="form-builder">
		<FormBuilderHead :mode="mode" :id="id" :campaign_id="campaign_id"></FormBuilderHead>
		<FormBuilderView :mode="mode" :id="id" :campaign_id="campaign_id"></FormBuilderView>
	</div>
</template>

<script>
import FormBuilderHead from "@components/FormBuilder/FormBuilderHead.vue";
import FormBuilderView from "@components/FormBuilder/FormBuilderView.vue";
import { formBuilder } from "@store/formBuilder";

export default {
	name: "FormBuilder",
	components: {
		FormBuilderHead,
		FormBuilderView,
	},
	props: {
		id: {
			type: Number,
			required: true,
		},
		campaign_id: {
			type: Number,
			required: true,
		},
	},
	data() {
		return {
			mode: 'add'
		}
	},
	created() {
		this.setMode();
		this.initForm();
	},
	methods: {
		setMode() {
			if (this.id) {
				this.mode = 'edit';
			} else {
				this.mode = 'add';
			}
		},
		initForm() {
			formBuilder.dispatch('initForm', {
				id: this.id,
				campaign_id: this.campaign_id,
				mode: this.mode,
			});
		},
	}
}
</script>
