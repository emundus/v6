<template>
  <!-- The root element of the component -->
  <div class="w-full">
    <!-- The sidebar of the application -->
    <aside id="logo-sidebar"
           class="corner-bottom-left-background fixed left-0 top-0 w-64 h-screen transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
           aria-label="Sidebar">
      <!-- The container for the sidebar content -->
      <div class="h-full pb-4 overflow-y-auto bg-white dark:bg-gray-800">
        <!-- The list of menu items in the sidebar -->
        <ul class="space-y-2 font-large list-none">
          <!-- The back button -->
          <li>
            <span class="flex items-center p-3 rounded-lg group cursor-pointer" @click="backButton">
              <!-- The back button icon -->
              <span class="material-icons-outlined user-select-none text-green-700">arrow_back</span>
              <!-- The back button label -->
              <span class="ms-1 text-green-700">{{ translate('BACK') }}</span>
            </span>
          </li>

          <!-- The list of menu items, dynamically generated from the `menus` data property -->
          <li v-for="(menu, indexMenu) in menus" class="m-3" v-if="menu.published === true">
            <span :id="'Menu-'+indexMenu" @click="handleMenu(indexMenu , menu)"
                  class="flex items-start p-2 cursor-pointer rounded-lg group user-select-none"
                  :class="activeMenu === indexMenu ? 'green-Menubutton' : 'hover:bg-gray-100'"
            >
              <!-- The menu item icon -->
              <i class="material-icons-outlined font-bold" :class="activeMenu === indexMenu ? 'text-green-700' : ''"
                 name="icon-Menu" :id="'icon-'+indexMenu">{{ menu.icon }}</i>
              <!-- The menu item label -->
              <span class="ms-2 font-bold"
                    :class="activeMenu === indexMenu ? 'text-green-700' : ''">{{ translate(menu.label) }}</span>
            </span>
          </li>
        </ul>
      </div>
      <!-- The bottom part of the sidebar -->
      <div class="tchoozy-corner-bottom-left-bakground-mask-image h-2/4	w-full absolute bottom-0 bg-main-500"></div>
    </aside>

    <!-- The main content area of the application -->
    <div class="px-6 sm:ml-40" v-if="activeMenu != null">
      <!-- The title of the active menu -->
      <h1 class="text-2xl pl-1 font-semibold text-green-700 mb-3">
        <!-- The icon of the active menu -->
        <span class="material-icons-outlined scale-150 text-green-700 me-2">
          {{ this.aMenu.icon }}
        </span>
        <!-- The label of the active menu -->
        {{ translate(this.aMenu.label) }}
      </h1>

      <!-- The list of sections in the active menu, dynamically generated from the `subMenus` data property -->
      <div id="accordion-collapse" v-for="(section, indexSection) in subMenus[activeMenu]"
           v-if="subMenus[activeMenu][indexSection].type !== 'Tile'"
           class="flex flex-col justify-between w-full p-5 font-medium rtl:text-right text-black border border-gray-200 rounded-[15px] bg-white mb-3 gap-3 shadow"
           data-accordion-target="#accordion-collapse-body-1" aria-expanded="true"
           aria-controls="accordion-collapse-body-1">

        <!-- The header of the section -->
        <div @click="handleSubMenu(indexSection)" class="flex items-center justify-between cursor-pointer">
          <!-- The title of the section -->
          <div class="flex">
            <h1 id="accordion-collapse-heading-1" class="user-select-none flex flex-row justify-between">
              <!-- The label of the section -->
              <span :id="'Subtile'+indexSection" class="text-2xl user-select-none">{{
                  translate(subMenus[activeMenu][indexSection].label)
                }}</span>
            </h1>
            <!-- The notification icon of the section -->
            <div v-if="subMenus[activeMenu][indexSection].notify === 1 ">
              <div v-if="$data.notifRemain > -1"
                   class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-2 -end-2 ">
                <div v-if="$data.notifRemain >0">{{ $data.notifRemain }}</div>
              </div>
            </div>
          </div>
          <!-- The expand icon of the section wich rotate-->
          <span class="material-icons-outlined scale-150 user-select-none" :id="'SubtitleArrow'+indexSection"
                name="SubtitleArrows"
                :class="subMenuOpen === indexSection ? 'rotate-180' : ''">expand_more</span>
        </div>

        <!-- The content of the section -->
        <div name="SubMenuContent" class="flex flex-col" v-show="subMenuOpen === indexSection">
          <div v-for="(option,indexOption) in subMenus[activeMenu][indexSection].options" :class="[{'flex-wrap w-full sm:w-1/2 md:w-1/2 lg:w-1/2' : option.type === 'mail-config'}]">

            <!-- the title elements -->
            <div class="flex flex-col" v-if="(option.type_field === 'Title') && (option.published === true) && (displayEmail)">
              <h2 v-if="option.published === true">{{ translate(option.label) }}</h2>
              <hr>
            </div>

            <!-- The button elements -->
            <div class="flex flex-col"
                 v-if="(option.type_field === 'Button') && (option.published === true) && (displayEmail !== 0)">
              <button type="button" class="bg-green-600 p-2 border border-gray-200 rounded" @click="handleClickButton(option)">
                <i class="material-icons-outlined scale-150 mr-2" :id="'IconButton-'+indexOption" name="IconsButton"
                   style="color: white">{{ option.icon }}</i>
                <span style="color: white">{{ translate(option.label) }}</span>
              </button>
            </div>

            <!-- The input elements -->
            <div class="flex flex-col"
                 v-if="(option.type_field === 'Input') && (option.published === true) &&(displayEmail !== 0) && (subMenus[activeMenu].name='manage_server_mail')">
              <input :placeholder="option.placeholder"
                     class="w-full p-2 border border-gray-200 rounded hover:bg-gray-300"
                     v-model="option.value">
            </div>

            <!-- The dropdown elements -->
            <div class="flex flex-col"
                 v-if="(option.type_field === 'dropdown') && (option.published === true)  && (displayEmail !== 0) && (subMenus[activeMenu].name='manage_server_mail')">
              <select>
                <option v-for="choice in option.choices">{{ translate(choice.label) }}</option>
              </select>
            </div>

            <!-- The sqldropdown elements -->
            <div class="flex flex-col"
                 v-if="(option.type_field === 'sqldropdown') && (option.published === true)&& (displayEmail !== 0) && (subMenus[activeMenu].name='manage_server_mail')">
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

            <!-- The yesno elements -->
            <div class="flex flex-col"
                 v-if="(option.type_field === 'yesno') && (option.published === true)&&(displayEmail !== 0) && (subMenus[activeMenu].name='manage_server_mail')">
              <div class="flex-row flex items-center">
                <button v-for="( button, indexOfbutton) in option.options" type="button"
                        :id="'BtYN'+indexOption+'_'+indexOfbutton" :name="'YNbuttton'+option.name"
                        :class="['YesNobutton'+button.value ,{'active': option.value ===1} , {'click':option.value === 0}]"
                        class="focus:ring-neutral-50 focus:outline-none  font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"
                        v-model="option.value" @click="clickYN(option,indexOption, indexOfbutton)">
                  {{ translate(button.label) }}
                </button>
              </div>
            </div>

            <!-- the checkox elements -->
            <div class="flex flex-col"
                 v-if="(option.type_field === 'checkbox')&& (option.published === true) && (displayEmail !== 0) && (subMenus[activeMenu].name='manage_server_mail')">
              <div class="flex items-center " v-for="(aChoice,indexChoice) in option.choices">
                <input type="checkbox" :id="'myCheck'+ indexChoice" :value="aChoice.value">
                <label class="mt-2.5 ml-1">{{ translate(aChoice.label) }}</label>
              </div>
            </div>

            <!-- The textarea elements -->
            <div class="flex flex-col"
                 v-if="(option.type_field === 'textarea')&& (option.published === true)&& (displayEmail !== 0) && (subMenus[activeMenu].name='manage_server_mail')">
              <editor-quill :height="'30em'" :text="''" :enable_variables="false" :id="'editor'" :key="0"
                            v-model="form.content"></editor-quill>
            </div>

            <!-- The toggle elements -->
            <div v-if="(option.type_field === 'toggle')&&(option.published === true)" class="inline-flex items-center cursor-pointer">
              <div class="mb-4 flex items-center">
                <div class="em-toggle">
                  <input type="checkbox"
                         true-value="1"
                         false-value="0"
                         class="em-toggle-check"
                         :id="'published'+indexOption"
                         v-model="option.value"
                         @click="toggleButton(option)"
                  />
                  <strong class="b em-toggle-switch"></strong>
                  <strong class="b em-toggle-track"></strong>
                </div>
                <span for="published" class="ml-2">{{translate(option.label_right) }}</span>
              </div>
            </div>

            <!-- The warning message -->
            <div v-if="(option.name === 'mailonline')&&(displayEmail === 0) && (subMenus[activeMenu].name='manage_server_mail')"
                 class="bg-orange-300 rounded flex items-center pb-2">
              <span class="material-icons-outlined scale-150 ml-2 mt-2">warning</span>
              <p class="ml-2 mt-2">{{ translate(subMenus[activeMenu][indexSection].warning) }}</p>
            </div>

            <!-- The info message -->
            <div v-if="(displayEmail === 1)&& (option.type_field === 'info')"
                 class="bg-blue-300 rounded flex items-center pb-2">
              <span class="material-icons-outlined scale-150 ml-2 mt-2">info</span>
              <p class="ml-2 mt-2">{{ translate(option.label) }}</p>
            </div>

            <!-- SubSection elements -->
            <div
                v-if="(option.type ==='subSection') && (option.published === true) "
                name="ComponentsubSections"
                :id="'ComponentsubSection-'+indexOption" style=" ">
              <div @click="handleSubSection(activeMenu,indexSection,indexOption)" class="pb-3 cursor-pointer">
                <span :id="'SubSectionTile'+indexOption" class="em-font-size-16">{{ translate(option.label) }}</span>
                <i class="material-icons-outlined scale-150" :id="'SubSectionArrow'+indexOption" name="SubSectionArrows"
                   style="transform-origin: unset">expand_more</i>
                <div v-if="$data.notifCheck[indexOption]===true && subMenus[activeMenu][indexSection].notify === 1"
                     class="inline-flex w-6 h-6 bg-red-500 border-2 border-white rounded-full -top-2 -end-2 ">
                </div>
              </div>

              <div :key="reloadTheSubSection  " :id="'SubSection-'+indexOption" name="SubSectionContent"
                   v-show=" showTheSubSection(indexSection,indexOption) "
                   class="flex flex-col bg-gray-200 rounded subSection">

                <div v-if="option.helptext !== '' "
                     class="bg-blue-300 rounded flex  flex-col items-center pb-2">
                  <span class="material-icons-outlined scale-150 ml-2 mt-2">info</span>
                  <p class="ml-2 mt-2">{{ translate(option.helptext) }}</p>
                </div>


                <div v-for="(element, indexElement) in option.elements">
                  <div v-if="element.type_field === 'component'">
                    <component
                        :is="element.component"
                        v-bind="element.props"
                        @NeedNotify="value => countNotif(value, indexOption)"
                        :key="keySubContent"
                    ></component>
                  </div>
                </div>

              </div>
            </div>

            <!-- side by side elements -->
            <div v-if="option.type === 'sideBYside' && (displayEmail)" class="grid-container">
              <div v-for="(choice, indexChoice) in option.choices" :key="indexChoice">
                <input type="radio" :id="'choice' + indexChoice" :value="choice.value" v-model="DefaultParamMail">
                <label :for="'choice' + indexChoice">{{ translate(choice.label) }}</label>
              </div>
              <div v-for="(element, indexElement) in option.elements" class="flex flex-col"
                   :key="indexElement">
                <div v-if="element.type_field === 'component'" :class="[{  'bg-green-400/50 rounded' : !DefaultParamMail && element.value === 'custom' } , {  'bg-green-400/50 rounded' : DefaultParamMail && element.value === 'default' }]">
                  <component
                      :is="element.component"
                      v-bind="element.props"
                  ></component>
                </div>
              </div>
            </div>

            <!-- The component elements in is simplest form -->
            <div class="flex flex-col"
                 v-if="(option.type_field === 'component')&& (option.published === true)">
              <component :is="option.component" :key="ComponantReload" v-bind="option.props">
              </component>
            </div>

          </div>
        </div>
      </div>

      <!-- The tile elements -->
      <div class="flex flex-row flex-wrap">
        <div v-for="tile in subMenus[activeMenu]" v-if="tile.type==='Tile' && tile.published === true" class="flex flex-col flex-wrap mr-3"
             :key="tile.id">
          <div
              :style="{'width': '20em', 'height':'14em' ,'margin-bottom':'2em', 'box-shadow':'rgba(255, 255, 255, 0.1) 0px 1px 1px 0px inset, rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px'}"
              class="flex bg-white justify-center items-center rounded" name="tilebutton">
            <button type="button" @click="this.goTo(tile.link, false);"
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
import EditEmailJoomla from "@/components/Settings/FilesTool/EditEmailJoomla.vue";

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
import Swal from "sweetalert2";


export default {
  name: "globalSettings",
  components: {
    EditSettingJoomla,
    EditEmailJoomla,
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
    subSectionOpen: [],
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

    displayEmail: 1,
    keySubContent: 0,
    DefaultParamMail: 0,
    reloadTheSubSection: 0,
    configJoomla: {},
    mailonlineValue: 1,

    form: {
      content: ''
    },
  }),

  created() {
    this.loading = true;
    this.changeCSS();
    this.getParamFromjson();
    //this.getTimeZone(); -- todo in next version
    this.loading = false;
  },
  mounted() {
    this.URLMenu();
  },

  methods: {
    logActiveMenuSubMenus() {
      console.log(this.subMenus[this.activeMenu], "test");
    },

    //visual method -------------
    changeCSS() {
      document.getElementById("header-b").style.display = "none";
      document.getElementById("g-navigation").style.display = "none";
    },

    //get methode -------------
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
      // todo handling of the select of offset
    },

    //direction automatic method -------------
    backButton() {
      window.history.back();
    },
    goTo(url, newTab) {
      if (newTab) {
        window.open(url, '_blank');
      } else {
        window.location.href = url;
      }
    },
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

    // search method -------------
    searchMenu(menu) {
      return this.menus.findIndex(value => value.name === menu);
    },
    searchSubMenu(menu, section) {
      return this.subMenus[menu].findIndex(value => value.name === section);
    },

    // handle method -------------
    handleMenu(index, item) {
      this.toggleMenuButton(index, item);
      this.$data.ComponantReload++;
    },
    handleSubMenu(index) {
      if (this.subMenuOpen == index) {
        this.subMenuOpen = -1;
      } else {
        this.subMenuOpen = index;
      }
    },
    handleSubSection(indexMenu, indexSection, indexoption) {
      let indexOfArray = indexMenu + '-' + indexSection + '-' + indexoption;
      if (this.subSectionOpen[indexOfArray] === undefined || this.subSectionOpen[indexOfArray] === "0") {
        this.subSectionOpen[indexOfArray] = "1";
      } else if (this.subSectionOpen[indexOfArray] === "1") {
        this.subSectionOpen[indexOfArray] = "0";
      }
      this.reloadTheSubSection++;
    },

    // toggle method -------------
    toggleMenuButton(index, item) {
      this.aMenu = item;
      this.activeMenu = index;
    },
    toggleButton(element) {
      /*
      if (element.name === 'custom_config_mail') {
        element.value = !element.value;
        this.CustomParam = element.value ? 1 : 0;

      } else if (element.name === 'mailonline') {
        this.displayEmail = !this.displayEmail;
        element.value = this.displayEmail ? 1 : 0;
        this.$emit('changeMailOnline', element);
        this.goTo('/parametres-globaux?Menu=email_settings&section=manage_server_mail', false);
      } else {
      }
       */
        element.value = !element.value;

    },

    // show method -------------
    showTheSubSection(indexSection, indexoption) {
      let indexOfArray = this.activeMenu + '-' + indexSection + '-' + indexoption;
      return this.subSectionOpen[indexOfArray] === "1";
    },

    // count method -------------
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


    // click method -------------
    clickYN(param, index, indexOfOptions) {
      param.value = indexOfOptions;
      param.value = param.value ? 1 : 0;
      this.YNButtons[index] = indexOfOptions;
    },

    handleClickButton(element){
      if(element.action === "sweetAlert"){
        Swal.fire({
          title: "test principale",
          text: "text ",
        });
      }

    },


    displayElement(element)
    {
      return element.published === true;
    },

  },
  watch: {

  }
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

.subSection {
  padding-left: 1em;
  padding-right: 1em;
  margin-bottom: 1em;
}
.grid-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
</style>
