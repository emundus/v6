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

    async deleteAttachments(uploadIds) {
        try {
            const formData = new FormData();
            formData.append('upload_ids', JSON.stringify(uploadIds));

            const response = await client().post(
                'index.php?option=com_emundus&controller=sync&task=deleteattachments',
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
    async checkAttachmentsExists(uploadIds) {
        try {
           const formData = new FormData();
           formData.append('upload_ids', JSON.stringify(uploadIds));

           const response = await client().post(
               'index.php?option=com_emundus&controller=sync&task=checkattachmentsexists',
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

    async getAttachmentAspectsConfig(attachmentId) {
        try {
            const response = await client().get(`index.php?option=com_emundus&controller=sync&task=getattachmentaspectsconfig`, {
                params: {
                    attachmentId: attachmentId
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

    saveAttachmentAspectsConfig(attachmentId, config) {
        try {
            const formData = new FormData();
            formData.append('attachmentId', attachmentId);
            formData.append('config', JSON.stringify(config));

            return client().post(`index.php?option=com_emundus&controller=sync&task=saveattachmentaspectsconfig`,
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

    async getAspects() {
        try {
            return await client().get(`index.php?option=com_emundus&controller=sync&task=getaspects`);
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
    async uploadAspectFile(file) {
        try {
            const formData = new FormData();
            formData.append('file', file);

            return await client().post(`index.php?option=com_emundus&controller=sync&task=uploadaspectfile`,
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

    async updateAspectListFromFile(file) {
        try {
            const formData = new FormData();
            formData.append('file', file);

            return await client().post(`index.php?option=com_emundus&controller=sync&task=updateaspectlistfromfile`,
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

    async getEmundusTags(){
        try {
            return await client().get(`index.php?option=com_emundus&controller=sync&task=getemundustags`);
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

    async getSetupTags() {
        try {
            const response = await client().get(`index.php?option=com_emundus&controller=sync&task=getsetuptags`);

            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },

    async getAttachmentSyncNodeId(uploadId) {
        let nodeId = null;

        if (typeof uploadId != 'undefined' && uploadId !== null) {
            try {
                const response = await client().get(`index.php?option=com_emundus&controller=sync&task=getnodeid`, {
                    params: {
                        uploadId: uploadId
                    }
                });

                if (response.status && response.data.status) {
                    nodeId = response.data.data;
                }
            } catch (e) {
                console.log('Error occurred trying to get upload sync node id ' + uploadId);
            }
        }

        return nodeId;
    }
};