import { stringify } from 'qs';
import client from './axiosClient';
const qs = require("qs");

export default {

    async createElement(gid, plugin, attachmentId = null) {
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=formbuilder&task=createsimpleelement", 
            attachmentId == 0 ?
                qs.stringify( {
                    gid: gid,
                    plugin: plugin
                    })
                :  qs.stringify( {
                    gid: gid,
                    plugin: plugin,
                    attachementId: attachmentId
                    })
                );

            return response.data
        } catch (e) {
            return false
        }
    },

    async getElement(gid, element){
        try{
            const response = await client().get("index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement", { 
                params: {
                    element: element,
                    gid: gid
                }
            });
            return response;
        } catch(e){
            return false;
        }
    },

    async updateElementParams(element){
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=formbuilder&task=updateparams",qs.stringify({ element: element }));
            return response;
        } catch (e) {
            return false;
        }
    },

    async createGroup(fid){
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=formbuilder&task=createsimplegroup",
            qs.stringify({
                fid: fid
            }
            ));
            return response.data;
        } catch (e) {
            return e;
        }
    },

    async createTestingFile(campaignId){
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=formbuilder&task=createtestingfile",
                qs.stringify({
                    cid: campaignId
                })
            );
            return response.data;
        } catch (e) {
            return false;
        }
    },

    async reorderMenuItems(rgt,link){
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=formbuilder&task=reordermenu",
                qs.stringify({
                    rgt: rgt,
                    link: link
                })
            )
            return response;
            
        } catch (e) {
            return false;
        }

    },

}