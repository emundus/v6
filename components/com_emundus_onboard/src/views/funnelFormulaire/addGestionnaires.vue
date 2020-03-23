<template>
  <div class="container-evaluation">
    <h2 class="heading">{{ funnelCategorie }}</h2>
    <p class="paragraphe-sous-titre">
      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
      elementum tristique.
    </p>
    <div v-for="(group, index) in groups" :key="index" class="ajouter-une-ligne">
      <a class="tick w-inline-block"></a>
      <div>Groupe d'utilisateur {{ group }}</div>
      <a @click="getAcls(group)" class="edit w-inline-block"></a>
      <a @click="deleteAcls(group, index)" class="bin w-inline-block"></a>
    </div>

    <div class="form-group container-flexbox-choisir-ou-plus w-clearfix">
      <select @change="getAcls2" class="dropdown-toggle-gestionnaires">
        <option value="" disabled selected>Choisir un groupe d'utilisateur</option>
        <option v-for="(group, index) in groupIds" :key="index"
          >Groupe d'utilisateur {{ group }}</option
        >
      </select>
      <button
        @click="
          stepGroup = 1;
          getAcls2(-1);
        "
        id="add-acl"
        class="plus w-inline-block"
        type="button"
      >
        +
      </button>
    </div>
    <div class="icon-title equipe"></div>

    <table
      v-show="displayAcls"
      id="em-modal-action-table"
      class="table table-hover em-showgroupright-table"
      style="color:black !important;"
    >
      <thead class="theadBorderBottom">
        <tr class="tdTextCenter">
          <th scope="col" class="modifGroup">{{ currentGroup }}</th>
          <th scope="col">
            <label for="c-check-all">{{ CrudCreate }}</label>
          </th>
          <th scope="col">
            <label for="r-check-all">{{ CrudRetrieve }}</label>
          </th>
          <th scope="col">
            <label for="u-check-all">{{ CrudUpdate }}</label>
          </th>
          <th scope="col">
            <label for="d-check-all">{{ CrudDelete }}</label>
          </th>
        </tr>
      </thead>
      <tbody class="tdTextCenter">
        <tr
          v-for="(crud, index) in groupRights"
          :key="index"
          class="em-actions-table-line"
          :id="index"
        >
          <td style="float: left;" :id="index">{{ actionsLabels[index] }}</td>
          <td action="c" :id="index">
            <a @click="changeCrud('c', index)">
              <span
                v-if="crud.c == 1"
                :id="'span-c' + index"
                class="glyphicon glyphicon-ok glyphiconVert"
              ></span>
              <span
                v-else
                :id="'span-c' + index"
                class="glyphicon glyphicon-ban-circle glyphiconRouge"
              ></span>
            </a>
          </td>
          <td action="r" :id="index">
            <a @click="changeCrud('r', index)">
              <span
                v-if="crud.r == 1"
                :id="'span-r' + index"
                class="glyphicon glyphicon-ok glyphiconVert"
              ></span>
              <span
                v-else
                :id="'span-r' + index"
                class="glyphicon glyphicon-ban-circle glyphiconRouge"
              ></span>
            </a>
          </td>
          <td action="u" :id="index">
            <a @click="changeCrud('u', index)">
              <span
                v-if="crud.u == 1"
                :id="'span-u' + index"
                class="glyphicon glyphicon-ok glyphiconVert"
              ></span>
              <span
                v-else
                :id="'span-u' + index"
                class="glyphicon glyphicon-ban-circle glyphiconRouge"
              ></span>
            </a>
          </td>
          <td action="d" :id="index">
            <a @click="changeCrud('d', index)">
              <span
                v-if="crud.d == 1"
                :id="'span-d' + index"
                class="glyphicon glyphicon-ok glyphiconVert"
              ></span>
              <span
                v-else
                :id="'span-d' + index"
                class="glyphicon glyphicon-ban-circle glyphiconRouge"
              ></span>
            </a>
          </td>
        </tr>
      </tbody>
    </table>
    <button
      v-show="displayAcls"
      @click="stepGroup == 0 ? updateAcls() : stepGroup == 1 ? addUsers() : addAcls()"
      class="buttonMenuItems"
      style="margin-bottom: 2%"
      type="button"
    >
      <em class="far fa-edit"></em>
      {{ stepGroup == 0 ? "Mettre à jour" : stepGroup == 1 ? "Suivant" : "Ajouter" }}
    </button>
  </div>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "addGestionnaires",

  props: {
    funnelCategorie: String,
    formulaireEmundus: Number
  },

  data() {
    return {
      stepGroup: 0,
      displayAcls: false,
      currentGroup: "",
      currentGroupId: 0,

      groups: [],
      groupIds: [],

      groupRights: [],
      oldGroupRights: [],
      actionsIds: [],
      actionsLabels: [],

      maxGroupId: 0,

      CrudCreate: Joomla.JText._("CREATE"),
      CrudRetrieve: Joomla.JText._("RETRIEVE"),
      CrudUpdate: Joomla.JText._("UPDATE"),
      CrudDelete: Joomla.JText._("DELETE")
    };
  },

  methods: {
    getAcls(groupId) {
      this.displayAcls = true;
      this.currentGroup = "Groupe d'utilisateur " + groupId;
      this.currentGroupId = groupId;

      axios
        .get(
          "index.php?option=com_emundus_onboard&controller=form&task=getgrouprights&groupid=" +
            groupId
        )
        .then(response => {
          this.groupRights = response.data.data;
          this.oldGroupRights = response.data.data;
          for (let i = 0; i < response.data.data.length; i++) {
            this.actionsIds.push(response.data.data[i].action_id);
          }
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=form&task=getactionslabels",
            data: qs.stringify({
              actionIds: this.actionsIds
            })
          })
            .then(response => {
              for (let i = 0; i < response.data.data.length; i++) {
                this.actionsLabels.push(response.data.data[i]);
              }
            })
            .catch(e => {
              console.log(e);
            });
        })
        .catch(e => {
          console.log(e);
        });
    },

    getAcls2(e) {
      if (e == -1) {
        groupId = 8;
        this.maxGroupId++;
        this.currentGroup = "Groupe d'utilisateur " + this.maxGroupId;
      } else {
        var index = e.target.options.selectedIndex - 1;
        var groupId = this.groupIds[index];
        this.currentGroup = "Groupe d'utilisateur " + groupId;
      }

      this.currentGroupId = groupId;

      axios
        .get(
          "index.php?option=com_emundus_onboard&controller=form&task=getgrouprights&groupid=" +
            groupId
        )
        .then(response => {
          this.groupRights = response.data.data;
          this.oldGroupRights = response.data.data;
          for (let i = 0; i < response.data.data.length; i++) {
            this.actionsIds.push(response.data.data[i].action_id);
          }
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=form&task=getactionslabels",
            data: qs.stringify({
              actionIds: this.actionsIds
            })
          })
            .then(response => {
              for (let i = 0; i < response.data.data.length; i++) {
                this.actionsLabels.push(response.data.data[i]);
              }
              for (let i = 0; i < this.groupRights.length; i++) {
                var spanC = document.getElementById("span-c" + i);
                var spanR = document.getElementById("span-r" + i);
                var spanU = document.getElementById("span-u" + i);
                var spanD = document.getElementById("span-d" + i);

                if (this.groupRights[i].c == 0) {
                  spanC.className = "glyphicon glyphicon-ban-circle glyphiconRouge";
                  spanC.style.color = "#ff0000";
                } else if (this.groupRights[i].c == 1) {
                  spanC.className = "glyphicon glyphicon-ok glyphiconVert";
                  spanC.style.color = "#00c500";
                }

                if (this.groupRights[i].r == 0) {
                  spanR.className = "glyphicon glyphicon-ban-circle glyphiconRouge";
                  spanR.style.color = "#ff0000";
                } else if (this.groupRights[i].r == 1) {
                  spanR.className = "glyphicon glyphicon-ok glyphiconVert";
                  spanR.style.color = "#00c500";
                }

                if (this.groupRights[i].u == 0) {
                  spanU.className = "glyphicon glyphicon-ban-circle glyphiconRouge";
                  spanU.style.color = "#ff0000";
                } else if (this.groupRights[i].u == 1) {
                  spanU.className = "glyphicon glyphicon-ok glyphiconVert";
                  spanU.style.color = "#00c500";
                }

                if (this.groupRights[i].d == 0) {
                  spanD.className = "glyphicon glyphicon-ban-circle glyphiconRouge";
                  spanD.style.color = "#ff0000";
                } else if (this.groupRights[i].d == 1) {
                  spanD.className = "glyphicon glyphicon-ok glyphiconVert";
                  spanD.style.color = "#00c500";
                }
              }
            })
            .then(() => {
              this.displayAcls = true;
            })
            .catch(e => {
              console.log(e);
            });
        })
        .catch(e => {
          console.log(e);
        });
    },

    deleteAcls(group_id, index) {
      axios
        .post(
          "index.php?option=com_emundus_onboard&controller=form&task=deletegroup&groupid=" + groupId
        )
        .then(response => {
          this.groupIds.splice(index, 1);
        })
        .catch(e => {
          console.log(e);
        });
    },

    addUsers() {
      this.stepGroup = 2;
    },

    addAcls() {
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=form&task=addgroup",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          groupid: this.maxGroupId,
          campaignid: this.formulaireEmundus,
          data: this.groupRights
        })
      })
        .then(response => {
          this.groupIds.push(this.maxGroupId);
          this.groups.push(this.maxGroupId);
          this.stepGroup = 0;
          this.displayAcls = false;
        })
        .catch(e => {
          console.log(e);
        });
    },

    changeCrud(crud, index) {
      var currentGlyphicon = document.getElementById("span-" + crud + index);
      var classGlyphicon = "";
      var newCrud = -1;
      var old = this.oldGroupRights[index][crud];

      if (
        currentGlyphicon.className == "glyphicon glyphicon-ok" ||
        currentGlyphicon.className == "glyphicon glyphicon-ok glyphiconVert"
      ) {
        classGlyphicon = "glyphicon glyphicon-ban-circle";
        newCrud = 0;
      } else if (
        currentGlyphicon.className == "glyphicon glyphicon-ban-circle" ||
        currentGlyphicon.className == "glyphicon glyphicon-ban-circle glyphiconRouge"
      ) {
        classGlyphicon = "glyphicon glyphicon-ok";
        newCrud = 1;
      }

      if (this.oldGroupRights[index][crud] == newCrud) {
        if (currentGlyphicon.className == "glyphicon glyphicon-ok") {
          classGlyphicon = "glyphicon glyphicon-ban-circle glyphiconRouge";
          currentGlyphicon.style.color = "#ff0000";
        } else if ((currentGlyphicon.className = "glyphicon glyphicon-ban-circle")) {
          classGlyphicon = "glyphicon glyphicon-ok glyphiconVert";
          currentGlyphicon.style.color = "#00c500";
        }
      } else {
        currentGlyphicon.style.color = "#0095A4";
      }

      currentGlyphicon.className = classGlyphicon;

      this.groupRights[index][crud] = newCrud;
      this.oldGroupRights[index][crud] = old;
    }
  },

  created() {
    axios
      .get(
        "index.php?option=com_emundus_onboard&controller=form&task=getgroupids&campaign=" +
          this.formulaireEmundus
      )
      .then(response => {
        for (let i = 0; i < response.data.data.length; i++) {
          this.groupIds.push(response.data.data[i].group_id);
        }
        for (let i = 0; i < response.data.groups.length; i++) {
          this.groups.push(response.data.groups[i].parent_id);
        }
        this.maxGroupId = parseInt(response.data.max.maxParent);
      })
      .catch(e => {
        console.log(e);
      });
  }
};
</script>
