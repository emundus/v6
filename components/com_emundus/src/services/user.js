import client from './axiosClient';

export default {
    async getUsers() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getusers');

            return response.data;
        } catch (e) {
            throw e;
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