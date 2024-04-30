<template>
  <div class="w-full">
    <aside id="logo-sidebar"
           class="corner-bottom-left-background fixed left-0 top-0 w-64 h-screen transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
           aria-label="Sidebar">
      <div class="h-full pb-4 overflow-y-auto bg-white dark:bg-gray-800">
        <ul class="space-y-2 font-large list-none">
          <li>
            <span class="flex items-center p-3 rounded-lg group cursor-pointer" @click="backButton">
              <span class="material-icons-outlined user-select-none text-green-700">arrow_back</span>
              <span class="ms-1 text-green-700">{{ translate('BACK') }}</span>
            </span>
          </li>

          <li v-for="(menu, indexMenu) in menus" class="m-3">
            <span :id="'Menu-'+indexMenu" @click="handleMenu(indexMenu , menu)"
                  class="flex items-start p-2 cursor-pointer rounded-lg group user-select-none"
                  :class="activeMenu === indexMenu ? 'green-Menubutton' : 'hover:bg-gray-100'"
            >
              <i class="material-icons-outlined font-bold" :class="activeMenu === indexMenu ? 'text-green-700' : ''" name="icon-Menu" :id="'icon-'+indexMenu">{{ menu.icon }}</i>
              <span class="ms-2 font-bold" :class="activeMenu === indexMenu ? 'text-green-700' : ''">{{ translate(menu.label) }}</span>
            </span>
          </li>
        </ul>
      </div>
      <div class="tchoozy-corner-bottom-left-bakground-mask-image h-2/4	w-full absolute bottom-0 bg-main-500"></div>
    </aside>

    <div class="px-6 sm:ml-40" v-if="activeMenu != null">
      <h1 class="text-2xl pl-1 font-semibold text-green-700 mb-3">
        <span class="material-icons-outlined scale-150 text-green-700 me-2">
          {{ this.aMenu.icon }}
        </span>
        {{ translate(this.aMenu.label) }}
      </h1>

      <div id="accordion-collapse" v-for="(x, menu) in subMenus[activeMenu]"
           v-if="subMenus[activeMenu][menu].type !== 'Tile'"
           class="flex flex-col justify-between w-full p-5 font-medium rtl:text-right text-black border border-gray-200 rounded-[15px] bg-white mb-3 gap-3 shadow"
           data-accordion-target="#accordion-collapse-body-1" aria-expanded="true"
           aria-controls="accordion-collapse-body-1">

        <div @click="handleSubMenu(menu)" class="flex items-center justify-between cursor-pointer">
          <div class="flex">
            <h1 id="accordion-collapse-heading-1" class="user-select-none flex flex-row justify-between">
              <span :id="'Subtile'+menu" class="text-2xl user-select-none">{{
                  translate(subMenus[activeMenu][menu].label)
                }}</span>
            </h1>
            <div v-if="subMenus[activeMenu][menu].notify === 1 ">
              <div v-if="$data.notifRemain > -1"
                   class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-2 -end-2 ">
                <div v-if="$data.notifRemain >0">{{ $data.notifRemain }}</div>
              </div>
            </div>
          </div>
          <span class="material-icons-outlined scale-150 user-select-none" :id="'SubtitleArrow'+menu"
                name="SubtitleArrows"
                :class="subMenuOpen == menu ? 'rotate-180' : ''">expand_more</span>
        </div>


        <div name="SubMenuContent" class="flex flex-col" v-show="subMenuOpen == menu">
          <div v-if="subMenus[activeMenu][menu].helptext !== '' "
               class="bg-blue-300 rounded flex items-center pb-2">
            <span class="material-icons-outlined scale-150 ml-2 mt-2">info</span>
            <p class="ml-2 mt-2">{{ translate(subMenus[activeMenu][menu].helptext) }}</p>
          </div>
          <div v-for="(option,indexOption) in subMenus[activeMenu][menu].options">
            <div class="flex flex-col" v-if="(option.type_field === 'Title') && (option.published === true)">
              <h2 v-if="option.published === true">{{ translate(option.label) }}</h2>
              <hr>
            </div>

            <div v-else-if="(option.type ==='subSection') && (option.published === true)" name="ComponentsubSections"
                 :id="'ComponentsubSection-'+indexOption" style=" ">
              <div @click="handleSubSection(indexOption)">
                <span :id="'SubSectionTile'+indexOption" class="em-font-size-16">{{ translate(option.label) }}</span>
                <i class="material-icons-outlined scale-150" :id="'SubSectionArrow'+indexOption" name="SubSectionArrows"
                   style="transform-origin: unset">expand_more</i>
                <div v-if="$data.notifCheck[indexOption]===true && subMenus[activeMenu][menu].notify === 1"
                     class="inline-flex w-6 h-6 bg-red-500 border-2 border-white rounded-full -top-2 -end-2 ">
                </div>
              </div>
              <div :id="'SubSection-'+indexOption" name="SubSectionContent" v-if="subSectionOpen == indexOption" class="flex flex-col ">
                <div v-for="(option1,index3) in option.elements">
                  <div class="flex flex-col" v-if="option1.type_field === 'component'">
                    <component :is="option1.component" v-bind="option1.props"
                               @NeedNotify="value =>countNotif(value,indexOption)"></component>
                  </div>
                </div>
              </div>
            </div>


            <div v-else-if="(option.type_field !== 'Button')&&(option.published === true)" name="labelOfElements"
                 class="block text-xl ">
              {{ translate(option.label) }}
            </div>

            <div class="flex flex-col" v-if="(option.type_field === 'Button') && (option.published === true)">
              <button type="button" class="bg-green-600 p-2 border border-gray-200 rounded">
                <i class="material-icons-outlined scale-150 mr-2" :id="'IconButton-'+indexOption" name="IconsButton"
                   style="color: white">{{ option.icon }}</i>
                <span style="color: white">{{ translate(option.label) }}</span>
              </button>
            </div>

            <div class="flex flex-col" v-if="(option.type_field === 'Input') && (option.published === true)">
              <input :placeholder="option.placeholder" class="w-full p-2 border border-gray-200 rounded hover:bg-gray-300"
                     v-model="option.value">
            </div>

            <div class="flex flex-col" v-if="(option.type_field === 'dropdown') && (option.published === true)">
              <select>
                <option v-for="choice in option.choices">{{ translate(choice.label) }}</option>
              </select>
            </div>

            <div class="flex flex-col" v-if="(option.type_field === 'sqldropdown') && (option.published === true)">
              <div v-if="option.name === 'offset'">
                <multiselect
                    :filteredOptions=[]
                    v-model="$data.baseTimeZone"
                    label="label"
                    track-by="value"
                    :options="$data.timeZoneLand"
                    :multiple="false"
                    :taggable="false"
                    select-label=""
                    selected-label=""
                    deselect-label=""
                    :placeholder="translate('COM_EMUNDUS_ONBOARD_BUILDER_BIRTHDAY_FORMAT')"
                    :close-on-select="true"
                    :clear-on-select="false"
                    :searchable="true"
                    :allow-empty="false"
                ></multiselect>

              </div>
            </div>

            <div class="flex flex-col" v-if="(option.type_field === 'yesno') && (option.published === true)">
              <div class="flex-row flex items-center">
                <button v-for="( button, indexOfbutton) in option.options" type="button"
                        :id="'BtYN'+indexOption+'_'+indexOfbutton" :name="'YNbuttton'+option.name"
                        :class="['YesNobutton'+button.value ,{'active': option.value ===1} , {'click':option.value === 0}]"
                        class="focus:ring-neutral-50 focus:outline-none  font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"
                        v-model="option.value" @click="clickYN(option,indexOption, indexOfbutton)">{{ translate(button.label) }}
                </button>
              </div>
            </div>

            <div class="flex flex-col" v-if="(option.type_field === 'checkbox')&& (option.published === true)">
              <div class="flex items-center " v-for="(x,choice) in option.choices">
                <input type="checkbox" :id="'myCheck'+ choice" :value="x.value">
                <label class="mt-2.5 ml-1">{{ translate(x.label) }}</label>
              </div>
            </div>

            <div class="flex flex-col" v-if="(option.type_field === 'textarea')&& (option.published === true)">
              <editor-quill :height="'30em'" :text="''" :enable_variables="false" :id="'editor'" :key="0"
                            v-model="form.content"></editor-quill>
            </div>

            <div class="flex flex-col" v-if="(option.type_field === 'component')&& (option.published === true)">
              <component :is="option.component" :key="ComponantReload" v-bind="option.props"></component>
            </div>

          </div>
        </div>
      </div>

      <div class="flex flex-row flex-wrap">
        <div v-for="tile in subMenus[activeMenu]" v-if="tile.type==='Tile'" class="flex flex-col flex-wrap mr-3"
             :key="tile.id">
          <div
              :style="{'width': '20em', 'height':'14em' ,'margin-bottom':'2em', 'box-shadow':'rgba(255, 255, 255, 0.1) 0px 1px 1px 0px inset, rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px'}"
              class="flex bg-white justify-center items-center rounded" name="tilebutton">
            <button type="button" @click="redirect(tile.link)"
                    class="rounded flex flex-col justify-center items-center">
              <div class="rounded" :style="{ 'background-color': tile.color, 'width': '16em', 'height':'10em' }">
                <i class="material-icons-outlined mt-16 "
                   :style="{'transform': 'scale(7)' ,'margin-top': '4em', 'color':'white'}">{{ tile.icon }}</i>
              </div>
              {{ translate(tile.label) }}
            </button>

          </div>
        </div>
      </div>

    </div>


    <div class="em-page-loader" v-if="loading"></div>
  </div>

</template>

<script>
import EditStatus from '../components/Settings/FilesTool/EditStatus';
import EditTags from '../components/Settings/FilesTool/EditTags';
import TranslationTool from '../components/Settings/TranslationTool/TranslationTool';
import ContentTool from '../components/Settings/Content/ContentTool';
import FilesTool from '../components/Settings/FilesTool/FilesTool';
import StyleTool from '../components/Settings/Style/StyleTool';
import AttachmentStorage from '../components/Settings/AttachmentStorage/AttachmentStorage';

import SettingParam from '../../data/settings-global-group-params.json';
import EditSettingJoomla from "@/components/Settings/FilesTool/EditSettingJoomla.vue";
import Global from "@/components/Settings/Style/General.vue";
import EditTheme from "@/components/Settings/Style/EditTheme.vue";
import EditFooter from "@/components/Settings/Content/EditFooter.vue";
import Translations from "@/components/Settings/TranslationTool/Translations.vue";
import Orphelins from "@/components/Settings/TranslationTool/Orphelins.vue";
import EditArticle from "@/components/Settings/Content/EditArticle.vue";
import EditorQuill from "@/components/editorQuill.vue";
import axios from "axios"
import GlobalLang from "@/components/Settings/TranslationTool/Global.vue";

import Multiselect from 'vue-multiselect';


export default {
  name: "globalSettings",
  components: {
    EditSettingJoomla,
    EditorQuill,
    EditArticle,
    Orphelins,
    Translations,
    EditFooter,
    Global,
    EditTheme,
    StyleTool,
    AttachmentStorage,
    FilesTool,
    ContentTool,
    TranslationTool,
    EditStatus,
    EditTags,
    Multiselect,
    GlobalLang,

  },
  props: {
    actualLanguage: {
      type: String,
      default: 'fr'
    },
    coordinatorAccess: {
      type: Number,
      default: 1
    },
    manyLanguages: {
      type: Number,
      default: 1
    }
  },

  data: () => ({
    saving: false,
    endSaving: false,
    loading: null,

    ComponantReload: 0,
    subMenuOpen: -1,
    subSectionOpen: 0,
    activeMenu: null,

    aMenu: [],
    menus: [],
    subMenus: [],
    YNButtons: [],

    notifRemain: 0,
    notifCheck: [],

    selectedOffset: null,
    timeZoneLand: [],
    baseTimeZone: null,


    form: {
      content: ''
    },
  }),

  created() {
    this.loading = true;
    this.changeCSS();
    this.getParamFromjson();
    this.getTimeZone();
    this.loading = false;
  },
  mounted() {
    this.URLMenu();
  },

  methods: {
    changeCSS() {
      document.getElementById("header-b").style.display = "none";
      document.getElementById("g-navigation").style.display = "none";
    },

    getParamFromjson() {
      return new Promise((resolve) => {
        SettingParam.forEach(i => {
          this.menus.push(i);
          this.subMenus.push(i.sections);
        });
        resolve();
      });

    },
    getTimeZone() {
      return new Promise((resolve) => {
        axios({
          method: "get",
          url: 'index.php?option=com_emundus&controller=settings&task=getTimeZone',
        }).then((rep) => {
          this.baseTimeZone = rep.data.baseData;
          this.timeZoneLand = rep.data.data1;
        });

        resolve(true);
      });
    },
    // todo handling of the select of offset
    URLMenu() {
      const url = new URL(window.location.href);
      if (url.search) {
        const params = new URLSearchParams(url.search);
        const menu = params.get("Menu");
        const section = params.get("section");
        const indexMenu = this.searchMenu(menu)
        this.handleMenu(indexMenu, this.menus[indexMenu]);
        const indexSubSection = this.searchSubMenu(indexMenu, section);

        if (indexSubSection !== -1) {
          setTimeout(() => {
            this.handleSubMenu(indexSubSection);
          }, 100);//wait thant this.handleMenu(indexMenu  , this.menus[indexMenu]) is finished
        }
      } else {
        this.handleMenu(0, this.menus[0]);
      }
    },
    searchMenu(menu) {
      return this.menus.findIndex(value => value.name === menu);
    },
    searchSubMenu(menu, section) {
      return this.subMenus[menu].findIndex(value => value.name === section);
    },
    handleMenu(index, item) {
      this.toggleMenuButton(index, item);
      this.$data.ComponantReload++;
    },
    toggleMenuButton(index, item) {
      this.aMenu = item;
      this.activeMenu = index;
    },
    handleSubMenu(index) {
      if(this.subMenuOpen == index) {
        this.subMenuOpen = -1;
      } else {
        this.subMenuOpen = index;
      }
    },
    countNotif(notif, index) {
      if (notif === true) {
        this.notifRemain += 1;
        this.notifCheck[index] = true;
      } else {
        if (this.notifCheck[index] === true) {
          this.notifRemain -= 1;
          this.notifCheck[index] = false;
        }
      }
      if (this.notifRemain === 0) {
        this.notifRemain = -1;
      }
    },


    handleSubSection(index) {
      if(this.subSectionOpen == index) {
        this.subSectionOpen = -1;
      } else {
        this.subSectionOpen = index;
      }
    },


      clickYN(param, index, indexOfOptions) {
        param.value = indexOfOptions;
        param.value = param.value ? 1 : 0;
        this.YNButtons[index] = indexOfOptions;
    },

    backButton() {
      window.history.back();
    },

    redirect(link) {
      /*
      [https://google.com] in the json link to the exterior
      [link] in the json link from the base to the internal
      emails -> localhost/emails
       */
      window.location.href = link;
    },

  },

  computed: {},
  watch: {}
};

</script>

<style scoped>
.green-Menubutton {
  weight: bold;
  background-color: #008A351A; /* Change background color to green when clicked */
  background-color-opacity: 0.1;
  color: #008A35; /* Change text color to green */
}


.YesNobutton1 {
  border: 1px solid #008A35;
  background-color: white;
  color: #008A35;
}

.YesNobutton1:hover {
  background-color: #008A35;
  color: black;
}

.YesNobutton1.active {
  background-color: #008A35;
  color: white;
}

.YesNobutton0 {
  border: 1px solid #FF0000;
  background-color: white;
  color: #FF0000;
}

.YesNobutton0:hover {
  background-color: #FF0000;
  color: white;
}

.YesNobutton0.click {
  background-color: #FF0000;
  color: white;
}


</style>
