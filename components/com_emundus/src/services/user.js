import client from './axiosClient';

export default {
    async getUserInformations() 
    {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=users&task=getuser');

            console.log(response);
            return response.data;
        } catch (e) {
            throw new Error(e);
        }
    }
}