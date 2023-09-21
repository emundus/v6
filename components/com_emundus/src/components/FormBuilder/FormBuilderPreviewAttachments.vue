<template>
	<div id="form-builder-preview-attachment" class="em-h-100 em-w-100" :class="{loading: loading}">
		<div v-if="!loading" class="em-text-align-center">
      <img src="/media/com_emundus/images/icones/filetype/doc.png" class="em-m-center" style="width: 50px" alt="filetype">
			<p class="em-font-size-12 em-w-100 em-mt-16">{{ document_label }}</p>
		</div>
		<skeleton v-else height="100%" width="100%"></skeleton>
	</div>
</template>

<script>
import formService from '../../services/form';
import Skeleton from '../Skeleton';

export default {
	name: "FormBuilderPreviewAttachments",
	components: {Skeleton},
	props: {
		document_id: {
			type: Number,
			required: true
		},
		document_label: {
			type: String,
			default: ''
		}
	},
	data() {
		return {
			loading: false,
		}
	},
	created() {},
	methods: {}
}
</script>

<style lang="scss">
#form-builder-preview-attachment {
	padding: 8px !important;
	font-size: 6px;
	background-color: #F1F1F1 !important;
  overflow: hidden;
  height: 150px;
  align-items: center;
  display: flex;
  flex-direction: column;
  justify-content: center;

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