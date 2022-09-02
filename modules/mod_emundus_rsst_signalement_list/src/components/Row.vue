<template>
    <tr class="list-row">
        <td>
            <input type="checkbox" class="em-switch input" v-model = 'checkedRows.rows' :value ='rowData' checked = 'true'>
        </td>

        <td v-for="column in listColumns" :key="column.label">

            <template v-if="column.column_name != 'etat' || column.column_name != 'publication'">
                <template v-if="column.plugin =='date'">
                    {{ formattedDate(rowData[column.column_name]) }}
                </template>
                <template v-else>
                    {{ rowData[column.column_name] }}
                </template>
            </template>
            <template v-else>
                <span :class="classFromValue(rowData[column.column_name])">
                        {{ texteFromValue(rowData[column.column_name]) }}
                </span>
            </template>

        </td>
        <td>
			<span>
				<list-action-menu :actionColumnId="actionColumnId" :listId="listId" @setAs="setAs"></list-action-menu>
			</span>
        </td>
    </tr>
</template>

<script>
import ListActionMenu from './ListActionMenu.vue';
import ListService from '../services/list';
export default {
    name: "Row",
    props: {
        rowData: {
            type: Object,
            required: true,
        },
        listColumns: {
            type: Array,
            required: true
        },
        checkedRows:{
            type: Object,
            required : true
        },
        actionColumnId: {
            type:String,
            required: false
        },
        listId:{
            type: String,
            required: true,
        },
    },
    components: {
        'list-action-menu': ListActionMenu
    },
    data:()=>({
        isChecked : false,
    }),
    watch : {
        /*checkedRows: function(val){
            console.log('^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^');
            console.log(val);
            console.log('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
            this.isChecked = val.rows.some(row => row.id === this.rowData.id);

        }*/
    },
    methods: {
        classFromValue(val) {
            let className = '';
            switch (val) {
                case 'a_faire':
                    className = 'tag todo';
                    break;
                case 'en_cours':
                    className = 'tag inprogress';
                    break;
                case 'fait' :
                    className = 'tag done';
                    break;
                case 'sand_objet' :
                    className = 'tag todo';
                    break;
                case '1' :
                    className = 'tag done';
                    break;
                case '0' :
                    className = 'to do';

            }
            return className;
        },

        texteFromValue(val) {

            let texte = '';
            switch (val) {
                case 'a_faire':
                    texte = 'À faire';
                    break;
                case 'en_cours':
                    texte = 'En cours';
                    break;
                case 'fait' :
                    texte = 'Fait';
                    break;
                case 'sans_objet' :
                    texte = 'Sans objet';
                    break;
                case '1' :
                    texte = 'Publié';
                    break;
                case '0' :
                    texte = 'Non publié';

            }
            return texte;
        },

        async setAs(actionColumn,value){

            try{
                const isChecked = this.checkedRows.rows.some(row => row.id === this.rowData.id);
                if(isChecked){

                    const response = await ListService.setAs(actionColumn,value,this.rowData.id);
                    console.log('^^^^^^^^^^^^^^^^^^^')
                    console.log(response);
                } else {
                    alert('Merci de sélectionné une ligne avant de pouvoir éffectué cette action');
                }
            } catch (e) {
                console.log(e);
            }


        }


    }
}
</script>

<style scoped lang="scss">
.list-row {

    span.tag {
        margin: 0 8px 8px 0;
        padding: 4px 8px;
        border-radius: 4px;
        color: #080C12;
        height: fit-content;
        background: #F2F2F3;
        box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07),
        0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);

        &.done {
            background: #DFF5E9;
        }

        &.todo {
            color: #ACB1B9;
        }

        &.inprogress {

            background: #FFFBDB;
        }
    }

    span.list-td-label,
    span.list-td-subject {
        cursor: pointer;
        transition: all .3s;

        &:hover {
            color: #20835F;
        }
    }
}

tr.list-row td {
    border-left: 0;
    border-right: 0;
    font-size: 12px;
    padding: 0.85rem 0.5rem;
}

.list-row:hover {
    background: #F2F2F3;
}
</style>

