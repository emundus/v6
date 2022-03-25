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
    /**
     *
     * @param {number} uploadId
     * @returns {Promise<{msg, status: boolean}|any>}
     */
    async getSynchronizeState(uploadId) {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=sync&task=getsynchronizestate',
                {
                    params: {
                        'upload_id': uploadId
                    }
                }
            );

            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
    /**
     * @param {Array<number>} uploadIds
     * @returns {Promise<{status: boolean, msg: string}>}
     */
    async synchronizeAttachments(uploadIds) {
        try {
            const formData = new FormData();
            formData.append('upload_ids', JSON.stringify(uploadIds));

            const response = await client().post(
                'index.php?option=com_emundus&controller=sync&task=synchronizeattachments',
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
            );

            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
};