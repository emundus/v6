import client from './axiosClient';

export default {
    async getEmundusParams() {
        try {
            return await client().get('index.php?option=com_emundus&controller=settings&task=getemundusparams');
        } catch (e) {
            return false;
        }
    },
}
