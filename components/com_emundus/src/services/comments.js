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
    },
    async addComment(ccid, comment, target, visible_to_applicant = 0) {
        if (ccid > 0 && comment.length > 0) {
            try {
                const formData = new FormData();
                formData.append('ccid', ccid);
                formData.append('comment', comment);
                formData.append('target', JSON.stringify(target));
                formData.append('visible_to_applicant', visible_to_applicant);

                const response = await client().post('index.php?option=com_emundus&controller=comments&task=addcomment', formData, {
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
    }
};