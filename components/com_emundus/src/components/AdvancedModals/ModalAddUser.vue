<template>
  <!-- modalC -->
  <span :id="'modalAddUser'">
    <modal
        :name="'modalAddUser'"
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
      <div class="fixed-header-modal">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalAddUser')">
              <em class="fas fa-times"></em>
            </button>
          </div>
                        <div class="update-field-header">
          <h2 class="update-title-header">
             {{ addUser }}
          </h2>
                        </div>
        </div>

      <div class="modalC-content">
        <div class="form-group">
          <label>{{ Lastname }}* :</label>
          <input v-model="form.lastname" type="text" class="form__input field-general w-input" maxlength="40"
                 :class="{ 'is-invalid': errors.lastname}"/>
          <p v-if="errors.lastname" class="error">
            <span class="error">{{ LastnameRequired }}</span>
          </p>
        </div>
        <div class="form-group">
          <label>{{ Firstname }}* :</label>
          <input v-model="form.firstname" type="text" class="form__input field-general w-input" maxlength="40"
                 :class="{ 'is-invalid': errors.firstname}"/>
          <p v-if="errors.firstname" class="error">
            <span class="error">{{ FirstnameRequired }}</span>
          </p>
        </div>
        <div class="form-group">
          <label>{{ Email }}* :</label>
          <input v-model="form.email" type="text" class="form__input field-general w-input" maxlength="40"
                 :class="{ 'is-invalid': errors.email}"/>
          <p v-if="errors.email" class="error">
            <span class="error">{{ EmailRequired }}</span>
          </p>
        </div>
        <div class="form-group" v-if="userManage == 1">
          <label class="mb-1">{{ Program }}* :</label>
          <div class="select-all">
              <input type="checkbox" class="form-check-input bigbox" @click="selectAllPrograms" v-model="selectall">
              <label>
                {{ SelectAll }}
              </label>
          </div>
          <div class="users-block">
            <div v-for="(program, index) in programs" :key="index" class="user-item">
              <input type="checkbox" class="form-check-input bigbox" v-model="affected_programs[program.id]">
              <div class="ml-10px">
                  <p>{{ program.label }}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group" v-if="userManage == 0 || !least_one_program">
          <label>{{ Role }}* :</label>
          <select v-model="form.profile" class="dropdown-toggle" :class="{ 'is-invalid': errors.profile}">
            <option value="5" v-if="coordinatorAccess != 0">{{ Administrator }}</option>
            <option value="6">{{ Evaluator }}</option>
          </select>
          <p v-if="errors.profile" class="error">
            <span class="error">{{ RoleRequired }}</span>
          </p>
        </div>
      </div>
      <div class="em-flex-row em-flex-space-between mb-1">
                <button type="button"
                        class="bouton-sauvergarder-et-continuer w-retour"
                        @click.prevent="$modal.hide('modalAddUser')"
                >{{ Retour }}</button>
        <button type="button"
                class="bouton-sauvergarder-et-continuer"
                @click.prevent="createUser()"
        >{{ Continuer }}</button>
      </div>
      <div class="em-page-loader" v-if="loading"></div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

const qs = require("qs");

export default {
  name: "modalAddUser",
  props: {
    group: Object,
    coordinatorAccess: Number,
    userManage: Number
  },
  data() {
    return {
      errors: {
        firstname: false,
        lastname: false,
        login: false,
        email: false,
      },
      form: {
        login: '',
        firstname: '',
        lastname: '',
        campaigns: '',
        oprofiles: '',
        groups: '',
        profile: 5,
        jgr: 13,
        email: '',
        newsletter: 0,
        university_id: 0,
        ldap: 0
      },
      affected_programs: [],
      least_one_program: true,
      programs: [],
      selectall: false,
      changes: false,
      loading: false,
      addUser: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_ADDUSER"),
      Retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      Firstname: this.translate("COM_EMUNDUS_ONBOARD_FIRSTNAME"),
      FirstnameRequired: this.translate("COM_EMUNDUS_ONBOARD_FIRSTNAME_REQUIRED"),
      Lastname: this.translate("COM_EMUNDUS_ONBOARD_LASTNAME"),
      LastnameRequired: this.translate("COM_EMUNDUS_ONBOARD_LASTNAME_REQUIRED"),
      Email: this.translate("COM_EMUNDUS_ONBOARD_EMAIL"),
      EmailRequired: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_REQUIRED"),
      Role: this.translate("COM_EMUNDUS_ONBOARD_ROLE"),
      RoleRequired: this.translate("COM_EMUNDUS_ONBOARD_ROLE_REQUIRED"),
      Administrator: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATOR"),
      Evaluator: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATOR"),
      Program: this.translate("COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM"),
      SelectAll: this.translate("COM_EMUNDUS_ONBOARD_SELECT_ALL"),
    };
  },
  methods: {
    beforeClose(event) {
    },
    beforeOpen(event) {
    },
    selectAllPrograms() {
      if (!this.selectall) {
        this.least_one_program = false;
      } else {
        this.least_one_program = true;
      }
      this.programs.forEach(element => {
        if (!this.selectall) {
          this.affected_programs[element.id] = true;
        } else {
          this.affected_programs[element.id] = false;
        }
      });
      this.$forceUpdate();
    },
    getProgramsList() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=programme&task=getallprogram",
        params: {
          filter: '',
          sort: '',
          recherche: '',
          lim: 100,
          page: 1,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.programs = response.data.data;
      });
    },
    createUser() {
      this.errors = {
        firstname: false,
        lastname: false,
        identifier: false,
        email: false,
      };
      if (this.form.lastname == '') {
        this.errors.lastname = true;
        return 0;
      }
      if (this.form.firstname == '') {
        this.errors.firstname = true;
        return 0;
      }
      if (this.form.email == '') {
        this.errors.email = true;
        return 0;
      }

      this.form.login = this.form.email;
      this.loading = true;
      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=users&task=adduser',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify(this.form)
      }).then((response) => {
        if (response.data.status == true) {
          if (!this.least_one_program && this.userManage == 1) {
            axios({
              method: "get",
              url: "index.php?option=com_emundus&controller=programme&task=getgroupsbyprograms",
              params: {
                programs: this.affected_programs,
              },
              paramsSerializer: params => {
                return qs.stringify(params);
              }
            }).then(rep => {
              Object.values(rep.data.groups).forEach((group) => {
                console.log(group)
                this.affectUserToRole(group);
              })
            });
          } else if (this.userManage == 0) {
            this.affectUserToRole(this.group);
          } else {
            this.loading = false;
            this.$emit("UpdateUsers", this.form);
            this.$modal.hide('modalAddUser');
          }

        } else {
          this.loading = false;
          Swal.fire({
            title: this.translate("COM_EMUNDUS_ONBOARD_ERROR"),
            text: response.data.msg,
            type: "error",
            showCancelButton: false,
            confirmButtonColor: '#de6339',
            confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
          });
        }
      }).catch((error) => {
        console.log(error);
      });
    },

    affectUserToRole(group) {
      let grouptoaffect = null;
      if (this.form.profile == 5) {
        grouptoaffect = group.manager;
      } else {
        grouptoaffect = group.evaluator;
      }

      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=programme&task=affectusertogroup',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          group: grouptoaffect,
          prog_group: group.prog,
          email: this.form.email
        })
      }).then((rep) => {
        this.loading = false;
        if (this.userManage == 1) {
          this.$emit("UpdateUsers", this.form);
        } else {
          if (this.form.profile == 5) {
            this.$emit("Updatemanager");
          } else {
            this.$emit("Updateevaluator");
          }
        }
        this.$modal.hide('modalAddUser')
      });
    }
  },

  created() {
    if (this.userManage == 1) {
      this.getProgramsList();
    }
  },

  watch: {
    affected_programs: function () {
      this.least_one_program = this.affected_programs.every((value) => {
        return value === false;
      });
    }
  }
};
</script>

<style scoped>
p .error {
  position: absolute;
  bottom: -10px;
  left: 28%;
}
</style>
