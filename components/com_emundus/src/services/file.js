/* jshint esversion: 8 */
import client from './axiosClient';

export default {
    async getFnums() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=files&task=getallfnums');

            return response.data;
        } catch (e) {
            return false;
        }
    },
    async getFnumInfos(fnum) {
        if (fnum) {
            try {
                const response = await client().get('index.php?option=com_emundus&controller=files&task=getfnuminfos', {
                    params: {
                        fnum: fnum
                    }
                });

                return response.data;
            } catch (e) {
                return false;
            }
        } else {
            return {
                status: false,
                message: 'Fnum is required'
            };
        }
    },
    async isDataAnonymized() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=files&task=isdataanonymized');

            return response.data;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getAllStatus() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=files&task=getstate');

            return response.data;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    }
}