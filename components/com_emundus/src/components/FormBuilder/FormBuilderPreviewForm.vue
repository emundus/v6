<template>
	<div id="form-builder-preview-form" class="em-h-100 em-w-100" :class="{loading: loading}">
		<div v-if="!loading">
			<h1 class="em-w-100 em-text-align-left">{{ formData.show_title.label[shortDefaultLang] }}</h1>
			<div class="preview-groups em-flex-column">
				<section v-for="group in formData.Groups" :key="group.group_id" class="em-mb-8">
					<h2 class="em-w-100 em-text-align-left">{{ group.label[shortDefaultLang] }}</h2>
				</section>
			</div>
		</div>
		<skeleton v-else height="100%" width="100%"></skeleton>
	</div>
</template>

<script>
import formService from '../../services/form';
import Section from '../Users/Section';
import Skeleton from '../Skeleton';

export default {
	name: "FormBuilderPreviewForm",
	components: {Skeleton, Section},
	props: {
		form_id: {
			type: Number,
			required: true
		}
	},
	data() {
		return {
			loading: true,
			formData: {}
		}
	},
	created() {
		formService.getPageObject(this.form_id).then((response) => {
			if (response.status) {
				this.formData = response.data;
			}

			this.loading = false;
		});
	},
	methods: {

	}
}
</script>

<style lang="scss">
#form-builder-preview-form {
	padding: 8px !important;

	&.loading {
		padding: 0 !important;
		border: unset !important;
	}

	h1 {
		font-size: 12px !important;
		margin-bottom: 8px;
	}

	h2 {
		font-size: 10px !important;
	}

	.preview-groups {
		justify-content: flex-start;

		section {
			width: 90%;
		}
	}
}
</style>