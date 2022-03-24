import client from './axiosClient';

export default {
    async saveWidget(config,type) {
        try {
            const formData = new FormData();
            formData.append('config', JSON.stringify(config));
            formData.append('type', type);

            return await client().post(`index.php?option=com_emundus&controller=sync&task=saveconfig`,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
            );
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },

    async getWidgets() {
        try {
            return await client().get(`index.php?option=com_emundus&controller=dashboard&task=getallwidgets`);
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
};
