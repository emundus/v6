<template>
  <div>
    <div class="container-evaluation">
      <div class="w-form">
        <h2 class="menuH3">Gestion du menu</h2>
        <div class="divH4">
          <h4 class="leftItem">Nom du menu</h4>
          <h4 class="secondtH4">Type du menu</h4>
        </div>
        <div class="divFlex">
          <!-- menuTitle, menutype | title, type, link, parent_id, component_id, alias -->
          <input placeholder="Nom du menu" class="leftItem" v-model="menutitle" />
          <input placeholder="Nom du menu" class="rightItem" v-model="menu.menutype" disabled />
        </div>
        <div class="container-evaluation w-clearfix">
          <button v-show="isMenuChange" @click="createMenu()" class="buttonMenuItems" type="button">
            <em class="far fa-edit"></em>
            Mettre à jour le menu
          </button>
        </div>
      </div>
      <div class="icon-title"></div>
    </div>
    <div class="divider"></div>
    <div class="container-evaluation">
      <div class="toggleH2">
        <h2 class="menuH3 gestionDesItems">Gestion des items</h2>
        <h4 style="margin-top: 0.65%" class="secondtH4">Publié</h4>
        <div class="toggleMenu rightItem">
          <input type="checkbox" class="check" v-model="itemPublished" />
          <strong class="b switch"></strong>
          <strong class="b track"></strong>
        </div>
      </div>
      <div class="divH4">
        <h4 class="leftItem">Titre de l'item</h4>
        <h4 class="secondtH4">Alias de l'item (automatique par défaut)</h4>
      </div>
      <div class="rightItem bubble">
        <div id="bubbleInfo" class="bubble-text">
          <p>Cet alias existe déjà</p>
        </div>
      </div>
      <div class="divFlex">
        <select @change="menuItemSelected" class="dropdown-toggle w-select inputSelect">
          <option v-for="(item, index) in this.menuItems" :key="index">
            {{ item.title == "" ? "Nouvel Item" : item.title }}
          </option>
        </select>
        <input placeholder="Nom de l'item" class="leftItem inputSelect" v-model="itemTitle" />
        <input placeholder="Alias de l'item" class="rightItem" v-model="itemAlias" />
      </div>
      <div class="divH4">
        <h4 class="leftItem">Type de l'item</h4>
        <h4 class="secondtH4">Lien de l'item</h4>
      </div>
      <div class="divFlex10">
        <input placeholder="Type de l'item" class="leftItem" v-model="itemType" />
        <input placeholder="Lien de l'item" class="rightItem" v-model="itemLink" />
      </div>
      <div class="divH4">
        <h4 class="leftItem">Parent de l'item</h4>
        <h4 class="secondtH4">ID du composant</h4>
      </div>
      <div class="divFlex10">
        <input placeholder="Parent de l'item" class="leftItem" v-model="itemParent_id" />
        <input placeholder="Composant de l'item" class="rightItem" v-model="itemComponent_id" />
      </div>
      <button
        @click="createItem()"
        v-show="isNew"
        class="buttonMenuItems addMenuItems"
        type="button"
      >
        <em class="fas fa-plus-circle"></em>
        Ajouter un item
      </button>
      <button
        @click="modifyItem()"
        v-show="!isNew && isItemChange"
        class="buttonMenuItems updateMenuItems"
        type="button"
      >
        <em class="far fa-edit"></em>
        Mettre à jour l'item
      </button>

      <button
        @click="removeItem()"
        v-show="!isNew"
        class="buttonMenuItems removeMenuItems"
        type="button"
      >
        <em class="far fa-trash-alt"></em>
        Supprimer l'item
      </button>
      <div class="icon-title"></div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { required } from "vuelidate/lib/validators";

export default {
  name: "addMenu",

  props: {
    profileId: Number
  },

  data() {
    return {
      tokenMenu: 0,

      isNew: true,
      isItemChange: false,
      isMenuChange: false,

      allAliases: [],

      menutitle: "",
      itemTitle: "",
      itemAlias: "",
      itemType: "",
      itemLink: "",
      itemParent_id: "",
      itemComponent_id: "",
      itemId: "",
      itemPublished: 1,

      currentItemId: 0,

      menu: {
        menuTitle: "",
        menutype: "menu-profile",
        title: "",
        alias: "",
        type: "component",
        link: "index.php?option=com_fabrik&view=form&formid=287",
        parent_id: 1,
        component_id: 10041,
        published: "",
        id: 0
      },

      itemToChange: {
        title: "",
        alias: "",
        type: "",
        link: "",
        parent_id: "",
        component_id: "",
        published: ""
      },

      menuItems: [],
      menuItems0: {
        title: "",
        alias: "",
        type: "component",
        link: "index.php?option=com_fabrik&view=form&formid=287",
        parent_id: 1,
        component_id: 10041,
        itemid: -1,
        published: 1
      }
    };
  },

  validations: {
    menu: {
      menuTitle: { required },
      title: { required }
    }
  },

  methods: {
    createMenu() {
      let newMenu = {
        title: this.menutitle,
        menutype: this.menu.menutype,
        description: "",
        client_id: "0"
      };
      axios({
        method: "post",
        url: `/vanilla/funnel_candidate/administrator/index.php?option=com_menus&layout=edit&id=${this.menu.id}`,
        data: qs.stringify({
          jform: newMenu,
          task: "menu.apply",
          [this.tokenMenu]: 1
        })
      })
        .then(() => {
          this.getToken();
        })
        .catch(e => {
          console.log(e);
        });
    },

    createItem() {
      let newItems = {
        client_id: 0,
        menutype: this.menu.menutype,
        list: {
          fullordering: "a.lft ASC",
          limit: 20
        }
      };
      axios({
        method: "post",
        url: "/vanilla/funnel_candidate/administrator/index.php?option=com_menus&view=items",
        data: qs.stringify({
          jform: newItems,
          task: "item.add",
          boxchecked: 0,
          [this.tokenMenu]: 1
        })
      })
        .then(() => {
          this.addItem();
        })
        .catch(e => {
          console.log(e);
        });
    },

    addItem() {
      let newItemsInfos = {
        title: this.itemTitle,
        alias: this.itemAlias,
        type: this.itemType,
        link: this.itemLink,
        browserNav: 0,
        template_style_id: 0,
        id: 0,
        menutype: this.menu.menutype,
        parent_id: this.itemParent_id,
        published: 1,
        home: 0,
        access: 1,
        language: "*",
        note: "",
        component_id: this.itemComponent_id
      };
      axios({
        method: "post",
        url:
          "/vanilla/funnel_candidate/administrator/index.php?option=com_menus&view=item&client_id=0&layout=edit&id=0",
        data: qs.stringify({
          jform: newItemsInfos,
          task: "item.apply",
          forcedLanguage: "",
          [this.tokenMenu]: 1
        })
      })
        .then(() => {})
        .catch(e => {
          console.log(e);
        });
    },

    removeItem() {
      axios({
        method: "post",
        url: "/vanilla/funnel_candidate/administrator/index.php?option=com_menus&view=items",
        data: qs.stringify({
          menutype: this.menu.menutype,
          cid: this.itemId,
          task: "items.trash",
          boxchecked: 1,
          [this.tokenMenu]: 1
        })
      })
        .then(() => {
          this.menuItems.splice(this.currentItemId, 1);
          this.menuItemSelected();
        })
        .catch(e => {
          console.log(e);
        });
    },

    modifyItem() {
      if (this.itemToChange.published == true) {
        this.itemToChange.published = 1;
      } else {
        this.itemToChange.published = 0;
      }
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=form&task=modifymenuitem",
        data: qs.stringify({
          itemid: this.itemId,
          itemtochange: this.itemToChange
        })
      })
        .then(() => {})
        .catch(e => {
          console.log(e);
        });
    },

    getToken() {
      axios
        .get(
          "/vanilla/funnel_candidate/administrator/index.php?option=com_menus&view=items&layout=give"
        )
        .then(response => {
          this.tokenMenu = response.data.data;
        })
        .catch(e => {
          console.log(e);
        });
    },

    menuItemSelected(e = 0) {
      if (e == 0) {
        id = 0;
      } else {
        var id = e.target.options.selectedIndex;
      }
      if (id == -1) {
        //do nothing
      } else {
        if (id != 0) {
          this.isNew = false;
        } else {
          this.isNew = true;
        }
        this.itemTitle = this.menuItems[id].title;
        this.itemAlias = this.menuItems[id].alias;
        this.itemType = this.menuItems[id].type;
        this.itemLink = this.menuItems[id].link;
        this.itemParent_id = this.menuItems[id].parent_id;
        this.itemComponent_id = this.menuItems[id].component_id;
        if (this.menuItems[id].published == 0) {
          this.itemPublished = false;
          this.menu.published = false;
        } else {
          this.itemPublished = true;
          this.menu.published = true;
        }
        this.menu.title = this.menuItems[id].title;
        this.menu.alias = this.menuItems[id].alias;
        this.menu.type = this.menuItems[id].type;
        this.menu.link = this.menuItems[id].link;
        this.menu.parent_id = this.menuItems[id].parent_id;
        this.menu.component_id = this.menuItems[id].component_id;
        this.itemId = this.menuItems[id].itemid;
        this.currentItemId = id;
      }
    }
  },

  created() {
    this.getToken();
    this.menu.menutype = "menu-profile" + this.profileId;

    axios
      .get(
        "index.php?option=com_emundus_onboard&controller=form&task=getmenu&prid=" + this.profileId
      )
      .then(response => {
        this.menu.menuTitle = response.data.data.title;
        this.menutitle = response.data.data.title;
        this.menu.id = response.data.data.id;
      })
      .catch(e => {
        console.log(e);
      });

    axios
      .get("index.php?option=com_emundus_onboard&controller=form&task=getaliases")
      .then(response => {
        this.allAliases = response.data.data;
      })
      .catch(e => {
        console.log(e);
      });

    axios
      .get(
        "index.php?option=com_emundus_onboard&controller=form&task=getmenuitems&menutype=" +
          this.menu.menutype
      )
      .then(response => {
        this.menuItems = response.data.data;
        this.menuItems.unshift(this.menuItems0);
      })
      .catch(e => {
        console.log(e);
      });
  },

  watch: {
    menutitle(val) {
      if (val == this.menu.menuTitle) {
        this.isMenuChange = false;
      } else {
        this.isMenuChange = true;
      }
    },
    itemPublished(val) {
      if (val == this.menu.published) {
        this.isItemChange = false;
      } else {
        this.isItemChange = true;
      }
      this.itemToChange.published = val;
    },
    itemTitle(val) {
      const regex = /[^a-zA-Z0-9]/gm;
      var newAlias = val.replace(regex, "-");
      if (val == this.menu.title) {
        this.isItemChange = false;
      } else {
        this.isItemChange = true;
      }
      this.itemToChange.title = val;
      this.itemAlias = newAlias;
    },
    itemAlias(val) {
      document.getElementById("bubbleInfo").style.display = "none";
      if (val == this.menu.alias) {
        this.isItemChange = false;
      } else {
        this.isItemChange = true;
        for (let i = 0; i < this.allAliases.length; i++) {
          if (this.allAliases[i].alias == val) {
            document.getElementById("bubbleInfo").style.display = "block";
          }
        }
      }
      this.itemToChange.alias = val;
    },
    itemType(val) {
      if (val == this.menu.type) {
        this.isItemChange = false;
      } else {
        this.isItemChange = true;
      }
      this.itemToChange.type = val;
    },
    itemLink(val) {
      if (val == this.menu.link) {
        this.isItemChange = false;
      } else {
        this.isItemChange = true;
      }
      this.itemToChange.link = val;
    },
    itemParent_id(val) {
      if (val == this.menu.parent_id) {
        this.isItemChange = false;
      } else {
        this.isItemChange = true;
      }
      this.itemToChange.parent_id = val;
    },
    itemComponent_id(val) {
      if (val == this.menu.component_id) {
        this.isItemChange = false;
      } else {
        this.isItemChange = true;
      }
      this.itemToChange.component_id = val;
    }
  }
};
</script>
