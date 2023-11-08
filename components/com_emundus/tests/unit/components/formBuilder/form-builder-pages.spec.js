import {mount, createLocalVue} from '@vue/test-utils';
import {VPopover} from 'v-tooltip';
import FormBuilderPages from '../../../../src/components/FormBuilder/FormBuilderPages';
import translate from '../../../mocks/mixins/translate';
import draggable from 'vuedraggable';
import store from '../../../../src/store/index';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(draggable);
localVue.component('v-popover', VPopover);

const mockPages = [
    {
        'link': 'index.php?option=com_fabrik&view=form&formid=1',
        'rgt': '1',
        'id': '1',
        'label': 'Informations personnelles'
    },
    {
        'link': 'index.php?option=com_fabrik&view=form&formid=2',
        'rgt': '2',
        'id': '2',
        'label': 'Diplômes et formations'
    }
];

describe('FormBuilderPages.vue', () => {
    const wrapper = mount(FormBuilderPages, {
        propsData: {
            pages: mockPages,
            profile_id: 1
        },
        localVue,
        store
    });

    it('FormBuilderPages should exist', () => {
        expect(wrapper.find('#form-builder-pages').exists()).toBeTruthy();
    });

    it('a button to add a new page should exist', () => {
        expect(wrapper.find('#add-page').exists()).toBeTruthy();
    });

    it('should have the same number of pages as the mockPages array', () => {
        expect(wrapper.findAll('.form-builder-page-label').length).toEqual(mockPages.length);
    });
});