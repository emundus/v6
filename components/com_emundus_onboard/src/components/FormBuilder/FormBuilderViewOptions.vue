<template>
	<div id="form-builder-options em-white-bg ">
		<div class='em-border-bottom-neutral-700 em-p-16 em-w-380 em-mb-16'>
			<h5>Éléments</h5>
		</div>
		<FormBuilderViewOptionElement :element="element" v-for="(element,index) in elements" :key="'element_' + index"/>
	</div>
	
</template>

<script>
import FormBuilderElements from '../../data/formBuilderElements'
import FormBuilderViewOptionElement from './FormBuilderViewOptionElement.vue';
export default {
	name: "",
	components: {
		FormBuilderViewOptionElement
	},
	props: {

	},
	data() {
		return {
			elements: []
		}
	},
	created() {
		this.getElements();
	},
	methods: {
		getElements() {

			this.elements=FormBuilderElements;
			console.log(typeof(this.elements[0]))
			
		}
	},
	computed: {

	},
}
</script>

<style>

</style>