import client from './axiosClient';

export default {
    async saveWidget(widget) {
        try {
            const formData = this.getObjectFormData(widget);

            return await client().post(`index.php?option=com_emundus&controller=dashboard&task=savewidget`,
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

    async deleteWidget(widget){
        try {
            return await client().delete(`index.php?option=com_emundus&controller=dashboard&task=deletewidget`, {
                params: {
                    id: widget
                }
            });
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

    async renderPreview(code) {
        try {
            const formData = new FormData();

            formData.append('code', code);
            return await client().post(`index.php?option=com_emundus&controller=dashboard&task=getevalcode`,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },

    async getDefaultDashboard(profile){
        try {
            return await client().get(`index.php?option=com_emundus&controller=dashboard&task=getdefaultdashboard`, {
                params: {
                    profile: profile
                }
            });
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },

    create_UUID(){
        var dt = new Date().getTime();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            var r = (dt + Math.random()*16)%16 | 0;
            dt = Math.floor(dt/16);
            return (c=='x' ? r :(r&0x3|0x8)).toString(16);
        });
    },

    getObjectFormData(object) {
        const formData = new FormData();
        Object.keys(object).forEach(key => formData.append(key, object[key]));
        return formData;
    }
};
