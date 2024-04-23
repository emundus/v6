/* jshint esversion: 8 */
import client from './axiosClient';

export default {
    async getComments(ccid) {
        if (ccid > 0) {
            try {
                const response = await client().get(`index.php?option=com_emundus&controller=comments&task=getcomments&ccid=${ccid}`);
                return response.data;
            } catch (e) {
                return {
                    status: false,
                    msg: e.message
                };
            }
        }
    }
};