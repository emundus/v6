<template>
    <div class="em-settings-menu">
        <ModalAddUser
                :group="this.group"
                :coordinatorAccess="1"
                :userManage="1"
                @UpdateUsers="getUsers"
        />
        <button class="create-user-admin__button bouton-sauvergarder-et-continuer-3" @click="$modal.show('modalAddUser')">{{ addUser }}</button>
        <div class="mt-1" id="blocked_filter">
            <div class="d-flex mr-2">
                <input type="checkbox" class="mr-1" v-model="block" />
                <p>{{BlockedUsers}}</p>
            </div>
          <div class="d-flex mt-1">
            <div class="d-flex">
                <p class="mb-0 mr-1" style="white-space: nowrap">{{Program}} : </p>
                <select class="dropdown-toggle" style="min-width: 80%" v-model="searchProgram">
                    <option selected value="-1"></option>
                    <option v-for="program in programs" :value="program.id">{{program.label}}</option>
                </select>
            </div>
            <div class="d-flex" style="margin-left: 5em">
              <p class="mb-0 mr-1" style="white-space: nowrap">{{Role}} : </p>
              <select class="dropdown-toggle" style="min-width: 80%" v-model="searchRole">
                <option selected value="-1"></option>
                <option value="5">{{Administrator}}</option>
                <option value="6">{{Evaluator}}</option>
              </select>
            </div>
          </div>
        </div>
        <table-component
                :data="fetchData"
                sort-by="name"
                sort-order="asc"
                show-caption="false"
                :filter-placeholder="Search + '...'"
                :filter-no-results="NoResultsFound"
                ref="table"
                :key="table_users"
        >
            <table-column show="id" label="ID" data-type="numeric" hidden></table-column>
            <table-column show="name" :label="Name"></table-column>
            <table-column show="email" :label="Email"></table-column>
            <table-column show="profile" :label="Role" :formatter="roleFormatter"></table-column>
            <table-column show="lastvisitDate" :label="LastConnected" :filterable="false" data-type="date:DD/MM/YYYY"></table-column>
            <table-column show="block" :label="Status" :filterable="false" :formatter="statusFormatter"></table-column>
            <table-column :label="Actions" :sortable="false" :filterable="false" cell-class="user-list__actions">
                <template slot-scope="row">
                    <a @click="lockUser(row.id)" v-if="row.block == 0" :title="LockUser"><i class="fas fa-unlock user-unlock__icon"></i></a>
                    <a @click="unlockUser(row.id)" v-if="row.block == 1" :title="UnlockUser"><i class="fas fa-lock user-lock__icon"></i></a>
                    <a @click="resetPassword(row.id, row.name)" :title="ResetPassword"><i class="fas fa-redo"></i></a>
                    <!--<a @click="showInfos(row.id)" :title="ShowInformations"><i class="fas fa-info-circle user-infos__icon"></i></a>-->
                </template>
            </table-column>
        </table-component>
        <div class="loading-form" v-if="loading">
            <Ring-Loader :color="'#12DB42'" />
        </div>
    </div>
</template>

<script>
    import axios from "axios";
    import ModalAddUser from "@/components/AdvancedModals/ModalAddUser";
    import Swal from "sweetalert2";

    const qs = require("qs");

    export default {
        name: "editUsers",

        components: {
            ModalAddUser
        },

        props: {
            actualLanguage: String
        },

        data() {
            return {
                options: { year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric' },
                loading: false,
                tableUsers: 0,
                users: [],
                programs: [],
                filters: {
                  block: false,
                  searchProgram: -1,
                  searchRole: -1,
                },
                pagination: {
                  currentPage: 0,
                  totalPages: 1
                },
                block: false,
                searchProgram: -1,
                searchRole: -1,
                table_users: 0,
                Name: Joomla.JText._("COM_EMUNDUS_ONBOARD_LASTNAME"),
                Email: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL"),
                LastConnected: Joomla.JText._("COM_EMUNDUS_ONBOARD_LAST_CONNECTED"),
                Status: Joomla.JText._("COM_EMUNDUS_ONBOARD_DOSSIERS_STATUS"),
                Search: Joomla.JText._("COM_EMUNDUS_ONBOARD_SEARCH"),
                Activated: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTIVATED"),
                Blocked: Joomla.JText._("COM_EMUNDUS_ONBOARD_BLOCKED"),
                NoResultsFound: Joomla.JText._("COM_EMUNDUS_ONBOARD_NO_RESULTS_FOUND"),
                Actions: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTIONS"),
                addUser: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADDUSER"),
                LockUser: Joomla.JText._("COM_EMUNDUS_ONBOARD_LOCK_USER"),
                UnlockUser: Joomla.JText._("COM_EMUNDUS_ONBOARD_UNLOCK_USER"),
                ResetPassword: Joomla.JText._("COM_EMUNDUS_ONBOARD_RESET_PASSWORD"),
                BlockedUsers: Joomla.JText._("COM_EMUNDUS_ONBOARD_BLOCKED_USERS"),
                Program: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM"),
                Role: Joomla.JText._("COM_EMUNDUS_ONBOARD_ROLE"),
                Administrator: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATOR"),
                Evaluator: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATOR"),
            };
        },

        methods: {
          fetchData({page}) {
            return new Promise(resolve=>{
                axios({
                    method: "get",
                    url: "index.php?option=com_emundus_onboard&controller=program&task=getusers",
                    params: {
                       filters : this.filters,
                       page: {page}
                    },
                    paramsSerializer: params => {
                        return qs.stringify(params);
                    }
                    }).then(response => resolve({
                        data: response.data.data.users,
                        pagination: {
                          totalPages: response.data.data.users_count/10,
                          currentPage: page,
                          count: response.data.data.users_count
                        },
                        })
                    )
            })
          },

            getUsers(){
                axios({
                    method: "get",
                    url: "index.php?option=com_emundus_onboard&controller=program&task=getusers",
                    params: {
                       filters : this.filters,
                    },
                    paramsSerializer: params => {
                        return qs.stringify(params);
                    }
                    }).then(response => {
                    this.users = response.data.data.users;
                    this.users.forEach((user,key) => {
                        user.lastvisitDate = new Date(user.lastvisitDate).toLocaleDateString(this.actualLanguage, this.options);
                    });
                    document.getElementsByClassName('table-component__filter')[0].append(document.getElementById('blocked_filter'));
                    this.$refs.table.refresh();
                });
            },
            getProgramsList() {
                axios({
                    method: "get",
                    url: "index.php?option=com_emundus_onboard&controller=program&task=getallprogram",
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
            unlockUser(id){
                axios({
                    method: "post",
                    url: 'index.php?option=com_emundus_onboard&controller=settings&task=unlockuser',
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    data: qs.stringify({
                        user: id,
                    })
                }).then((rep) => {
                    if(rep.data.status == true) {
                        this.users.find(us => us.id == id).block = 0;
                        this.$refs.table.refresh();
                    }
                });
            },
            lockUser(id){
                axios({
                    method: "post",
                    url: 'index.php?option=com_emundus_onboard&controller=settings&task=lockuser',
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    data: qs.stringify({
                        user: id,
                    })
                }).then((rep) => {
                    if(rep.data.status == true) {
                        this.users.find(us => us.id == id).block = 1;
                        this.$refs.table.refresh();
                    }
                });
            },
            resetPassword(id, name){
                Swal.fire({
                    title: Joomla.JText._("COM_EMUNDUS_ONBOARD_RESET_PASSWORD"),
                    text: Joomla.JText._("COM_EMUNDUS_ONBOARD_RESET_PASSWORD_MESSAGE") + name,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#de6339',
                    confirmButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
                    cancelButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
                    reverseButtons: true
                }).then(result => {
                        if (result.value) {
                            axios({
                                method: "post",
                                url: 'index.php?option=com_emundus&controller=users&task=regeneratepassword',
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                data: qs.stringify({
                                    user: id,
                                })
                            }).then((rep) => {
                                console.log(rep);
                                Swal.fire({
                                    text: rep.data.msg,
                                    type: "success",
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    confirmButtonColor: '#de6339',
                                    timer: 2000,
                                })
                            });
                        }
                });
            },
            showInfos(id){
                console.log(id)
            },

            // Table formatter
            statusFormatter(value, rowProperties) {
                if(value == 0){
                    return '<i class="fas fa-check-circle activated col-md-2"></i><span class="ml-10px">' + this.Activated + '</span>';
                } else {
                    return '<i class="fas fa-minus-circle blocked col-md-2"></i><span class="ml-10px">' + this.Blocked + '</span>';
                }
            },
          roleFormatter(value, rowProperties) {
            if(value == 5){
              return '<span>' + this.Administrator + '</span>';
            } else {
              return '<span>' + this.Evaluator + '</span>';
            }
          }
            /*actionFormatter(value) {
                let user = this.users.find(user => user.id == value);
                let lockAction = '<a onclick="lockUser(' + user.id + ')"><i class="fas fa-unlock"></i></a>\n'
                if(user.block == 1){
                    lockAction = '<a onclick="unlockUser(' +  user.id + ')"><i class="fas fa-lock"></i></a>\n'
                }
                return lockAction +
                    '<a><i class="fas fa-redo"></i></a>\n' +
                    '<a><i class="fas fa-info-circle"></i></a>';
            }*/
            //
        },

        created() {
            this.getUsers();
            this.getProgramsList();
            //document.getElementsByClassName('table-component__filter')[0].append(document.getElementById('blocked_filter'));
        },

        watch: {
            block: function(value) {
                this.filters.block = value;
                this.getUsers();
            },

            searchProgram: function(value) {
                this.filters.searchProgram = value;
                this.getUsers();
            },

            searchRole: function(value) {
              this.filters.searchRole = value;
              this.getUsers();
            }
        }
    };
</script>
<style>
    .activated{
        color: green;
        width: 17px;
        height: 17px;
    }
    .blocked{
        color: darkred;
        width: 17px;
        height: 17px;
    }
    .user-list__actions{
        display: flex;
        justify-content: start;
    }

    .user-list__actions a{
        cursor: pointer;
        color: #1b1f3c;
        margin-right: 10px;
    }

    .user-infos__icon{
        color: #1b1f3c;
    }

    .user-lock__icon{
        color: darkred;
    }

    .user-unlock__icon{
        color: #1b1f3c;
    }
</style>
