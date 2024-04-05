<template>
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
              <span class="ms-1">{{ item.label }}</span>
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <div class="p-4 sm:ml-40" style="user-select: none" v-if="indexMenuClick != null">
      <h1 class="text-2xl font-semibold" style="user-select: none; color: #008A35;">
        <i class="material-icons-outlined" style="scale: 1.5; color:#008A35;  padding-right: 0.5em">{{
            this.aMenu.icon
          }}</i>
        {{ this.aMenu.label }}
      </h1>
      <div id="accordion-collapse" v-for="(x, index1) in SubMenus[indexMenuClick]"
           v-if="SubMenus[indexMenuClick][index1].type!=='Tile'"

           class="flex flex-col justify-between w-full p-5 font-medium rtl:text-right text-black border border-gray-200 rounded bg-white mb-3 hover:bg-gray-100 gap-3 "
           data-accordion-target="#accordion-collapse-body-1" aria-expanded="true"
           aria-controls="accordion-collapse-body-1"
           style="box-shadow:0.1em 0.05em 0.05em grey;">
        <div @click="handleSubMenuClick(index1)">
          <h1 id="accordion-collapse-heading-1" class="flex flex-row justify-between">
            <span :id="'Subtile'+index1" class="em-font-size-24 ">{{ SubMenus[indexMenuClick][index1].label }}</span>
            <span class="material-icons-outlined scale-150" :id="'SubtitleArrow'+index1" name="SubtitleArrows">expand_more</span>
          </h1>
        </div>


        <div :id="'SubMenu-'+index1" name="SubMenuContent" style="display: none" class="flex flex-col ">
          <div v-for="(option,index2) in SubMenus[indexMenuClick][index1].options">
            <div class="flex flex-col" v-if="option.type_field === 'Title'">
              <h2>{{ option.label }}</h2>
              <hr>
            </div>
            <div v-else class="block text-xl ">
              {{ option.label }}
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'Input'">
              <input :placeholder="option.placeholder" class="w-full p-2 border border-gray-200 rounded"
                     v-model="option.value">
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'dropdown'">
              <select>
                <option v-for="choice in option.choices">{{ choice.label }}</option>
              </select>
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'sqldropdown'">
              <p>dropDown</p>
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'yesno'">
              <div class="flex-row flex items-center">
                <button type="button" :id="'BtN'+index2"  @click="clickYN(false, index2)" :class="{'red-YesNobutton': true, 'active': option.defaultVal === '0'}"  class="red-YesNobutton  focus:ring-neutral-50 focus:outline-none  font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Non</button>
                <button type="button" :id="'BtY'+index2"  @click="clickYN(true, index2)" :class="{'green-YesNobutton': true, 'active': option.defaultVal === '1'}" class="focus:ring-neutral-50 focus:outline-none  font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Oui</button>
              </div>
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'checkbox'">
              <div class="flex items-center " v-for="(x,choice) in option.choices">
                <input type="checkbox" :id="'myCheck'+ choice" :value="x.value">
                <label class="mt-2.5 ml-1">{{ x.label }}</label>
              </div>
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'textarea'">
              <editor-quill :height="'30em'" :text="''" :enable_variables="false" :id="'editor'" :key="0" v-model="form.content"></editor-quill>
            </div>

            <div class="flex flex-col" v-if="option.type_field === 'component'">
              <component :is="option.component" v-bind="option.props"></component>
            </div>

          </div>
        </div>
      </div>
      <div v-for="(item, index1) in SubMenus[indexMenuClick]"
           v-if="SubMenus[indexMenuClick][index1].type_field==='Tile'"
           class="flex items-center mb-3"><p>Tile for: {{ SubMenus[indexMenuClick][index1].label }} is comming soon</p>
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
import EditFooter from "@/components/Settings/Content/EditFooter.vue";
import Translations from "@/components/Settings/TranslationTool/Translations.vue";
import Orphelins from "@/components/Settings/TranslationTool/Orphelins.vue";
import EditArticle from "@/components/Settings/Content/EditArticle.vue";
import EditorQuill from "@/components/editorQuill.vue";


export default {
  name: "globalSettings",
  components: {
    EditorQuill,
    EditArticle,
    Orphelins,
    Translations,
    EditFooter,
    Global,
    StyleTool,
    AttachmentStorage,
    FilesTool,
    ContentTool,
    TranslationTool,
    EditStatus,
    EditTags
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
    indexMenuClick: null,
    aMenu: [],
    Menus: [],
    SubMenus: [],
    YNButtons: [],

    form: {
      content: ''
    },

  }),

  created() {
    this.loading = true;
    this.changeCSS();
    this.getParamFromjson();
    this.loading = false;
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
      this.SubMenuisClicked[index] = !this.SubMenuisClicked[index];
      if (this.SubMenuisClicked[index]) {
        document.getElementById('SubtitleArrow' + index).style.rotate = '-180deg';
        document.getElementById('SubMenu-' + index).style.display = 'flex';
      }
    },

    clickYN(bool, index){
      this.YNButtons[index] = bool;
      if (bool){
        document.getElementById('BtY'+index).classList.add('active');
        document.getElementById('BtN'+index).classList.remove('active');
      } else {
        document.getElementById('BtN'+index).classList.add('active');
        document.getElementById('BtY'+index).classList.remove('active');
      }
    }
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


.green-YesNobutton{
  border: 1px solid #008A35;
  background-color: white;
  color: #008A35;
}

.green-YesNobutton:hover {
  background-color: #008A35;
  color: white;
}

.green-YesNobutton.active{
  background-color: #008A35;
  color: white;
}

.red-YesNobutton{
  border: 1px solid #FF0000;
  background-color: white;
  color: #FF0000;
}

.red-YesNobutton:hover {
  background-color: #FF0000;
  color: white;
}

.red-YesNobutton.active{
  background-color: #FF0000;
  color: white;
}

</style>
