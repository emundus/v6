import client from './axiosClient';

export default {
    async getListAndDataContains(listId) {
        try {
            const formData = new FormData();
            formData.append('listId',parseInt(listId));
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
    }

};
