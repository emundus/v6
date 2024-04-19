<template xmlns="http://www.w3.org/1999/html">
  <div class="em-w-100">
    <aside id="logo-sidebar"
           class="fixed left-0 top-0 w-64 h-screen transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
           aria-label="Sidebar">
      <div class="h-full pb-4 overflow-y-auto bg-white dark:bg-gray-800">
        <ul class="space-y-2 font-large">
          <li>
            <a href="/" class="flex items-center p-2 rounded-lg group">
              <i class="material-icons-outlined" style="user-select: none; color: #008A35">arrow_back</i>
              <span class="ms-1" style="user-select: none; color: #008A35">{{ translate('BACK') }}</span>
            </a>
          </li>
          <li v-for="(item, index) in Menus">
            <a :id="'Menu-'+index" name="bouton-Menu" @click="handleMenuButtonClick(index , item)"
               style="user-select: none" class="flex items-center p-2  rounded-lg  group">
              <i class="material-icons-outlined" name="icon-Menu" :id="'icon-'+index">{{ item.icon }}</i>
              <span class="ms-1">{{ translate(item.label) }}</span>
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <div class="p-4 sm:ml-40" style="user-select: none" v-if="indexMenuClick != null">
      <h1 class="text-2xl font-semibold" style="user-select: none; color: #008A35;">
        <i class="material-icons-outlined scale-150 " style=" color:#008A35; padding-right: 0.5em">
          {{ this.aMenu.icon }}
        </i>
        {{ translate(this.aMenu.label) }}
      </h1>
      <div id="accordion-collapse" v-for="(x, index1) in SubMenus[indexMenuClick]"
           v-if="SubMenus[indexMenuClick][index1].type!=='Tile'"
           class="flex flex-col justify-between w-full p-5 font-medium rtl:text-right text-black border border-gray-200 rounded bg-white mb-3 hover:bg-gray-100 gap-3 "
           data-accordion-target="#accordion-collapse-body-1" aria-expanded="true"
           aria-controls="accordion-collapse-body-1"
           style="box-shadow:0.1em 0.05em 0.05em grey;">
        <div @click="handleSubMenuClick(index1)">
          <h1 id="accordion-collapse-heading-1" class="flex flex-row justify-between cursor-pointer">
            <div class="flex flex-row">
              <span :id="'Subtile'+index1" class="em-font-size-24 ">{{
                  translate(SubMenus[indexMenuClick][index1].label)
                }}</span>
              <div v-if="SubMenus[indexMenuClick][index1].notify === 1 ">
                <div v-if="$data.notifRemain > -1"
                     class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-2 -end-2 ">
                  <div v-if="$data.notifRemain >0">{{ $data.notifRemain }}</div>
                </div>

              </div>
              <div v-if="SubMenus[indexMenuClick][index1].helptext !== '' ">
                <span class="material-icons-outlined scale-150 ml-2 mt-2">info</span>
              </div>
            </div>
            <i class="material-icons-outlined scale-150" :id="'SubtitleArrow'+index1" name="SubtitleArrows"
               style="transform-origin: unset">expand_more</i>
          </h1>
        </div>


        <div :id="'SubMenu-'+index1" name="SubMenuContent" style="display: none" class="flex flex-col ">
          <div v-for="(option,index2) in SubMenus[indexMenuClick][index1].options">
            <div class="flex flex-col" v-if="option.type_field === 'Title'">
              <h2>{{ translate(option.label) }}</h2>
              <hr>
            </div>

            <div v-else-if="option.type ==='subSection'">
              <div @click="handleSubSectionClick(index2)">
                <span :id="'SubSectionTile'+index2" class="em-font-size-16">{{ translate(option.label) }}</span>
                <i class="material-icons-outlined scale-150" :id="'SubSectionArrow'+index2" name="SubSectionArrows"
                   style="transform-origin: unset">expand_more</i>
                <div v-if="$data.notifCheck[index2]===true"
                     class="inline-flex w-6 h-6 bg-red-500 border-2 border-white rounded-full -top-2 -end-2 ">
                </div>
              </div>
              <div :id="'SubSection-'+index2" name="SubSectionContent" style="display: none" class="flex flex-col ">
                <div v-for="(option1,index3) in option.elements">
                  <div class="flex flex-col" v-if="option1.type_field === 'component'">
                    <component :is="option1.component" v-bind="option1.props"
                               @NeedNotify="value =>countNotif(value,index2)"></component>
                  </div>
                </div>
              </div>
            </div>


            <div v-else class="block text-xl ">
              {{ translate(option.label) }}
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'Input'">
              <input :placeholder="option.placeholder" class="w-full p-2 border border-gray-200 rounded"
                     v-model="option.value">
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'dropdown'">
              <select>
                <option v-for="choice in option.choices">{{ translate(choice.label) }}</option>
              </select>
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'sqldropdown'">
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

            <div class="flex flex-col" v-if="option.type_field === 'yesno'">
              <div class="flex-row flex items-center">
                <button type="button" :id="'BtN'+index2" @click="clickYN(false, index2)"
                        :class="{'red-YesNobutton': true, 'active': option.defaultVal === '0'}"
                        class="red-YesNobutton  focus:ring-neutral-50 focus:outline-none  font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
                  Non
                </button>
                <button type="button" :id="'BtY'+index2" @click="clickYN(true, index2)"
                        :class="{'green-YesNobutton': true, 'active': option.defaultVal === '1'}"
                        class="focus:ring-neutral-50 focus:outline-none  font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
                  Oui
                </button>
              </div>
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'checkbox'">
              <div class="flex items-center " v-for="(x,choice) in option.choices">
                <input type="checkbox" :id="'myCheck'+ choice" :value="x.value">
                <label class="mt-2.5 ml-1">{{ translate(x.label) }}</label>
              </div>
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'textarea'">
              <editor-quill :height="'30em'" :text="''" :enable_variables="false" :id="'editor'" :key="0"
                            v-model="form.content"></editor-quill>
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'component'">
              <component :is="option.component" v-bind="option.props"></component>
            </div>

          </div>
        </div>
      </div>

      <div class="flex flex-row flex-wrap">
        <div v-for="tile in SubMenus[indexMenuClick]" v-if="tile.type==='Tile'" class="flex flex-col flex-wrap mr-3"
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
import {forEach} from "lodash";


export default {
  name: "globalSettings",
  components: {
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
    langue: 0,

    saving: false,
    endSaving: false,
    loading: null,

    MenuisClicked: [],
    SubMenuisClicked: [],
    SubSectionisClicked: [],
    indexMenuClick: null,
    aMenu: [],
    Menus: [],
    SubMenus: [],
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
          this.Menus.push(i);
          this.SubMenus.push(i.sections);
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
        this.handleMenuButtonClick(indexMenu  , this.Menus[indexMenu]);
        const indexSubSection = this.searchSubMenu(indexMenu, section);

        if (indexSubSection !== -1) {
          setTimeout(() => {
          this.handleSubMenuClick(indexSubSection);
          }, 10);//wait thant this.handleMenuButtonClick(indexMenu  , this.Menus[indexMenu]) is finished
        }
      } else {
        this.handleMenuButtonClick(0, this.Menus[0]);
      }
    },
    searchMenu(menu) {
      return this.Menus.findIndex(value => value.name === menu);
    },
    searchSubMenu(menu, section) {
      return this.SubMenus[menu].findIndex(value => value.name === section);
    },
    handleMenuButtonClick(index, item) {
      this.resetMenuButtons();
      this.toggleMenuButton(index, item);
    },
    resetMenuButtons() {
      this.MenuisClicked.fill(false);
      this.SubMenuisClicked.fill(false);
      this.resetStyles();
    },
    resetStyles() {
      document.getElementsByName("SubtitleArrows").forEach(element => element.style.rotate = '0deg');
      document.getElementsByName("bouton-Menu").forEach(element => element.classList.remove('green-Menubutton'));
      document.getElementsByName("SubMenuContent").forEach(element => element.style.display = 'none');
      document.getElementsByName("icon-Menu").forEach(element => element.style.color = '#000000');
    },
    toggleMenuButton(index, item) {
      this.aMenu = item;
      this.MenuisClicked[index] = !this.MenuisClicked[index];
      if (this.MenuisClicked[index]) {
        this.indexMenuClick = index;
        document.getElementById('Menu-' + index).classList.add('green-Menubutton');
        document.getElementById('icon-' + index).style.color = '#008A35';
      } else {
        this.indexMenuClick = null;
      }
    },
    handleSubMenuClick(index) {
      this.resetSubMenuButtons(index);
      this.toggleSubMenuButton(index);
    },
    resetSubMenuButtons(index) {
      if (this.SubMenuisClicked[index]) {
        this.SubMenuisClicked.fill(false);
        this.SubMenuisClicked[index] = true;
      } else {
        this.SubMenuisClicked.fill(false);
      }

      this.resetSubMenuStyles();
    },
    resetSubMenuStyles() {
      document.getElementsByName("SubtitleArrows").forEach(element => element.style.rotate = '0deg');
      document.getElementsByName("SubMenuContent").forEach(element => element.style.display = 'none');
    },

    toggleSubMenuButton(index) {
      if(this.SubMenuisClicked[index] === undefined){
        this.SubMenuisClicked[index] = false;
      }
      this.SubMenuisClicked[index] = !this.SubMenuisClicked[index];
      if (this.SubMenuisClicked[index]) {
        document.getElementById('SubtitleArrow' + index).style.rotate = '-180deg';
        document.getElementById('SubMenu-' + index).style.display = 'flex';
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


    handleSubSectionClick(index) {
      this.resetSubSectionButtons(index);
      this.toggleSubSectionButton(index);
    },
    resetSubSectionButtons(index) {
      if (this.SubSectionisClicked[index]) {
        this.SubSectionisClicked.fill(false);
        this.SubSectionisClicked[index] = true;
      } else {
        this.SubSectionisClicked.fill(false);
      }
      this.resetSubSectionStyles();
    },
    toggleSubSectionButton(index) {
      this.SubSectionisClicked[index] = !this.SubSectionisClicked[index];
      if (this.SubSectionisClicked[index]) {
        document.getElementById('SubSectionArrow' + index).style.rotate = '-180deg';
        document.getElementById('SubSection-' + index).style.display = 'flex';
      }
    },
    resetSubSectionStyles() {
      document.getElementsByName("SubSectionArrows").forEach(element => element.style.rotate = '0deg');
      document.getElementsByName("SubSectionContent").forEach(element => element.style.display = 'none');
    },

    clickYN(bool, index) {
      this.YNButtons[index] = bool;
      if (bool) {
        document.getElementById('BtY' + index).classList.add('active');
        document.getElementById('BtN' + index).classList.remove('active');
      } else {
        document.getElementById('BtN' + index).classList.add('active');
        document.getElementById('BtY' + index).classList.remove('active');
      }
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


.green-YesNobutton {
  border: 1px solid #008A35;
  background-color: white;
  color: #008A35;
}

.green-YesNobutton:hover {
  background-color: #008A35;
  color: black;
}

.green-YesNobutton.active {
  background-color: #008A35;
  color: white;
}

.red-YesNobutton {
  border: 1px solid #FF0000;
  background-color: white;
  color: #FF0000;
}

.red-YesNobutton:hover {
  background-color: #FF0000;
  color: white;
}

.red-YesNobutton.active {
  background-color: #FF0000;
  color: white;
}

</style>
