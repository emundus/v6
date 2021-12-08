import { shallowMount, mount, createLocalVue } from '@vue/test-utils';
import App from '../../src/App.vue';
import store from '../../src/store';
import translate from '../mocks/mixins/translate';
import VModal from 'vue-js-modal';
import moment from 'moment';
import Vuex from 'vuex';

describe('App.vue', () => {
	const wrapper = shallowMount(App, {
		propsData: {
			componentName: 'attachments',
			data: {
				lang: 'fr',
				fnum: '123456789',
				user: '96'
			}
		},
		mixins: [translate],
		store
	});

	it('should render the app', () => {
		expect(wrapper.find('.com_emundus_vue').exists()).toBe(true);
	});

	it('should render the app with the correct props', () => {
		expect(wrapper.props().componentName).toBe('attachments');
		expect(wrapper.props().data.lang).toBe('fr');
		expect(wrapper.props().data.fnum).toBe('123456789');
		expect(wrapper.props().data.user).toBe('96');
	});

	it('should store the language in vuex', () => {
		expect(store.state.global.lang).toBe('fr');
	});

	it('should set moment.js locale to be equals to data.lang props', () => {
		expect(moment.locale()).toBe('fr');
	});
});

describe('App.vue', () => {
	const localVue = createLocalVue();
	localVue.use(Vuex);
	localVue.mixin(translate);
	localVue.use(VModal);

	const wrapper = mount(App, {
		propsData: {
			componentName: 'attachments',
			data: {
				lang: 'fr',
				fnum: '123456789',
				user: '96'
			}
		},
		store: store,
    localVue
	});

	it('should render attachments component', () => {
		expect(wrapper.find('#em-attachments').exists()).toBe(true);
	});
});
