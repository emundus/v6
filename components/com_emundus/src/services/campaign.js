import client from './axiosClient';

export default {
    async updateDocument(params) {
        const formData = new FormData();
        Object.keys(params).forEach(key => {
            formData.append(key, params[key]);
        });

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=campaign&task=updatedocument',
                formData
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