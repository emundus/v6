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
    async getCurrentUser() 
    {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getuser');

            return response.data;
        } catch (e) {
            throw new Error(e);
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
    }
}