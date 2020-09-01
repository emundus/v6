<template>
  <!-- modalC -->
  <span :id="'modalAffect' + groupProfile">
    <modal
      :name="'modalAffect' + groupProfile"
      height="auto"
      transition="nice-modal-fade"
      :min-width="200"
      :min-height="200"
      :delay="100"
      :adaptive="true"
      :clickToClose="false"
      @closed="beforeClose"
      @before-open="beforeOpen"
    >
      <div class="modalC-content">
        <div class="update-field-header">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalAffect' + groupProfile)">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{affectUsers}}
          </h2>
        </div>
        <p v-if="users.length === 0" class="mt-1 mb-1">{{usersEmpty}}</p>
        <div class="wrap" v-if="users.length !== 0">
          <div class="search">
            <input type="text" class="searchTerm" :placeholder="Search" v-model="searchTerm" @keyup="searchUserByTerm">
            <button type="button" class="searchButton" @click="searchUserByTerm">
              <em class="fas fa-search"></em>
            </button>
          </div>
        </div>
        <div v-for="(user, index) in users" :key="index" class="user-item">
            <input type="checkbox" class="form-check-input bigbox" v-model="affectedUsers[user.id]">
            <div class="ml-10px">
                <p>{{user.name}}</p>
                <p>{{user.email}}</p>
            </div>
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <a
          class="bouton-sauvergarder-et-continuer-3"
          @click.prevent="affectToGroup"
        >{{ Continuer }}</a>
        <a
          class="bouton-sauvergarder-et-continuer-3 w-retour"
          @click.prevent="$modal.hide('modalAffect' + groupProfile)"
        >{{Retour}}</a>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalAffect",
  props: { group: Object, groupProfile: String },
  data() {
    return {
      users: [],
      affectedUsers: [],
      searchTerm: '',
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      affectUsers: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_AFFECTUSERS"),
      usersEmpty: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_USERSEMPTY"),
    };
  },
  methods: {
    beforeClose(event) {
    },
    beforeOpen(event) {
      this.getUsers();
    },
    affectToGroup() {
      let users = [];
      this.users.forEach(element => {
        if(this.affectedUsers[element.id]){
          users.push(element.id);
        }
      });
      let groupToAffect = null;
      if(this.groupProfile == 'manager') {
        groupToAffect = this.group.manager;
      } else {
        groupToAffect = this.group.evaluator;
      }

      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=program&task=affectuserstogroup',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          group: groupToAffect,
          prog_group: this.group.prog,
          users: users
        })
      }).then(() => {
        this.affectedUsers = [];
        this.$emit("Update" + this.groupProfile);
        this.$modal.hide('modalAffect' + this.groupProfile)
      });
    },
    getUsers() {
      axios.get("index.php?option=com_emundus_onboard&controller=program&task=getuserstoaffect&group=" + this.group.prog)
              .then(response => {
                this.users = response.data.data;
              });
    },
    searchUserByTerm() {
      axios.get("index.php?option=com_emundus_onboard&controller=program&task=getuserstoaffectbyterm&group=" + this.group.prog + "&term=" + this.searchTerm)
              .then(response => {
                this.users = response.data.data;
              });
    }
  },
};
</script>

<style scoped>
.modalC-content {
  height: 100%;
  box-sizing: border-box;
  padding: 10px;
  font-size: 15px;
  overflow: auto;
}
.topright {
  font-size: 25px;
  float: right;
}
.btnCloseModal {
  background-color: inherit;
}
  .update-field-header{
    margin-bottom: 1em;
  }

  .update-title-header{
    margin-top: 0;
    display: flex;
    align-items: center;
  }

.user-item{
  display: flex;
  padding: 10px;
  background-color: #f0f0f0;
  border-radius: 5px;
  align-items: center;
  margin-bottom: 1em;
}

  .bigbox{
    height: 30px !important;
    width: 30px !important;
    cursor: pointer;
  }
</style>
