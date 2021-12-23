import client from './axiosClient';

export default {
    async getFilters(type, id, otherids) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=application&task=getfilters', {
                params: {
                    type,
                    id,
                    otherids
                },
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },
    async mountQuery(id, filters) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=application&task=mountquery', {
                params: {
                    id: id,
                    filters: filters
                },
            });

            return response.data;
        } catch (e) {
            return false;
        }
    }
}