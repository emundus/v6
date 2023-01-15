import client from './axiosClient';

export default {
    async getFilesToEvaluate() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getfilestoevaluate');

            return response.data;
        } catch (e) {
            return false;
        }
    },
}