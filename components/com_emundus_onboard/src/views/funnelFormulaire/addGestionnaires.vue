<template>
    <div class="container-evaluation">
        <ModalAddUser
                :group="this.group"
                :coordinatorAccess="coordinatorAccess"
                :userManage="0"
                @Updatemanager="getManagersInGroup()"
                @Updateevaluator="getEvaluatorsInGroup()"
        />
        <ModalAffect
                v-if="coordinatorAccess != 0"
                :group="this.group"
                :groupProfile="'manager'"
                @Updatemanager="getManagersInGroup()"
        />
        <ModalAffect
                :group="this.group"
                :groupProfile="'evaluator'"
                @Updateevaluator="getEvaluatorsInGroup()"
        />
        <a @click="$modal.show('modalAddUser')" class="bouton-sauvergarder-et-continuer-3 create-user">{{ addUser }}</a>
        <div class="choices-buttons" v-if="coordinatorAccess != 0">
            <h2 style="margin-bottom: 0">{{ Administrators }}</h2>
            <a @click="$modal.show('modalAffectmanager')" class="bouton-sauvergarder-et-continuer-3">{{ affectUsers }}</a>
        </div>
        <transition-group :name="'slide-down'" type="transition">
        <div v-for="(manager, index) in managers" :key="index" class="manager-item" v-if="coordinatorAccess != 0">
            <div>
                <p>{{manager.name}}</p>
                <p>{{manager.email}}</p>
            </div>
            <button type="button" @click="removeManager(manager,index)" class="remove-user"><em class="fas fa-minus"></em></button>
        </div>
        </transition-group>
        <div class="choices-buttons">
            <h2 style="margin-bottom: 0">{{ Evaluators }}</h2>
            <a @click="$modal.show('modalAffectevaluator')" class="bouton-sauvergarder-et-continuer-3">{{ affectUsers }}</a>
        </div>
        <transition-group :name="'slide-down'" type="transition">
        <div v-for="(evaluator, index) in evaluators" :key="index" class="manager-item">
            <div>
                <p>{{evaluator.name}}</p>
                <p>{{evaluator.email}}</p>
            </div>
            <button type="button" @click="removeEvaluator(evaluator,index)" class="remove-user"><em class="fas fa-minus"></em></button>
        </div>
        </transition-group>
    </div>
</template>

<script>
    import axios from "axios";
    import ModalAddUser from "../advancedModals/ModalAddUser";
    import ModalAffect from "../advancedModals/ModalAffect";

    const qs = require("qs");

    export default {
        name: "addGestionnaires",

        components: {
            ModalAffect,
            ModalAddUser
        },

        props: {
            funnelCategorie: String,
            group: Object,
            coordinatorAccess: Number
        },

        data() {
            return {
                managers: [],
                evaluators: [],
                addUser: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADDUSER"),
                affectUsers: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_AFFECTUSERS"),
                Administrators: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATORS"),
                Evaluators: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATORS"),
            };
        },

        methods: {
            getManagersInGroup(){
                axios.get("index.php?option=com_emundus_onboard&controller=program&task=getmanagers&group=" + this.group.manager)
                    .then(response => {
                        this.managers = response.data.data;
                    });
            },
            getEvaluatorsInGroup(){
                axios.get("index.php?option=com_emundus_onboard&controller=program&task=getevaluators&group=" + this.group.evaluator)
                    .then(response => {
                        this.evaluators = response.data.data;
                    });
            },
            removeManager(manager,key){
                axios({
                    method: "post",
                    url: 'index.php?option=com_emundus_onboard&controller=program&task=removefromgroup',
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    data: qs.stringify({
                        id: manager.id,
                        group: this.group.manager,
                        prog_group: this.group.prog
                    })
                }).then(() => {
                    this.managers.splice(key,1);
                });
            },
            removeEvaluator(evaluator,key){
                axios({
                    method: "post",
                    url: 'index.php?option=com_emundus_onboard&controller=program&task=removefromgroup',
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    data: qs.stringify({
                        id: evaluator.id,
                        group: this.group.evaluator,
                        prog_group: this.group.prog
                    })
                }).then(() => {
                    this.evaluators.splice(key,1);
                });
            }
        },

        created() {
            this.getManagersInGroup();
            this.getEvaluatorsInGroup();
        }
    };
</script>
<style>
    .create-user{
        text-align: center;
        width: 100%;
        margin-bottom: 4em;
    }

    .choices-buttons{
        width: 100%;
        display: flex;
        justify-content: space-between;
        margin-bottom: 1em;
        margin-top: 4em;
    }

    .choices-buttons .bouton-sauvergarder-et-continuer-3{
        float: unset;
    }

    .manager-item{
        display: flex;
        justify-content: space-between;
        padding: 30px;
        background-color: #f0f0f0;
        border-radius: 5px;
        align-items: center;
        margin-bottom: 1em;
    }

    .remove-user{
        border-radius: 50%;
        height: 30px;
        width: 30px;
        transition: all 0.3s ease-in-out;
    }
    .remove-user:hover{
        background-color: red;
    }
    .remove-user:hover > .fa-minus {
        color: white;
    }

    .fa-minus{
        color: red;
        cursor: pointer;
        width: 15px;
        height: 15px;
    }
</style>
