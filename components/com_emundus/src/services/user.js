import client from './axiosClient';

export default {
    async getUsers() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getusers');

            return {
                data: response.data,
                status: true
            };
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
    async getUserById(id) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getuserbyid', {
                params: {
                    id: id
                }
            });

            return response.data;
        } catch (e) {
            throw new Error(e);
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
            console.log(e);
        }
    }
}