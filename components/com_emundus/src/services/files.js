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

    async getEvaluationFormByFnum(fnum) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getevaluationformbyfnum', {
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

    async getFile(fnum){
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getfile', {
                params: {
                    fnum: fnum
                }
            });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getDefaultFilters() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=file&task=getdefaultfilters');
            return response.data;
        } catch (e) {
            return false;
        }
    },

    async applyFilters(filters, tab) {
        const formData = new FormData();
        formData.append('filters', JSON.stringify(filters));
        formData.append('tab', tab);

        try {
            const response = await client().post('index.php?option=com_emundus&controller=file&task=applyfilters', formData);
            return response.data;
        } catch (e) {
            return false;
        }
    }
};