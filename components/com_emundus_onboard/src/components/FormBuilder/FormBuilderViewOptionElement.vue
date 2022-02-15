<template>
	
		<div class="em-flex-row em-flex-space-between em-p-16 em-border-neutral-300 em-mb-16 em-lightgray-bg em-w-380" :id="'element_' + element.value">
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

	
</template>

<script>

export default {
	name: "",
	components: {},
	props: {
        element:{
            type: Object,
            required:true
        }
	},
	data() {
		return {
			
		}
	},
	created() {
		
	},
	methods: {
		
	},
	computed: {

	},
}
</script>

<style>

</style>