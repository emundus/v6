<template>

</template>

<script>
import lists from '../data/onboarding_lists/lists.json';

export default {
	name: "list_v2",
	props: {
		type: {
			type: String,
			required: true
		},
		params: {
			type: Object,
			default: {}
		},
	},
	data() {
		return {
			'currentList': {},
			'selectedListTab': 0,
			'items': []
		}
	},
	created() {
		this.initList();
	},
	methods: {
		initList() {
			// check if lists[this.type] exists
			if (typeof lists[this.type] === 'undefined') {
				console.error('List type ' + this.type + ' does not exist');
				return;
			}

			this.currentList = lists[this.type];

			if (this.params.hasOwnProperty('tab')) {
				this.selectedListTab = this.params.tab;
			}

			this.getListItems();
		},
		getListItems() {

		}
	}
}
</script>

<style scoped>

</style>