<template>
    <div class="list-actions-menu">
        <v-popover class="em-pointer" :popoverArrowClass="'custom-popover-arrow'">
            <span class="tooltip-target b3 material-icons">more_vert</span>
            <div slot="popover">
                <action-menu
		                :actionColumnsAvailableValue="actionColumnsAvailableValue"
		                :actionColumn = "actionColumn"
		                @setAs="setAs">
                </action-menu>
            </div>
        </v-popover>
    </div>
</template>

<script>
import ActionMenu from "./ActionMenu.vue";
import ListService from '../services/list';

export default {
    name: "ListActionMenu",
    components: {
	    ActionMenu
    },
    props: {
        actionColumnId: {
            type:String,
            required: false
        },
        listId:{
            type: String,
            required: true,
        },
    },
    created() {
        this.retriveListActionsData();
    },
    data: () => ({
        actionColumn: '',
        actionColumnsAvailableValue: [],
    }),
    methods: {
        retriveListActionsData() {
	        ListService.getListActionAndDataContains(this.listId,this.actionColumnId).then((response) => {
		        if (response) {
			        this.actionColumn = response.data.actionsColumns;
			        this.actionColumnsAvailableValue = response.data.actionsData;
		        }
	        });
        },
        setAs(actionColumn,value){
	        this.$emit('setAs',actionColumn,value);
        }
    }
}
</script>

<style lang="scss" scoped>
.list-actions-menu {
    display: flex;
    align-items: center;
    justify-content: flex-end;

    .cta-block:hover {
        color: #298721;
    }

    &#list-row#action-menu {
        margin: 0;
        transform: rotate(90deg);
    }
}

.material-icons, .material-icons-outlined {
    font-size: 24px !important;
}
</style>
