<template>
    <div class="container-evaluation">
        <ModalAddUser
                :group="this.group"
                :coordinatorAccess="1"
                :userManage="1"
                @UpdateUsers="getUsers"
        />
        <button class="create-user-admin__button bouton-sauvergarder-et-continuer-3" @click="$modal.show('modalAddUser')">{{ addUser }}</button>
        <table-component
                :data="users"
                sort-by="name"
                sort-order="asc"
                :filter-placeholder="Search"
                :filter-no-results="NoResultsFound"
                ref="table"
        >
            <table-column show="id" label="ID" data-type="numeric" hidden></table-column>
            <table-column show="name" :label="Name"></table-column>
            <table-column show="email" :label="Email"></table-column>
            <table-column show="lastvisitDate" :label="LastConnected" :filterable="false" data-type="date:DD/MM/YYYY"></table-column>
            <table-column show="block" :label="Status" :filterable="false" :formatter="statusFormatter"></table-column>
            <table-column :label="Actions" :sortable="false" :filterable="false" cell-class="user-list__actions">
                <template slot-scope="row">
                    <a @click="lockUser(row.id)" v-if="row.block == 0"><i class="fas fa-unlock user-unlock__icon"></i></a>
                    <a @click="unlockUser(row.id)" v-if="row.block == 1"><i class="fas fa-lock user-lock__icon"></i></a>
                    <a @click="resetPassword(row.id, row.name)"><i class="fas fa-redo"></i></a>
                    <a @click="showInfos(row.id)"><i class="fas fa-info-circle user-infos__icon"></i></a>
                </template>
            </table-column>
        </table-component>
        <div class="loading-form" v-if="loading">
            <Ring-Loader :color="'#de6339'" />
        </div>
    </div>
</template>

<script>
    import axios from "axios";
    import ModalAddUser from "../../views/advancedModals/ModalAddUser";
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
            };
        },

        methods: {
            getUsers(){
                axios({
                    method: "get",
                    url: "index.php?option=com_emundus_onboard&controller=program&task=getusers",
                }).then(response => {
                    this.users = response.data.data;
                    this.users.forEach((user) => {
                        user.lastvisitDate = new Date(user.lastvisitDate).toLocaleDateString(this.actualLanguage, this.options);
                    })
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
        },

        watch: {
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
        justify-content: space-between;
    }

    .user-list__actions a{
        cursor: pointer;
        color: #1b1f3c;
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
