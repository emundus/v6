/* jshint esversion: 8 */
import client from './axiosClient';

export default {
    async getAdmissionFormUrl(fnum) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=admission&task=getAdmissionFormUrl&fnum=' + fnum);

            return response.data;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    }
}