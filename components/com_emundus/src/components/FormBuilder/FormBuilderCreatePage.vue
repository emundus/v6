<template>
	<div id="form-builder-create-page" class="em-w-100 em-p-32 em-pt-16">
		<div>
			<h3 class="em-mb-4 em-text-neutral-800">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE') }}</h3>
			<p>{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE_INTRO') }}</p>
			<section id="new-page">
				<div class="em-mt-16 em-mb-16 card-wrapper" :class="{selected: -1 === selected}" @click="selected = -1;">
					<div class="card em-shadow-cards em-pointer em-flex-row">
						<span class="add_circle material-icons-outlined em-main-500-color">add_circle</span>
					</div>
					<input
							type="text"
							v-model="page.label[shortDefaultLang]"
							class="em-p-4"
							:class="{
								'em-color-white': -1 === selected,
								'em-bg-main-500':  -1 === selected
							}"
					>
				</div>
			</section>
			<div class="separator em-mt-32">
				<p class="line-head em-mt-4 em-p-8 em-color-white em-bg-main-500">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE_FROM_MODEL') }}</p>
				<div class="line em-bg-main-500"></div>
			</div>
			<section id="models" class="em-flex-row em-w-100">
				<div v-if="!loading" class="em-w-100">
					<div id="search-model-wrapper">
						<input id="search-model" class="em-mt-16" type="text" v-model="search" placeholder="Rechercher"/>
						<span class="reset-search material-icons-outlined em-pointer" @click="search = ''">close</span>
					</div>
					<div class="models-card em-flex-row">
						<div
							v-for="model in models" :key="model.id"
							class="card-wrapper em-mr-32"
							:class="{selected: model.id === selected, hidden: !model.displayed}"
							:title="model.label[shortDefaultLang]"
							@click="selected = model.id"
						>
							<form-builder-preview-form
								:form_id="Number(model.form_id)"
								:form_label="model.label[shortDefaultLang]"
								class="card em-shadow-cards model-preview em-pointer"
								:class="{
									'em-color-white': model.id === selected,
									'em-bg-main-500': model.id === selected
								}"
							>
							</form-builder-preview-form>
							<p class="em-p-4" :class="{
								'em-color-white': model.id === selected,
								'em-bg-main-500': model.id === selected
							}">
								{{ model.label[shortDefaultLang] }}
							</p>
						</div>

						<div v-if="displayedModels.length < 1" class="empty-model-message em-w-100 em-text-align-center">
							<span class="material-icons-outlined">manage_search</span>
							<p class="em-w-100"> {{ translate('COM_EMUNDUS_FORM_BUILDER_EMPTY_PAGE_MODELS') }}</p>
						</div>
					</div>
				</div>
				<div v-else class="em-w-100">
					<skeleton width="206px" height="41px" classes="em-mt-16 em-mb-16 em-border-radius-5"></skeleton>
					<div class="models-card em-grid">
						<div v-for="i in 16" :key="i" class="em-flex-column card-wrapper em-mr-24">
							<skeleton width="150px" height="200px" classes="card em-shadow-cards model-preview"></skeleton>
							<skeleton width="150px" height="20px" classes="em-p-4"></skeleton>
						</div>
					</div>
				</div>
			</section>
		</div>
		<div class="actions em-flex-row-justify-end em-w-100">
			<button class="em-secondary-button em-w-max-content em-white-bg" @click="close(false)">{{ translate('COM_EMUNDUS_FORM_BUILDER_CANCEL') }}</button>
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
			},
			search: ''
		};
	},
	created() {
		this.getModels();
	},
	methods: {
		getModels() {
			formBuilderService.getModels().then((response) => {
				if (response.status) {
					this.models = response.data.map((model) => {
						model.displayed = true;

						return model;
					});
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
					this.close(true, response.data.id);
				}
			});
		},
		close(reload = true, newSelected = 0)
		{
			this.$emit('close', {
				'reload': reload,
				'newSelected': newSelected
			});
		}
	},
	computed: {
		displayedModels() {
			return this.models.filter((model) => {
				return model.displayed;
			})
		}
	},
	watch: {
		search: function() {
			this.models.forEach((model) => {
				model.displayed = model.label[this.shortDefaultLang].toLowerCase().includes(this.search.toLowerCase().trim());
			});
		}
	}
}
</script>

<style lang="scss" scoped>
#form-builder-create-page {
	height: calc(100vh - 42px);
	overflow-y: auto;
	background-color: #F2F2F3;

	.line-head {
		border-top-left-radius: 4px;
		border-top-right-radius: 4px;
		width: fit-content;
		color: white !important;
	}

	.line {
		height: 4px;
	}

	.card-wrapper {
		width: 150px;

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
			text-align: center;
		}

		&.selected {
			.em-shadow-cards {
				border: 2px solid #20835F;
			}

			p, input {
				color: white !important;
				background-color: #20835F !important;
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

	#models .models-card {
		grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
		margin-bottom: 64px;
	}

	#search-model-wrapper {
		position: relative;

		.reset-search {
			position: absolute;
			top: 27px;
			right: 10px;
		}
	}

	.empty-model-message {
		margin: 120px;

		.material-icons-outlined {
			font-size: 42px;
		}
	}

	.actions {
		position: fixed;
		bottom: 0;
		right: 0;
		padding: 16px 32px;
		background: linear-gradient(to top, white, transparent);
	}
}
</style>

