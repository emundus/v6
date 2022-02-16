<template>
	<div id="form-builder-options">

		<div class='em-border-bottom-neutral-700 em-p-16 em-w-380 em-mb-16'>

			<h5>Éléments</h5>

		</div>

		
		<div class="em-flex-row em-flex-space-between em-p-16 em-border-neutral-300 em-mb-16 em-lightgray-bg em-w-380" :id="'element_' + element.value" v-for="(element,index) in elements" :key="'element_' + index" >
			<div class='em-flex-row em-flex-start' >
					<span class="material-icons">
						{{element.icon}}
					</span>	
					<span class="ml-10px">
						{{translate(element.name)}}
					</span>
			
			</div>
			<div>
				<span class="material-icons ">
					apps
				</span>
			</div>
		</div>
	</div>
	
</template>

<script>

import FormBuilderElements from '../../data/formBuilderElements'

export default {
	name: "",
	components: {
		
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
			this.elements = FormBuilderElements;
			
		}
	},
	computed: {

	},
}
</script>

<style>

</style>