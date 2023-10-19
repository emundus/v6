/* jshint esversion: 8 */
import client from './axiosClient';

export default {
    async getFiles(type = 'default', refresh = false, limit = 25, page = 0) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getfiles', {
                params: {
                    type: type,
                    refresh: refresh
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getColumns(type = 'default') {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getcolumns', {
                params: {
                    type: type
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getEvaluationFormByFnum(fnum,type) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getevaluationformbyfnum', {
                params: {
                    fnum: fnum,
                    type: type,
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getMyEvaluation(fnum) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getmyevaluation', {
                params: {
                    fnum: fnum
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async checkAccess(fnum){
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=checkaccess', {
                params: {
                    fnum: fnum
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getLimit(type = 'default'){
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getlimit', {
                params: {
                    type: type
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getPage(type = 'default'){
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getpage', {
                params: {
                    type: type
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async updateLimit(limit){
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=updatelimit', {
                params: {
                    limit: limit
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async updatePage(page){
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=updatepage', {
                params: {
                    page: page
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getSelectedTab(type){
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getselectedtab', {
                params: {
                    type: type
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async setSelectedTab(tab,type = 'evaluation'){
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=setselectedtab', {
                params: {
                    tab: tab,
                    type: type
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getFile(fnum,type = 'default') {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getfile', {
                params: {
                    fnum: fnum,
                    type: type,
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getFilters() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getfilters');
            return response.data;
        } catch (e) {
            return false;
        }
    },

    async applyFilters(filters) {
        const formData = new FormData();
        formData.append('filters', JSON.stringify(filters));

        try {
            const response = await client().post('index.php?option=com_emundus&controller=file&task=applyfilters', formData);
            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getComments(fnum){
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getcomments', {
                params: {
                    fnum: fnum
                }
            });

            // make sure that response.data.data is an array and that every element has id property
            if (Array.isArray(response.data.data)) {
                const correctResponse = response.data.data.every((comment) => {
                    if (!comment.hasOwnProperty('id')) {
                        return false;
                    }

                    return true;
                });

                if (correctResponse) {
                    return response.data;
                } else {
                    return {
                        data: [],
                        status: false,
                        msg: 'Invalid response'
                    };
                }
            } else {
                return {
                    data: [],
                    status: false,
                    msg: 'Invalid response'
                };
            }
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },

    async saveComment(fnum,comment){
        const formData = new FormData();
        formData.append('fnum', fnum);
        Object.keys(comment).forEach(key => {
            formData.append(key, comment[key]);
        });

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=file&task=savecomment',
                formData
            );

            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },

    async deleteComment(cid){
        try {
            const response = await client().delete('index.php?option=com_emundus&controller=file&task=deletecomment', {
                params: {
                    cid: cid,
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