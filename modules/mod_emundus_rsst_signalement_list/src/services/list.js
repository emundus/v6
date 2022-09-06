import client from './axiosClient';


export default {

    async getListAndDataContains(listId,listParticularConditionalColumn, listParticularConditionalColumnValues) {
        try {
            const formData = new FormData();
            formData.append('listId',parseInt(listId));
            formData.append('listParticularConditionalColumn',JSON.stringify(listParticularConditionalColumn));
            formData.append('listParticularConditionalColumnValues',JSON.stringify(listParticularConditionalColumnValues));
            const response = await client().post('index.php?option=com_emundus&controller=list&task=getList',formData);

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getListActionAndDataContains(listId,actionColumnId) {
        try {
            const formData = new FormData();
            formData.append('listId',parseInt(listId));
            formData.append('listActionColumnId',parseInt(actionColumnId));
            const response = await client().post('index.php?option=com_emundus&controller=list&task=getListActions',formData);

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async setAs(actionConlumn,value,rowId){
        try {

            const formData = new FormData();
            formData.append('row_id',rowId);
            formData.append('column_name',actionConlumn.column_name);
            formData.append('db_table_name',actionConlumn.db_table_name);
            formData.append('value',value);
            const response = await client().post('index.php?option=com_emundus&controller=list&task=actionSetColumnValueAs',formData);


            return response.data;

        } catch (e){
            console.log(e);
        }
    }

};
