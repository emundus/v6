<template>
	<div id="form-builder-preview-form" class="em-h-100 em-w-100" :class="{loading: loading}">
		<div v-if="!loading">
			<p class="em-font-size-12 em-w-100 em-text-align-left em-mb-16">{{ form_label }}</p>
			<div class="preview-groups em-flex-column">
				<section v-for="(group, index) in formData.groups" :key="group.id" class="em-mb-8 form-builder-page-section">
					<div class="section-card em-flex-column">
						<div class="section-identifier em-bg-main-500 em-flex-row">
							<span>{{ translate('COM_EMUNDUS_FORM_BUILDER_SECTION') }} {{ index + 1 }} / {{ formData.groups.length }}</span>
						</div>
						<div class="section-content em-w-100">
							<p class="em-font-size-8 em-w-100 em-text-align-left">{{ group.label.replace('Model - ', '') }}</p>
						</div>
					</div>
				</section>
			</div>
		</div>
		<skeleton v-else height="100%" width="100%"></skeleton>
	</div>
</template>

<script>
import formService from '../../services/form';
import Skeleton from '../Skeleton';

export default {
	name: "FormBuilderPreviewForm",
	components: {Skeleton},
	props: {
		form_id: {
			type: Number,
			required: true
		},
		form_label: {
			type: String,
			default: ''
		}
	},
	data() {
		return {
			loading: true,
			formData: {}
		}
	},
	created() {
		formService.getPageGroups(this.form_id).then((response) => {
			if (response.status) {
				response.data.groups = response.data.groups.filter((group) => {
					return Number(group.published) === 1;
				});
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
	font-size: 6px;
	background-color: #F1F1F1 !important;
  overflow: hidden;

	&.loading {
		padding: 0 !important;
		border: unset !important;
	}


	p.em-font-size-8 {
		font-size: 8px !important;
	}

	.section-identifier {
		padding: 2px;
		border-radius: 2px 2px 0 0;
	}

	.section-content {
		border-top-width: 2px;
		padding: 2px 4px;
		min-height: 40px;
	}

	.preview-groups {
		justify-content: flex-start;

		section {
			width: 90%;
		}
	}
}
</style>