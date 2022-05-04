import { shallowMount } from '@vue/test-utils';
import ListTable from '../../src/components/List/Table/ListTable.vue';
import rows from '../../src/data/tableRows';

describe('ListTable.vue', () => {
	const wrapper = shallowMount(ListTable, {
		propsData: {
			type: "campaign",
			actions: {}
		}	
	});

	it("#list-table should be rendered", () => {
		expect(wrapper.find('#list-table').exists()).toBe(true);
	});

	it("#list-table should have a table", () => {
		expect(wrapper.find('table').exists()).toBe(true);
	});

	it("#list-table should have a table header", () => {
		expect(wrapper.find('thead').exists()).toBe(true);
	});

	it("#list-table table header should contain n <th>, n corresponding to rows entries", () => {
		expect(wrapper.findAll('thead th').length).toBe(rows[wrapper.vm.$props.type].length);
	});

});