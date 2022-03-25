import client from './axiosClient';

export default {
    async updateDocument(params) {
        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=campaign&task=updatedocument',
                params
            );

            return response.data;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
};