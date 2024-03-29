<template>
  <div class="em-w-100">
    <button v-for="(item, index) in Menus" :id="'Menu-'+index" name="BoutonMenu" :class="{ 'outlined-button': isOutlined, }" @click="handleButtonClick(index)">{{item.label}}</button>
    <div v-if="indexMenu != null" class="submenu-div"> <p>{{SubMenus[indexMenu]}}</p> </div>
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

//import settingsService from '../services/settings';

import SettingParam from '../../data/settings-global-group-params.json';


export default {
  name: "globalSettings",
  components: {
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
    menuHighlight: 0,
    currentTitle: '',
    langue: 0,
    saving: false,
    endSaving: false,
    loading: null,

    isOutlined: true, // Boolean flag to determine if buttons should be outlined
    isClicked: {},
    indexMenu: null,
    Menus : [],
    SubMenus : [],
    SubSubMenus : [],

  }),

  created() {
    this.loading = true;
    this.changeCSS();
    this.getParamFromjson();
    this.loading = false;

  },

  methods: {
    changeCSS() {
      let navbarDisplay = document.getElementById("header-b");
      navbarDisplay.style.display = "none";
    },
    getParamFromjson(){
      return new Promise((resolve) => {
        for(let i of SettingParam){
          this.Menus.push(i);
          this.SubMenus.push(i.sections); // Use spread operator to flatten the array
        }
        for(let j of this.SubMenus)
        {
          for(let k of j)
          {
            this.SubSubMenus.push(k.options);
          }
        }
        resolve();
        console.log(this.Menus)
        console.log(this.SubMenus)
        console.log(this.SubSubMenus)
      });

    },
    handleButtonClick(index) {
      // Toggle the clicked state for the button
      this.isClicked[index] = !this.isClicked[index];

      // Remove 'green-button' class from all buttons
      document.getElementsByName("BoutonMenu").forEach(element => {
        element.classList.remove('green-button');
      });

      if (this.isClicked[index]) {
        // If the button is clicked, add 'green-button' class and set indexMenu
        this.indexMenu = index;
        document.getElementById('Menu-' + index).classList.add('green-button');
      } else {
        // If the button is not clicked, set indexMenu to null
        this.indexMenu = null;
      }
    }

  },
	computed: {
	},
  watch: {
  }
};

</script>

<style scoped>
.outlined-button {
  border: 1px solid #333; /* Border color */
  background-color: transparent; /* Remove background color */
  padding: 8px 16px; /* Add padding for better spacing */
  margin: 4px; /* Add margin for better spacing */
  cursor: pointer; /* Show pointer cursor on hover */
  outline: none; /* Remove default outline */
}

.outlined-button:hover {
  background-color: #00ff00; /* Change background color on hover */
  color: white; /* Change text color to white */

}

.green-button {
  background-color: green; /* Change background color to green when clicked */
  color: white; /* Change text color to white */
}

.submenu-div {
  display: flex;
  flex-direction: column;
  margin-left: 20px;
}
</style>
