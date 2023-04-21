import client from './axiosClient';

export default {
    async getUserById(id) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getuserbyid', {
                params: {
                    id: id
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
    async getUserNameById(id) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getUserNameById', {
                params: {
                    id: id
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
    async getAccessRights(id, fnum) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getattachmentaccessrights', {
                params: {
                    id: id,
                    fnum: fnum
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
    async getProfileForm() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getprofileform');

            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
    async getProfileGroups(formid) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getprofilegroups', {
                params: {
                    formid: formid,
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
    async getProfileElements(groupid) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getprofileelements', {
                params: {
                    groupid: groupid,
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
    async saveUser(user) {
        try {
            return await client().post(`index.php?option=com_emundus&controller=users&task=saveuser`,
                JSON.stringify(user),
                {
                    headers: {
                        'Content-Type': 'application/json'
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

    async updateProfilePicture(file) {
        try {
            const formData = new FormData();
            formData.append("file", file);

            return await client().post(`index.php?option=com_emundus&controller=users&task=updateprofilepicture`,
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
    }
};
