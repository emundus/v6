/* jshint esversion: 8 */
import client from './axiosClient';

export default {
    async getDecisionFormUrl(fnum) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=decision&task=getDecisionFormUrl&fnum=' + fnum);

            return response.data;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    }
}