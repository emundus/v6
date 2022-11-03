<template>
	<div id="form-builder-create-page" class="em-w-100 em-p-32">
		<h3 class="em-mb-4">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE') }}</h3>
		<p>{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE_INTRO') }}</p>
		<section id="new-page">
			<div
					class="em-mt-16 em-mr-16 em-mb-16 card-wrapper"
					:class="{selected: -1 === selected}"
					@click="selected = -1;"
			>
				<div class="em-shadow-cards em-pointer em-flex-row">
					<span class="add_circle material-icons-outlined">add_circle</span>
				</div>
				<p class="em-mt-8 em-p-4" contenteditable="true"> {{ page.label[shortDefaultLang] }}</p>
			</div>
		</section>
		<h4 class="em-mb-4 em-mt-4">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE_FROM_MODEL') }}</h4>
		<section id="models" class="em-flex-row">
			<p v-if="models.length < 1 && !loading">{{ translate('COM_EMUNDUS_FORM_BUILDER_EMPTY_PAGE_MODELS') }}</p>
			<div v-if="!loading" class="em-flex-row">
				<div
						v-for="model in models" :key="model.id"
						class="em-mr-16 card-wrapper"
						:class="{selected: model.id === selected}"
						:title="model.label[shortDefaultLang]"
						@click="selected = model.id"
				>
					<form-builder-preview-form :form_id="model.form_id" class="em-shadow-cards model-preview em-pointer">
					</form-builder-preview-form>
					<p class="em-mt-8 em-p-4"> {{ model.label[shortDefaultLang] }}</p>
				</div>
			</div>
			<div v-else class="em-flex-row">
				<div v-for="i in 5" :key="i" class="em-mr-16 em-flex-column">
					<skeleton width="150px" height="200px" classes="em-shadow-cards model-preview"></skeleton>
					<skeleton width="190px" height="20px" classes="em-mt-8 em-p-4"></skeleton>
				</div>
			</div>
		</section>
		<div class="actions em-flex-row-justify-end em-w-100">
			<button class="em-secondary-button em-w-max-content" @click="close">{{ translate('COM_EMUNDUS_FORM_BUILDER_CANCEL') }}</button>
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
				}
				this.close();
			});
		},
		close()
		{
			this.$emit('close');
		}
	}
}
</script>

<style lang="scss" scoped>
#form-builder-create-page {
	height: calc(100vh - 42px);
	overflow-y: auto;
	background-color: #E3E5E8;

	.add_circle {
		color: #20835F;
	}

	.card-wrapper {
		width: 198px;

		.em-shadow-cards {
			background-color: white;
			width: 150px;
			border: 2px solid transparent;
		}

		p {
			text-align: center;
			border-radius: 4px;
			padding: 4px;
			transition: all .3s;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		&.selected {
			.em-shadow-cards {
				border: 2px solid #20835F;
			}

			p {
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
}
</style>