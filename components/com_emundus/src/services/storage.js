import client from './axiosClient';

export default {
    async saveConfig(config,type) {
        try {
            const formData = new FormData();
            formData.append('config', JSON.stringify(config));
            formData.append('type', type);

            return await client().post(`index.php?option=com_emundus&controller=sync&task=saveconfig`,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
            );
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },

    async getConfig(type) {
        try {
            return await client().get(`index.php?option=com_emundus&controller=sync&task=getconfig`, {
                params: {
                    type,
                }
            });
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },

    async getDocuments(){
        try {
            return await client().get(`index.php?option=com_emundus&controller=sync&task=getdocuments`);
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },

    async updateSync(did,sync) {
        try {
            const formData = new FormData();
            formData.append('did', did);
            formData.append('sync', sync);

            return await client().post(`index.php?option=com_emundus&controller=sync&task=updatedocumentsync`,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
            );
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },

    async updateSyncMethod(did,sync_method) {
        try {
            const formData = new FormData();
            formData.append('did', did);
            formData.append('sync_method', sync_method);

            return await client().post(`index.php?option=com_emundus&controller=sync&task=updatedocumentsyncmethod`,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
            );
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
};
