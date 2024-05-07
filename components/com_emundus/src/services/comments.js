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
    async addComment(ccid, comment, target, visible_to_applicant = false, parent_id = 0) {
        if (ccid > 0 && comment.length > 0) {
            try {
                visible_to_applicant = visible_to_applicant ? 1 : 0;

                const formData = new FormData();
                formData.append('ccid', ccid);
                formData.append('comment', comment);
                formData.append('target', JSON.stringify(target));
                formData.append('visible_to_applicant', visible_to_applicant);
                formData.append('parent_id', parent_id);

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
        } else {
            return {
                status: false,
                msg: 'Invalid data'
            };
        }
    },
    async deleteComment(comment_id) {
        if (comment_id > 0) {
            try {
                const formData = new FormData();
                formData.append('comment_id', comment_id);

                const response = await client().post('index.php?option=com_emundus&controller=comments&task=deletecomment', formData, {
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
        } else {
            return {
                status: false,
                msg: 'Invalid data'
            };
        }
    },
    async getTargetableElements(ccid) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=comments&task=gettargetableelements&ccid=' + ccid);
            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    }
};