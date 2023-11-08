import {mount, createLocalVue} from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import List from '../../../src/views/list.vue';
import translate from '../../mocks/mixins/translate';
import store from '../../../src/store/index';
import VModal from 'vue-js-modal';
import {VPopover} from 'v-tooltip';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);
localVue.component('v-popover', VPopover);


const ListMock = btoa(JSON.stringify({
    'forms': {
        'title': 'COM_EMUNDUS_ONBOARD_FORMS',
        'tabs': [{
            'title': 'COM_EMUNDUS_FORM_MY_FORMS',
            'key': 'form',
            'controller': 'form',
            'getter': 'getallform',
            'actions': [{
                'action': 'duplicateform',
                'label': 'COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE',
                'controller': 'form',
                'name': 'duplicate'
            }, {
                'action': 'index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%',
                'label': 'COM_EMUNDUS_ONBOARD_MODIFY',
                'controller': 'form',
                'type': 'redirect',
                'name': 'edit'
            }, {'action': 'createform', 'controller': 'form', 'label': 'COM_EMUNDUS_ONBOARD_ADD_FORM', 'name': 'add'}],
            'filters': []
        }, {
            'title': 'COM_EMUNDUS_FORM_MY_EVAL_FORMS',
            'key': 'form_evaluations',
            'controller': 'form',
            'getter': 'getallgrilleEval',
            'actions': [{
                'action': 'createformeval',
                'label': 'COM_EMUNDUS_ONBOARD_ADD_EVAL_FORM',
                'controller': 'form',
                'name': 'add'
            }, {
                'action': '/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%&mode=eval',
                'label': 'COM_EMUNDUS_ONBOARD_MODIFY',
                'controller': 'form',
                'type': 'redirect',
                'name': 'edit'
            }],
            'filters': []
        }, {
            'title': 'COM_EMUNDUS_FORM_PAGE_MODELS',
            'key': 'form_models',
            'controller': 'formbuilder',
            'getter': 'getallmodels',
            'actions': [{
                'action': 'deleteformmodelfromids',
                'label': 'COM_EMUNDUS_ACTIONS_DELETE',
                'controller': 'formbuilder',
                'parameters': '&model_ids=%id%',
                'name': 'delete'
            }, {
                'action': '/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%form_id%&mode=models',
                'label': 'COM_EMUNDUS_ONBOARD_MODIFY',
                'controller': 'form',
                'type': 'redirect',
                'name': 'edit'
            }],
            'filters': []
        }]
    }
}));

describe('List view', () => {
    const wrapper = mount(List, {
        propsData: {
            defaultType: 'forms',
            defaultLists: ListMock
        },
        localVue,
        store
    });

    it('List wrapper should exist', () => {
        expect(wrapper.find('#onboarding_list').exists()).toBeTruthy();
    });

    it('Current displayed tab should be equal to selected list tab in navigation', () => {
        expect(wrapper.vm.currentTab.key).toBe(wrapper.vm.selectedListTab);
    });

    const getListItems = jest.spyOn(wrapper.vm, 'getListItems');
    wrapper.vm.initList();
    it('We should try to get the list items on initList', () => {
        expect(getListItems).toHaveBeenCalled();
    });

    wrapper.vm.$data.loading.tabs = false;
    it('Nav tabs should exists and have as much tabs as list tabs length', () => {
        if (wrapper.vm.$data.currentList.tabs.length > 1) {
            expect(wrapper.findAll('#list-nav li').length).toBe(wrapper.vm.$data.currentList.tabs.length);
        }
    });

    it('Empty list message should be displayed if displayedItems is empty', () => {
        if (wrapper.vm.displayedItems.length < 1) {
            expect(wrapper.find('#empty-list').exists()).toBeTruthy();
        } else {
            expect(wrapper.find('#empty-list').exists()).toBeFalsy();
        }
    });

    it('Add action button should exists if current tab has add action', () => {
        if (wrapper.vm.addAction) {
            expect(wrapper.find('#add-action-btn').exists()).toBeTruthy();
        } else {
            expect(wrapper.find('#add-action-btn').exists()).toBeFalsy();
        }
    });

    it('tabActionsPopover should never include add, edit or preview actions', () => {
        wrapper.vm.tabActionsPopover.forEach(action => {
            expect(action.name).not.toBe('add');
            expect(action.name).not.toBe('edit');
            expect(action.name).not.toBe('preview');
        });
    });

    it('on click action should return false if no action is defined', () => {
        expect(wrapper.vm.onClickAction(null)).toBeFalsy();
        expect(wrapper.vm.onClickAction(['test'])).toBeFalsy();
    });

    it('select a tab that does not exist should return false', () => {
        expect(wrapper.vm.onSelectTab('test')).toBeFalsy();
    });

    it('select a tab that exists should return true and store it in session storage', () => {
        expect(wrapper.vm.onSelectTab('form_models')).toBeTruthy();
        expect(sessionStorage.getItem('tchooz_selected_tab/' + document.location.hostname)).toBe('form_models');
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

    const mockFilters = {
        'programs': [{
            'key': 'recherche',
            'value': 'all',
            'options': [{'value': 'all', 'label': 'Toutes les catégories'}, {
                'value': 'RECHERCHE',
                'label': 'RECHERCHE'
            }]
        }]
    };
    wrapper.vm.$data.filters = mockFilters;

    jest.useFakeTimers();
    test('We should try to get the list items when filtering items, and it should have been called with page = 1 and current tab', () => {
        wrapper.vm.onChangeFilter();
        jest.advanceTimersByTime(501);

        expect(getListItems).toHaveBeenCalledWith(1, wrapper.vm.currentTab.key);
    });
});

describe('List of campaigns', () => {
    let listCampaigns = btoa(JSON.stringify({
        'campaigns': {
            'title': 'Campagnes', 'tabs': [{
                'title': 'Campagnes',
                'key': 'campaign',
                'controller': 'campaign',
                'getter': 'getallcampaign',
                'actions': [{
                    'action': 'index.php?option=com_emundus&view=campaigns&layout=add',
                    'label': 'Créer une campagne',
                    'controller': 'campaign',
                    'name': 'add',
                    'type': 'redirect'
                }, {
                    'action': 'duplicatecampaign',
                    'label': 'Dupliquer',
                    'controller': 'campaign',
                    'name': 'duplicate'
                }, {
                    'action': 'index.php?option=com_emundus&view=campaigns&layout=addnextcampaign&cid=%id%',
                    'label': 'Modifier',
                    'controller': 'campaign',
                    'type': 'redirect',
                    'name': 'edit'
                }, {
                    'action': 'deletecampaign',
                    'label': 'Supprimer',
                    'controller': 'campaign',
                    'name': 'delete',
                    'confirm': 'Attention ! En supprimant une campagne, vous allez aussi effacer les dossiers de cette campagne',
                    'showon': {'key': 'nb_files', 'operator': '<', 'value': '1'}
                }, {
                    'action': 'unpublishcampaign',
                    'label': 'Dépublier',
                    'controller': 'campaign',
                    'name': 'unpublish',
                    'showon': {'key': 'published', 'operator': '=', 'value': '1'}
                }, {
                    'action': 'publishcampaign',
                    'label': 'Publier',
                    'controller': 'campaign',
                    'name': 'publish',
                    'showon': {'key': 'published', 'operator': '=', 'value': '0'}
                }, {
                    'action': 'pincampaign',
                    'label': 'COM_EMUNDUS_ONBOARD_ACTION_PIN_CAMPAIGN',
                    'controller': 'campaign',
                    'name': 'pin',
                    'icon': 'push_pin',
                    'iconOutlined': true,
                    'showon': {'key': 'pinned', 'operator': '!=', 'value': '1'}
                }, {
                    'action': 'unpincampaign',
                    'label': 'COM_EMUNDUS_ONBOARD_ACTION_UNPIN_CAMPAIGN',
                    'controller': 'campaign',
                    'name': 'unpin',
                    'icon': 'push_pin',
                    'iconOutlined': false,
                    'showon': {'key': 'pinned', 'operator': '=', 'value': '1'}
                }],
                'filters': [{
                    'label': 'Tout',
                    'getter': '',
                    'controller': 'campaigns',
                    'key': 'filter',
                    'values': [{
                        'label': 'COM_EMUNDUS_ONBOARD_FILTER_ALL',
                        'value': 'all'
                    }, {
                        'label': 'COM_EMUNDUS_CAMPAIGN_YET_TO_COME',
                        'value': 'yettocome'
                    }, {
                        'label': 'COM_EMUNDUS_ONBOARD_FILTER_OPEN',
                        'value': 'ongoing'
                    }, {
                        'label': 'COM_EMUNDUS_ONBOARD_FILTER_CLOSE',
                        'value': 'Terminated'
                    }, {
                        'label': 'COM_EMUNDUS_ONBOARD_FILTER_PUBLISH',
                        'value': 'Publish'
                    }, {'label': 'COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH', 'value': 'Unpublish'}],
                    'default': 'Publish'
                }, {
                    'label': 'Tous les programmes',
                    'getter': 'getallprogramforfilter',
                    'controller': 'programme',
                    'key': 'program',
                    'values': null
                }]
            }, {
                'title': 'Programmes',
                'key': 'programs',
                'controller': 'programme',
                'getter': 'getallprogram',
                'actions': [{
                    'action': 'index.php?option=com_fabrik&view=form&formid=108',
                    'controller': 'programme',
                    'label': 'Ajouter un programme',
                    'name': 'add',
                    'type': 'redirect'
                }, {
                    'action': 'index.php?option=com_fabrik&view=form&formid=108&rowid=%id%',
                    'label': 'Modifier',
                    'controller': 'programme',
                    'type': 'redirect',
                    'name': 'edit'
                }],
                'filters': [{
                    'label': 'Toutes les catégories',
                    'getter': 'getprogramcategories',
                    'controller': 'programme',
                    'key': 'recherche',
                    'values': null
                }]
            }]
        }
    }));

    const wrapper = mount(List, {
        propsData: {
            defaultType: 'campaigns',
            defaultLists: listCampaigns
        },
        localVue,
        store
    });
    wrapper.vm.$data.params.shortlang = 'fr';
    wrapper.vm.$data.loading.lists = false;
    wrapper.vm.$data.loading.tabs = false;

    it('List wrapper should exist', () => {
        expect(wrapper.find('#onboarding_list').exists()).toBeTruthy();
    });

    let campaignsData = {
        'status': true, 'msg': 'CAMPAIGNS_RETRIEVED', 'data': {
            'datas': [{
                'id': '10',
                'date_time': '2023-09-13 06:56:24',
                'user': null,
                'label': {'fr': 'Campagne test unitaire', 'en': 'Campagne test unitaire'},
                'description': 'Lorem ipsum',
                'short_description': 'Lorem ipsum',
                'start_date': '2023-09-12 06:56:24',
                'end_date': '2024-09-13 06:56:24',
                'profile_id': '9',
                'training': 'programmet-65015d180451b',
                'year': '2022-2023',
                'published': '1',
                'eval_end_date': null,
                'admission_start_date': null,
                'admission_end_date': null,
                'is_limited': '0',
                'limit': null,
                'limit_status': null,
                'pinned': null,
                'eval_start_date': null,
                'nb_files': '1',
                'program_label': 'Programme Test Unitaire',
                'program_id': '10',
                'published_prog': '1',
                'additional_columns': [{
                    'key': 'Date de début',
                    'value': '12/09/2023 06h56',
                    'classes': '',
                    'display': 'table'
                }, {
                    'key': 'Date de fin',
                    'value': '13/09/2024 06h56',
                    'classes': '',
                    'display': 'table'
                }, {
                    'key': 'État',
                    'type': 'tags',
                    'values': [{
                        'key': 'État',
                        'value': 'Publié',
                        'classes': 'label label-lightgreen em-p-5-12 em-font-weight-600'
                    }, {
                        'key': 'COM_EMUNDUS_ONBOARD_TIME_STATE',
                        'value': 'En cours',
                        'classes': 'label label-default em-p-5-12 em-font-weight-600'
                    }],
                    'display': 'table'
                }, {
                    'key': 'Nombre de dossiers',
                    'value': '<a target=\'_blank\' href=\'/index.php?option=com_emundus&controller=campaign&task=gotocampaign&campaign_id=10\' style=\'line-height: unset;font-size: unset;\'>1</a>',
                    'classes': 'go-to-campaign-link',
                    'display': 'table'
                }, {
                    'value': 'du 12/09/2023 06h56 au 13/09/2024 06h56',
                    'classes': 'em-font-size-14 em-neutral-700-color',
                    'display': 'blocs'
                }, {
                    'type': 'tags',
                    'key': 'État',
                    'values': [{
                        'key': 'État',
                        'value': 'Publié',
                        'classes': 'label label-lightgreen em-p-5-12 em-font-weight-600'
                    }, {
                        'key': 'COM_EMUNDUS_ONBOARD_TIME_STATE',
                        'value': 'En cours',
                        'classes': 'label label-default em-p-5-12 em-font-weight-600'
                    }, {
                        'key': 'dossiers',
                        'value': '<a target=\'_blank\' href=\'/index.php?option=com_emundus&controller=campaign&task=gotocampaign&campaign_id=10\' style=\'line-height: unset;font-size: unset;\'>1 dossier</a>',
                        'classes': 'label label-default em-p-5-12 em-font-weight-600 go-to-campaign-link'
                    }],
                    'classes': 'em-mt-8 em-mb-8',
                    'display': 'blocs'
                }]
            }], 'count': 8
        }, 'allow_pinned_campaigns': '1'
    };

    wrapper.vm.$data.items.campaign = campaignsData.data.datas;
    wrapper.vm.$data.loading.items = false;

    it('#list-items should exist', () => {
        expect(wrapper.find('#list-items').exists()).toBeTruthy();
    });

    it('List should have 1 items', () => {
        expect(wrapper.findAll('.table-row').length).toBe(campaignsData.data.datas.length);
    });

    it('Campaigns should have a link to campaign', () => {
        expect(wrapper.find('.table-row .go-to-campaign-link a').attributes('href')).toBe('/index.php?option=com_emundus&controller=campaign&task=gotocampaign&campaign_id=10');
    });
});