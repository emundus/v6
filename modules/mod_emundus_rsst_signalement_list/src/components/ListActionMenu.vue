<template>
    <div class="list-actions-menu">
        <v-popover
            class="em-pointer"
            :popoverArrowClass="'custom-popover-arrow'"
        >
            <span class="tooltip-target b3 material-icons">more_vert</span>
            <template slot="popover">
                <actions :actionColumnsAvailableValue="actionColumnsAvailableValue"
                         :actionColumn = "actionColumn"
                ></actions>
            </template>
        </v-popover>
    </div>
</template>

<script>
import actions from "./ActionMenu.vue";
import ListService from '../services/list';

export default {
    name: "ListActionMenu",
    components: {actions},
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
        async retriveListActionsData() {

            try {
                const response = await ListService.getListActionAndDataContains(this.listId,this.actionColumnId);
                this.actionColumn = response.data.actionsColumns;

                this.actionColumnsAvailableValue = response.data.actionsData;

            } catch (e) {
                console.log(e);
            }
        },
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
