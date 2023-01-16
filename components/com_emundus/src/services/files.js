import client from './axiosClient';

export default {
    async getFiles(type = 'default') {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getfiles', {
                params: {
                    type: type
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getColumns(type = 'default') {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getcolumns', {
                params: {
                    type: type
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    }
}