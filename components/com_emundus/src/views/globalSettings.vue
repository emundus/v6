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
        <i class="material-icons-outlined" style="scale: 1.5; color:#008A35;  padding-right: 0.5em">{{this.aMenu.icon }}</i>
        {{ this.aMenu.label }}
      </h1>
      <div id="accordion-collapse" v-for="(x, index1) in SubMenus[indexMenuClick]"
           v-if="SubMenus[indexMenuClick][index1].type!=='Tile'"

           class="flex flex-col justify-between w-full p-5 font-medium rtl:text-right text-black border border-gray-200 rounded bg-white mb-3 hover:bg-gray-100 gap-3 "
           data-accordion-target="#accordion-collapse-body-1" aria-expanded="true"
           aria-controls="accordion-collapse-body-1"
           style="box-shadow:0.1em 0.05em 0.05em grey;">
        <div @click="handleSubMenuClick(index1)">
          <h2 id="accordion-collapse-heading-1" class="flex flex-row justify-between">
            <span :id="'Subtile'+index1" class="em-font-size-24 ">{{ SubMenus[indexMenuClick][index1].label }}</span>
            <span class="material-icons-outlined scale-150" :id="'SubtitleArrow'+index1" name="SubtitleArrows">expand_more</span>
          </h2>
        </div>


        <div :id="'SubMenu-'+index1" name="SubMenuContent" style="display: none" class="flex flex-col ">
          <div  v-for="option in SubMenus[indexMenuClick][index1].options">
            <label class="block text em-font-size-18">{{option.label}}</label>
            <div class="flex flex-col" v-if="option.type === 'Input'">
              <input :placeholder="option.placeholder" class="w-full p-2 border border-gray-200 rounded"
                     v-model="option.value">
            </div>
            <div class="flex flex-col" v-if="option.type === 'sqldropdown'">
              <p>dropDown</p>
            </div>
            <div class="flex flex-col" v-if="option.type === 'textarea'">
              <editor-quill :height="'30em'" :text="''" :enable_variables="false" :id="'editor'" :key="0" v-model="form.content" ></editor-quill>
            </div>
            <div class="flex flex-col" v-if="option.type === 'component'">
              <component :is="option.component" v-bind="option.props"></component>
            </div>
            <div class="flex flex-col" v-if="option.type === 'dropdown'">
              <select><option v-for="choice in option.choices" >{{choice.label}}</option></select>
            </div>
            <div class="flex flex-col" v-if="option.type === 'checkbox'">
              <div v-for="choice in option.choices">
                <input type="checkbox" name="'myCheck'" :value="choice.value" @change="test()">
                <label>{{ choice.label }}</label>
              </div>
            </div>
          </div>


        </div>
      </div>
      <div v-for="(item, index1) in SubMenus[indexMenuClick]" v-if="SubMenus[indexMenuClick][index1].type==='Tile'"
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
      console.log(item);
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
      document.getElementsByName("bouton-Menu").forEach(element => element.classList.remove('green-button'));
      document.getElementsByName("SubMenuContent").forEach(element => element.style.display = 'none');
      document.getElementsByName("icon-Menu").forEach(element => element.style.color = '#000000');
    },

    toggleMenuButton(index, item) {
      this.aMenu = item;
      this.MenuisClicked[index] = !this.MenuisClicked[index];
      if (this.MenuisClicked[index]) {
        this.indexMenuClick = index;
        document.getElementById('Menu-' + index).classList.add('green-button');
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
    test(){
      console.log(document.getElementById("'myCheck'+choice.id"))  ;
      console.log(document.getElementById("myCheck2"))  ;
      console.log(document.getElementById("myCheck3"))  ;

    }
  },
  computed: {},
  watch: {}
};

</script>

<style scoped>
.green-button {
  weight: bold;
  background-color: #008A351A; /* Change background color to green when clicked */
  background-color-opacity: 0.1;
  color: #008A35; /* Change text color to green */
}
</style>
