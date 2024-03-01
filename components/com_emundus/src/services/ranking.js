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
            const Form = new FormData();
            Form.append('id', id);
            Form.append('rank', rank);
            Form.append('hierarchy_id', hierarchy_id);


            const response = await client().post('index.php?option=com_emundus&controller=ranking&task=updateFileRanking',
                Form,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
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
    async lockRanking(id, lock) {
        try {
            const Form = new FormData();
            Form.append('id', id);
            Form.append('lock', lock);

            const response = await client().post('index.php?option=com_emundus&controller=ranking&task=lockFilesOfHierarchyRanking',
                Form,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
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