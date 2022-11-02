<template>
	<div id="form-builder-create-page" class="em-w-100 em-p-32">
		<h3>{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE') }}</h3>
		<p>{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE_INTRO') }}</p>
		<section id="new-page">
			<div
					class="em-mt-16 em-mr-16 em-mb-16 card-wrapper"
					:class="{selected: -1 == selected}"
					@click="selected = -1;"
			>
				<div class="em-shadow-cards em-pointer em-flex-row">
					<span class="add_circle material-icons-outlined">add_circle</span>
				</div>
				<p class="em-mt-8 em-p-4" contenteditable="true"> {{ page.label[shortDefaultLang] }}</p>
			</div>
		</section>
		<h4>{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_NEW_PAGE_FROM_MODEL') }}</h4>
		<section id="models" class="em-flex-row">
			<p v-if="models.length < 1">{{ translate('COM_EMUNDUS_FORM_BUILDER_EMPTY_PAGE_MODELS') }}</p>
			<div class="em-mt-16 em-mr-16 em-mb-16">
				<div
						v-for="model in models" :key="model.id"
						class="em-mr-16 card-wrapper"
						:class="{selected: model.id == selected}"
						@click="selected = model.id"
				>
					<div class="em-shadow-cards model-preview em-pointer">
						<span></span>
					</div>
					<p class="em-mt-8 em-p-4"> {{ model.label[shortDefaultLang] }}</p>
				</div>
			</div>
		</section>
		<div class="actions em-flex-row-justify-end em-w-100">
			<button class="em-primary-button em-w-33" @click="createPage">{{ translate('COM_EMUNDUS_FORM_BUILDER_PAGE_CREATE_SAVE') }}</button>
		</div>
	</div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';

export default {
	name: "FormBuilderCreatePage.vue",
	props: {
		profile_id: {
			type: Number,
			required: true
		}
	},
	data() {
		return {
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
			});
		},
		createPage() {
			formBuilderService.addPage({...this.page, modelid: this.selected}).then(response => {
				if (!response.status) {

				}
				this.$emit('close');
			});
		}
	}
}
</script>

<style lang="scss" scoped>
#form-builder-create-page {
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
}
</style>