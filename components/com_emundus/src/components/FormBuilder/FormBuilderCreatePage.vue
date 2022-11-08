<template>
	<div id="form-builder-create-page" class="em-w-100 em-p-32">
		<h3 class="em-mb-4">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE') }}</h3>
		<p>{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE_INTRO') }}</p>
		<section id="new-page">
			<div
					class="em-mt-16 em-mb-16 card-wrapper"
					:class="{selected: -1 === selected}"
					@click="selected = -1;"
			>
				<div class="card em-shadow-cards em-pointer em-flex-row">
					<span class="add_circle material-icons-outlined">add_circle</span>
				</div>
				<input class="em-p-4" type="text" v-model="page.label[shortDefaultLang]">
			</div>
		</section>
		<div class="separator em-mt-32">
			<p class="line-head em-mt-4 em-p-8">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE_FROM_MODEL') }}</p>
			<div class="line"></div>
		</div>
		<section id="models" class="em-flex-row em-w-100">
			<p v-if="models.length < 1 && !loading">{{ translate('COM_EMUNDUS_FORM_BUILDER_EMPTY_PAGE_MODELS') }}</p>
			<div v-if="!loading" class="em-flex-row em-w-100">
				<div
						v-for="model in models" :key="model.id"
						class="card-wrapper"
						:class="{selected: model.id === selected}"
						:title="model.label[shortDefaultLang]"
						@click="selected = model.id"
				>
					<form-builder-preview-form :form_id="Number(model.form_id)" :form_label="model.label[shortDefaultLang]"  class="card em-shadow-cards model-preview em-pointer">
					</form-builder-preview-form>
					<p class="em-p-4"> {{ model.label[shortDefaultLang] }}</p>
				</div>
			</div>
			<div v-else class="em-flex-row em-w-100">
				<div v-for="i in 5" :key="i" class="em-flex-column card-wrapper">
					<skeleton width="150px" height="200px" classes="card em-shadow-cards model-preview"></skeleton>
					<skeleton width="150px" height="20px" classes="em-p-4"></skeleton>
				</div>
			</div>
		</section>
		<div class="actions em-flex-row-justify-end em-w-100">
			<button class="em-secondary-button em-w-max-content" @click="close(false)">{{ translate('COM_EMUNDUS_FORM_BUILDER_CANCEL') }}</button>
			<button class="em-primary-button em-w-max-content em-ml-8" @click="createPage">{{ translate('COM_EMUNDUS_FORM_BUILDER_PAGE_CREATE_SAVE') }}</button>
		</div>
	</div>
</template>

<script>
import FormBuilderPreviewForm from "./FormBuilderPreviewForm";
import formBuilderService from '../../services/formbuilder';
import Skeleton from '../Skeleton'

export default {
	name: "FormBuilderCreatePage.vue",
	components: {
		Skeleton,
		FormBuilderPreviewForm
	},
	props: {
		profile_id: {
			type: Number,
			required: true
		}
	},
	data() {
		return {
			loading: true,
			selected: -1,
			models: [],
			page: {
				label: {
					fr: 'Nouvelle page',
					en: 'New page'
				},
				intro: {
					fr: '',
					en: ''
				},
				prid: this.profile_id,
				template: 0,
			}
		};
	},
	created() {
		this.getModels();
	},
	methods: {
		getModels() {
			formBuilderService.getModels().then((response) => {
				if (response.status) {
					this.models = response.data;
				} else {
					Swal.fire({
						type: 'warning',
						title: this.translate('COM_EMUNDUS_FORM_BUILDER_GET_PAGE_MODELS_ERROR'),
						reverseButtons: true,
						customClass: {
							title: 'em-swal-title',
							confirmButton: 'em-swal-confirm-button',
							actions: "em-swal-single-action",
						}
					});
				}

				this.loading = false;
			});
		},
		createPage() {
			let model_form_id = -1;
			if (this.selected > 0) {
				const found_model = this.models.find((model) => {
					return model.id === this.selected
				});

				if (found_model) {
					model_form_id = found_model.form_id;
					this.page.label = found_model.label;
					this.page.intro = found_model.intro;
				}
			}

			const data = {...this.page, modelid: model_form_id};
			formBuilderService.addPage(data).then(response => {
				if (!response.status) {
					Swal.fire({
						type: 'error',
						title: this.translate('COM_EMUNDUS_FORM_BUILDER_CREATE_PAGE_ERROR'),
						reverseButtons: true,
						customClass: {
							title: 'em-swal-title',
							confirmButton: 'em-swal-confirm-button',
							actions: "em-swal-single-action",
						}
					});
					this.close(false);
				} else {
					this.close();
				}
			});
		},
		close(reload = true)
		{
			this.$emit('close', reload);
		}
	}
}
</script>

<style lang="scss" scoped>
#form-builder-create-page {
	height: calc(100vh - 42px);
	overflow-y: auto;
	background-color: #F2F2F3;

	.add_circle {
		color: #20835F;
	}

	.line-head {
		background-color: #20835F;
		border-top-left-radius: 4px;
		border-top-right-radius: 4px;
		color: white;
		width: fit-content;
	}

	.line {
		height: 4px;
		background-color: #20835f;
	}

	.card-wrapper {
		width: 150px;
		margin-right: 40px;

		.em-shadow-cards {
			background-color: white;
			width: 150px;
			border: 2px solid transparent;
		}

		.card {
			margin: 24px 0 12px 0;
		}

		p {
			text-align: center;
			border-radius: 4px;
			padding: 4px;
			transition: all .3s;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			font-size: 12px;
		}

		input {
			width: 150px;
			height: 20px;
			font-size: 12px;
			border: 0;
			background-color: transparent;
			text-align: center;
		}

		&.selected {
			.em-shadow-cards {
				border: 2px solid #20835F;
			}

			p, input {
				color: white;
				background-color: #20835F;
			}
		}
	}

	#new-page {
		.material-icons-outlined {
			margin: auto;
		}
	}

	.model-preview {
		overflow: hidden;
	}

	#models > div {
		flex-wrap: wrap;
	}
}
</style>