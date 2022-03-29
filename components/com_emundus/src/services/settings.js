import client from './axiosClient';

export default {
    async getActiveLanguages() {
        try {
            return await client().get('index.php?option=com_emundus&controller=settings&task=getactivelanguages');
        } catch (e) {
            return {
                status: false,
                error: e
            };
        }
    }
}