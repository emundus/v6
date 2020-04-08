<template>
  <div class="container-fluid full-width">
    <notifications
      group="foo-velocity"
      position="top right"
      animation-type="velocity"
      :speed="500"
    />
    <ModalSide
      v-for="(value, index) in formNameArray"
      :key="index"
      v-show="formObjectArray[indexHighlight]"
      :ID="formObjectArray[index].rgt"
      :element="formObjectArray[index].object"
      :index="index"
      @show="show"
      @UpdateUx="UpdateUXT"
      @UpdateName="UpdateName"
    />
    <div class="row">
      <div class="col-md-2">
        <div class="sidebar" data-spy="affix">
          <h1>{{profileLabel}}</h1>
          <!-- <legend class="legendSide">
            <span class="mr-rightS dinherit">
              <em class="fas fa-plus-circle btnPM"></em>
            </span>
          </legend>-->
          <ul class="fa-ul">
            <li v-for="(value, index) in formNameArray" :key="index" class="MenuForm">
              <!-- <span class="fa-li">
                <em class="fas fa-arrows-alt-v"></em>
              </span>-->
              <a
                @click="indexHighlight = index"
                class="MenuFormItem"
                :class="indexHighlight == index ? 'MenuFormItem_current' : ''"
              >{{formObjectArray[index].object.show_title.value}}</a>
              <BtnModalSide
                v-if="formObjectArray[indexHighlight]"
                :IDs="formObjectArray[index].rgt"
              />
            </li>
          </ul>
          <button class="btnreturn" @click="prevent()">{{Retour}}</button>
        </div>
      </div>
      <div class="col-md-10">
        <div class="container-fluid row BuilderExplain">
          <div class="col-md-3 Topbar separator">
            <h2 class="buildmenu">{{buildmenu}}</h2>
          </div>
          <div class="col-md-9 Topbar">
            <h2>{{preview}}</h2>
          </div>
        </div>
        <Builder
          :object="formObjectArray[indexHighlight]"
          v-if="formObjectArray[indexHighlight]"
          :UpdateUx="UpdateUx"
          @show="show"
        />
      </div>
    </div>
  </div>
</template>


<script>
import _ from "lodash";
import axios from "axios";

import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";

import "../assets/css/formbuilder.css";

import Builder from "../components/formClean/Builder";
import ModalSide from "../components/formClean/ModalSide";
import BtnModalSide from "../components/formClean/BtnModalSide";

const qs = require("qs");

export default {
  name: "FormBuilder",
  props: {
    prid: String,
    index: Number,
    fid: Number
  },
  components: {
    Builder,
    BtnModalSide,
    ModalSide
  },
  data() {
    return {
      UpdateUx: false,
      showModal: false,
      indexHighlight: "0",
      formNameArray: [],
      formObjectArray: [],
      formArray: [],
      thevalue: "",
      formList: "",
      profileLabel: "",
      id: 0,
      animation: {
        enter: {
          opacity: [1, 0],
          translateX: [0, -300],
          scale: [1, 0.2]
        },
        leave: {
          opacity: 0,
          height: 0
        }
      },
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      buildmenu: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDMENU"),
      preview: Joomla.JText._("COM_EMUNDUS_ONBOARD_PREVIEW")
    };
  },
  methods: {
    UpdateName(index, label) {
      this.formObjectArray[index].object.show_title.value = label;
    },
    UpdateUXT() {
      this.UpdateUx = true;
    },
    /**
     * ** Methods for notify
     */
    show(group, type = "", text = "", title = "Information") {
      this.$notify({
        group,
        title: `${title}`,
        text,
        type
      });
    },
    clean(group) {
      this.$notify({ group, clean: true });
    },
    //TODOS a refaire
    getDataObject: function() {
      this.indexHighlight = this.index;
      this.formList.forEach(element => {
        axios
          .get(element.link + "&format=vue_jsonclean")
          .then(response => {
            this.formNameArray.push({
              value: response.data.show_title.value,
              rgt: element.rgt
            });
            this.formObjectArray.push({
              object: response.data,
              rgt: element.rgt,
              link: element.link
            });
          })
          .then(r => {
            this.formObjectArray.sort((a, b) => a.rgt - b.rgt);
            this.formNameArray.sort((a, b) => a.rgt - b.rgt);
          })
          .catch(e => {
            console.log(e);
          });
      });
    },
    /**
     *  ** Récupère toute les formes du profile ID
     */
    getForms(profile_id) {
      axios({
        method: "get",
        url:
          "index.php?option=com_emundus_onboard&controller=form&task=getFormsByProfileId",
        params: {
          profile_id: profile_id
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      })
        .then(response => {
          this.formList = response.data.data;
          this.getProfileLabel(profile_id);
          this.getDataObject();
        })
        .catch(e => {
          console.log(e);
        });
    },
    /**
     * **Appel le nom du profile
     */
    getProfileLabel(profile_id) {
      axios({
        method: "get",
        url:
          "index.php?option=com_emundus_onboard&controller=form&task=getProfileLabelByProfileId",
        params: {
          profile_id: profile_id
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      })
        .then(response => {
          this.profileLabel = response.data.data.label;
        })
        .catch(e => {
          console.log(e);
        });
    },
    prevent() {
      window.location.replace(
        "index.php?option=com_emundus_onboard&view=form&layout=add&fid=" +
          this.fid
      );
    }
  },
  created() {
    this.getForms(this.prid);
  }
};
</script>

<style scoped>
.fa-li {
  left: -0.45em;
}

.full-width {
  width: 100vw;
  position: relative;
  margin-left: -50vw !important;
  left: 50%;
  margin-top: -4.2%;
}
.container {
  margin-bottom: 5%;
}
.MenuForm {
  padding-top: 40px;
  padding-left: 40px;
  font-family: "Open Sans", sans-serif;
  font-size: 16px;
  line-height: 20px;
  list-style: inside;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.MenuForm:hover {
  text-decoration: underline;
  color: salmon;
}
.MenuFormItem {
  color: black;
  cursor: pointer;
}
.MenuFormItem:hover {
  color: grey;
}
.MenuFormItem_current {
  color: #ef6d3b;
}
h1 {
  margin: 20px;
  line-height: 20px;
  font-family: "Open Sans", sans-serif;
  box-sizing: border-box;
}
.sidebar {
  padding-top: 20px;
  background-color: #f0f0f0;
  height: 100%;
  width: 16.9%;
}
body {
  background-color: #fafafa;
}
.Topbar {
  text-align: center;
  font-family: "Open Sans", sans-serif;
  padding: 25px 0;
  background-color: #f0f0f0;
}
.separator {
  border-right: 1px solid hsla(0, 0%, 81%, 0.5);
  border-left: 1px solid hsla(0, 0%, 81%, 0.5);
}

.btnreturn {
  position: relative;
  left: 37%;
  top: 5%;
  background-color: #1b1f3c;
  border-radius: 28px;
  border: 1px solid #1b1f3c;
  display: inline-block;
  cursor: pointer;
  color: #ffffff;
  font-family: Arial;
  font-size: 17px;
  padding: 12px 27px;
  text-decoration: none;
}
.btnreturn:hover {
  background-color: #ef6d3b;
  border: 1px solid #ef6d3b;
}
</style>