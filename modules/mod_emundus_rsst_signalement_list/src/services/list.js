import client from './axiosClient';

export default {
    async getListAndDataContains() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=list&task=getList');

            return response.data;
        } catch (e) {
            return false;
        }
    }

};
