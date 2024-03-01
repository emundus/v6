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
    },
    async updateRanking(id, rank, hierarchy_id) {
        try {
            const response = await client().post('index.php?option=com_emundus&controller=ranking&task=updateFileRanking', {
                id,
                rank,
                hierarchy_id
            });
            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    }
};