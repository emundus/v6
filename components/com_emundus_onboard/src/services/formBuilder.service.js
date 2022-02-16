import client from './axiosClient';
const qs = require("qs");

export default {

    async createElement(gid, plugin) {
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=formbuilder&task=createsimpleelement", 
                qs.stringify({
                    gid: gid,
                    plugin: plugin
                  }
            ));
            return response.data
        } catch (e) {
            return false
        }
    },

    async getElement(gid, element){
        try{
            const response = await client.get("index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement", { params:
                qs.stringify({
                    element: element,
                    gid: gid
                })
            });
            return response.data;
        } catch(e){
            return false;
        }
    },

    async updateElementParams(element){
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=formbuilder&task=updateparams",{ params:
                qs.stringify({
                    element: element
                })
            });
            return response.data;
        } catch (e) {
            return false;
        }
    }
}