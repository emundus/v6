import client from './axiosClient';

export default {
    async isSyncModuleActive() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=sync&task=issyncmoduleactive');

            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
    async getSyncType(uploadId) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=sync&task=getsynctype', {
                params: {
                    'upload_id': uploadId
                }
            });

            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
};