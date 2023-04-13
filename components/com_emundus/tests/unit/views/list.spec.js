import { mount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import List from '../../../src/views/list_v2';
import translate from '../../mocks/mixins/translate';
import store from '../../../src/store/index';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

localStorage.setItem('tchooz_lists/' + document.location.hostname, btoa(JSON.stringify({'forms':{'title':'COM_EMUNDUS_ONBOARD_FORMS','tabs':[{'title':'COM_EMUNDUS_FORM_MY_FORMS','key':'form','controller':'form','getter':'getallform','actions':[{'action':'duplicateform','label':'COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE','controller':'form','name':'duplicate'},{'action':'index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%','label':'COM_EMUNDUS_ONBOARD_MODIFY','controller':'form','type':'redirect','name':'edit'},{'action':'createform','controller':'form','label':'COM_EMUNDUS_ONBOARD_ADD_FORM','name':'add'}],'filters':[]},{'title':'COM_EMUNDUS_FORM_MY_EVAL_FORMS','key':'form_evaluations','controller':'form','getter':'getallgrilleEval','actions':[{'action':'createformeval','label':'COM_EMUNDUS_ONBOARD_ADD_EVAL_FORM','controller':'form','name':'add'},{'action':'/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%&mode=eval','label':'COM_EMUNDUS_ONBOARD_MODIFY','controller':'form','type':'redirect','name':'edit'}],'filters':[]},{'title':'COM_EMUNDUS_FORM_PAGE_MODELS','key':'form_models','controller':'formbuilder','getter':'getallmodels','actions':[{'action':'deleteformmodelfromids','label':'COM_EMUNDUS_ACTIONS_DELETE','controller':'formbuilder','parameters':'&model_ids=%id%','name':'delete'},{'action':'/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%form_id%&mode=models','label':'COM_EMUNDUS_ONBOARD_MODIFY','controller':'form','type':'redirect','name':'edit'}],'filters':[]}]}})));

describe('List view', () => {
    const wrapper = mount(List, {
        propsData: {
            defaultType: 'forms'
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
});