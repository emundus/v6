import client from './axiosClient';
export default {
    async getMyRanking() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=ranking&task=getMyFilesToRank');
            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    }
};