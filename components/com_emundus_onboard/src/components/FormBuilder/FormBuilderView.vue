<template>
	<div id="form-builder-view" class="em-flex-row em-flex-space-between">
		<FormBuilderViewOptions></FormBuilderViewOptions>
		<FormBuilderViewPage></FormBuilderViewPage>
		<FormBuilderViewHierarchy></FormBuilderViewHierarchy>
	</div>
</template>

<script>
import FormBuilderViewHierarchy from './FormBuilderViewHierarchy.vue';
import FormBuilderViewPage from './FormBuilderViewPage.vue';
import FormBuilderViewOptions from './FormBuilderViewOptions.vue';

export default {
	name: 'FormBuilderView',
	components: {
		FormBuilderViewHierarchy,
		FormBuilderViewPage,
		FormBuilderViewOptions,
	},
}
</script>

<style lang="scss">
#form-builder-view {
	width: 100%;
}
</style>