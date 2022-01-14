import client from './axiosClient';

export default {
    async getFnums() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=files&task=getallfnums');

            return response.data;
        } catch (e) {
            console.error(e);
        }
    },

    async getFnumInfos(fnum) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=files&task=getfnuminfos',  {
                params: {
                    fnum
                }
            });

            return response.data;
        } catch (e) {
            console.log(e);
        }
    }
}