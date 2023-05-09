import { mount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import List from '../../../src/views/list_v2';
import translate from '../../mocks/mixins/translate';
import store from '../../../src/store/index';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

const ListMock = btoa(JSON.stringify({'forms':{'title':'COM_EMUNDUS_ONBOARD_FORMS','tabs':[{'title':'COM_EMUNDUS_FORM_MY_FORMS','key':'form','controller':'form','getter':'getallform','actions':[{'action':'duplicateform','label':'COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE','controller':'form','name':'duplicate'},{'action':'index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%','label':'COM_EMUNDUS_ONBOARD_MODIFY','controller':'form','type':'redirect','name':'edit'},{'action':'createform','controller':'form','label':'COM_EMUNDUS_ONBOARD_ADD_FORM','name':'add'}],'filters':[]},{'title':'COM_EMUNDUS_FORM_MY_EVAL_FORMS','key':'form_evaluations','controller':'form','getter':'getallgrilleEval','actions':[{'action':'createformeval','label':'COM_EMUNDUS_ONBOARD_ADD_EVAL_FORM','controller':'form','name':'add'},{'action':'/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%&mode=eval','label':'COM_EMUNDUS_ONBOARD_MODIFY','controller':'form','type':'redirect','name':'edit'}],'filters':[]},{'title':'COM_EMUNDUS_FORM_PAGE_MODELS','key':'form_models','controller':'formbuilder','getter':'getallmodels','actions':[{'action':'deleteformmodelfromids','label':'COM_EMUNDUS_ACTIONS_DELETE','controller':'formbuilder','parameters':'&model_ids=%id%','name':'delete'},{'action':'/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%form_id%&mode=models','label':'COM_EMUNDUS_ONBOARD_MODIFY','controller':'form','type':'redirect','name':'edit'}],'filters':[]}]}}));

describe('List view', () => {
    const wrapper = mount(List, {
        propsData: {
            defaultType: 'forms',
            defaultLists: ListMock
        },
        localVue,
        store
    });

    it ('List wrapper should exist', () => {
        expect(wrapper.find('#onboarding_list').exists()).toBeTruthy();
    });

    it ('Current displayed tab should be equald to selected list tab in navigation', () => {
        expect(wrapper.vm.currentTab.key).toBe(wrapper.vm.selectedListTab);
    });

    const getListItems = jest.spyOn(wrapper.vm, 'getListItems');
    wrapper.vm.initList();
    it('We should try to get the list items on initList', () => {
        expect(getListItems).toHaveBeenCalled();
    });

    wrapper.vm.$data.loading.tabs = false;
    it ('Nav tabs should exists and have as much tabs as list tabs length', () => {
        if (wrapper.vm.$data.currentList.tabs.length > 1) {
            expect(wrapper.findAll('#list-nav li').length).toBe(wrapper.vm.$data.currentList.tabs.length);
        }
    });

    it ('Empty list message should be displayed if displayedItems is empty', () => {
        if (wrapper.vm.displayedItems.length < 1) {
            expect(wrapper.find('#empty-list').exists()).toBeTruthy();
        } else {
            expect(wrapper.find('#empty-list').exists()).toBeFalsy();
        }
    });

    it ('Add action button should exists if current tab has add action', () => {
        if (wrapper.vm.addAction) {
            expect(wrapper.find('#add-action-btn').exists()).toBeTruthy();
        } else {
            expect(wrapper.find('#add-action-btn').exists()).toBeFalsy();
        }
    });

    it ('tabActionsPopover should never include add, edit or preview actions', () => {
        wrapper.vm.tabActionsPopover.forEach(action => {
            expect(action.name).not.toBe('add');
            expect(action.name).not.toBe('edit');
            expect(action.name).not.toBe('preview');
        });
    });

    it ('on click action should return false if no action is defined', () => {
        expect(wrapper.vm.onClickAction(null)).toBeFalsy();
        expect(wrapper.vm.onClickAction(['test'])).toBeFalsy();
    });
});

describe('List view search function', () => {
    const wrapper = mount(List, {
        propsData: {
            defaultType: 'forms',
            defaultLists: ListMock
        },
        localVue,
        store
    });

    const getListItems = jest.spyOn(wrapper.vm, 'getListItems');

    jest.useFakeTimers();
    test('We should try to get the list items on searchItems, and it should have been called with page = 1 and current tab', () => {
        wrapper.vm.searches[wrapper.vm.currentTab.key].search = 'test';
        wrapper.vm.searchItems();
        jest.advanceTimersByTime(501);

        expect(getListItems).toHaveBeenCalledWith(1, wrapper.vm.currentTab.key);
    });
});

describe('List view filter function', () => {
    const wrapper = mount(List, {
        propsData: {
            defaultType: 'forms',
            defaultLists: ListMock
        },
        localVue,
        store
    });

    const getListItems = jest.spyOn(wrapper.vm, 'getListItems');

    const mockFilters = {'programs':[{'key':'recherche','value':'all','options':[{'value':'all','label':'Toutes les catégories'},{'value':'RECHERCHE','label':'RECHERCHE'}]}]};
    wrapper.vm.$data.filters = mockFilters;

    jest.useFakeTimers();
    test('We should try to get the list items when filtering items, and it should have been called with page = 1 and current tab', () => {
        wrapper.vm.onChangeFilter();
        jest.advanceTimersByTime(501);

        expect(getListItems).toHaveBeenCalledWith(1, wrapper.vm.currentTab.key);
    });
});