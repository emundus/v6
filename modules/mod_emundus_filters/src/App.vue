<template>
  <div id="emundus-filters">
	  <p>{{ moduleId }}</p>
  </div>
</template>

<script>

export default {
  name: 'App',
	props: {
		moduleId: {
			type: Number,
			required: true
		},
		appliedFilters: {
			type: Array,
			default: () => []
		},
		filters: {
			type: Array,
			default: () => []
		},
	},
	mounted() {
		console.log(this.moduleId);
		console.log(this.appliedFilters);
		console.log(this.filters);
	}
}
</script>