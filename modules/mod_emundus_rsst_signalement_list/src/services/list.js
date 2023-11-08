import client from './axiosClient';


export default {
    async getListAndDataContains(listId, listParticularConditionalColumn, listParticularConditionalColumnValues) {
        const formData = new FormData();
        formData.append('listId', parseInt(listId));
        formData.append('listParticularConditionalColumn', JSON.stringify(listParticularConditionalColumn));
        formData.append('listParticularConditionalColumnValues', JSON.stringify(listParticularConditionalColumnValues));

        try {
            const response = await client().post('index.php?option=com_emundus&controller=list&task=getList', formData);

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getListActionAndDataContains(listId, actionColumnId) {
        const formData = new FormData();
        formData.append('listId', parseInt(listId));
        formData.append('listActionColumnId', parseInt(actionColumnId));

        try {
            const response = await client().post('index.php?option=com_emundus&controller=list&task=getListActions', formData);

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async setAs(actionConlumn, value, rowId) {
        const formData = new FormData();
        formData.append('row_id', rowId);
        formData.append('column_name', actionConlumn.column_name);
        formData.append('db_table_name', actionConlumn.db_table_name);
        formData.append('value', value);

        try {
            const response = await client().post('index.php?option=com_emundus&controller=list&task=actionSetColumnValueAs', formData);
            return response.data;
        } catch (e) {
            console.log(e);
        }
    },

    async updateActionState(newValue, rows) {
        const formData = new FormData();
        formData.append('newValue', JSON.stringify(newValue));
        formData.append('rows', JSON.stringify(rows));

        try {
            const response = await client().post('index.php?option=com_emundus&controller=list&task=updateActionState', formData);
            return response.data;
        } catch (error) {
            return {
                status: false,
                msg: error
            };
        }
    }
};
