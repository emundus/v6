import { stringify } from 'qs';
import client from './axiosClient';
const qs = require("qs");

export default {

    async updateFormLabel(prid, label){
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=form&task=updateformlabel",
                qs.stringify({
                    label: label,
                     prid: prid,
                })
            );
            return response;
        } catch (error) {
            return false;
        }
        
    },

    async getFilesByForm(prid){
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getfilesbyform&pid="+prid)
            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getSubmissionPage(prid){
        try {
            const responseLink = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getsubmittionpage",{
                params: qs.stringify({
                    prid: prid
                })
            })

            const link = responseLink.data.link.replace("fabrik","emundus_onboard");
            const responseVueJsonClean = await client().get(link+"&format=vue_jsonclean");

            const response = {
                responseAfterGettingSubmissionPage: responseLink,
                responseAfterVueJsonClean: responseVueJsonClean
            }

            return response;

        } catch (e) {
            return false;
        }
    },

    async getFormsByProfileId(profile_id){
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getFormsByProfileId",
                {
                    params: qs.stringify({
                        profile_id: profile_id
                        }
                    )
                }
            );

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getDocuments(pid){
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getDocuments",
                {
                    params: qs.stringify({
                        pid: pid
                        }
                    )
                }
            );

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async removeDocumentFromProfile(docId){
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=form&task=removeDocumentFromProfile",
                qs.stringify({
                    did: docId
                })
            );

            return response;
        } catch (e) {
            return false;
        }
    },

    async getProfilelabelByProfileId(profile_id){
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getProfileLabelByProfileId",
                {
                    params: qs.stringify({
                        profile_id: profile_id
                        }
                    )
                }
            );

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getAssociatedCampaign(pid){
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getassociatedcampaign",
                {
                    params: qs.stringify({
                        pid: pid
                        }
                    )
                }
            );

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async reorderDocument(documents){
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=form&task=reorderDocuments",
                qs.stringify({
                    documents: documents,
                })
            );
            return response;
        } catch (error) {
            return false;
        }

    }


}