/* jshint esversion: 8 */
import client from './axiosClient';

export default {
    async updateDocument(params, create = false) {
        const formData = new FormData();
        Object.keys(params).forEach(key => {
            formData.append(key, params[key]);
        });

        const task = create ? 'createdocument' : 'updatedocument';

        try {
            const response = await client().post('index.php?option=com_emundus&controller=campaign&task=' + task, formData);

            return response.data;
        } catch (e) {
            return {
                status: false, msg: e.message
            };
        }
    },

    async setDocumentMandatory(params) {
        const formData = new FormData();
        Object.keys(params).forEach(key => {
            formData.append(key, params[key]);
        });

        try {
            const response = await client().post('index.php?option=com_emundus&controller=campaign&task=updatedocumentmandatory', formData);

            return response.data;
        } catch (e) {
            return {
                status: false, msg: e.message
            };
        }
    },

    async getAllCampaigns(filter = '', sort = 'DESC', recherche = '', lim = 9999, page = 0, program = 'all') {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=campaign&task=getallcampaign', {
                params: {
                    filter: filter, sort: sort, recherche: recherche, lim: lim, page: page, program: program,
                }
            });

            return response.data;
        } catch (e) {
            return {
                status: false, msg: e.message
            };
        }
    },

    async createCampaign(form) {
        // label, start_date and end_date are required
        if (!form.label || !form.start_date || !form.end_date) {
            return {
                status: false, msg: 'Label, start date and end date are required'
            };
        }

        try {
            const formData = new FormData();
            formData.append('label', JSON.stringify(form.label));
            formData.append('start_date', form.start_date);
            formData.append('end_date', form.end_date);
            formData.append('short_description', form.short_description);
            formData.append('description', form.description);
            formData.append('training', form.training);
            formData.append('year', form.year);
            formData.append('published', form.published);
            formData.append('is_limited', form.is_limited);
            formData.append('profile_id', form.profile_id);
            formData.append('limit', form.limit);
            formData.append('limit_status', form.limit_status);

            return await client().post(`index.php?option=com_emundus&controller=campaign&task=createcampaign`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
        } catch (e) {
            return {
                status: false, msg: e.message
            };
        }
    },

    async pinCampaign(cid) {
        // cid must be an integer
        if (cid < 1) {
            return {
                status: false, msg: 'Invalid campaign ID'
            };
        }

        try {
            const formData = new FormData();
            formData.append('cid', cid);

            return await client().post(`index.php?option=com_emundus&controller=campaign&task=pincampaign`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
        } catch (e) {
            return {
                status: false, msg: e.message
            };
        }
    },

    async getCampaignMoreFormUrl(cid) {
        if (cid < 1) {
            return {
                status: false, msg: 'Invalid campaign ID'
            };
        }

        try {
            const response = await client().get(`/index.php?option=com_emundus&controller=campaign&task=getcampaignmoreformurl&cid=${cid}`);

            return response.data;
        } catch (e) {
            return {
                status: false, msg: e.message
            };
        }
    }
};
