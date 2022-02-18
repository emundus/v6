import { stringify } from 'qs';
import client from './axiosClient';
const qs = require("qs");

export default {

    async updateFormLabel(prid, label) {
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=form&task=updateformlabel", { label: label, prid: prid });
            return response;
        } catch (error) {
            return false;
        }

    },

    async pushMenu(menuId) {
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&view=form&formid=" + menuId + "&format=vue_jsonclean");
            return response.data;
        } catch (error) {
            return false;
        }

    },

    async getFilesByForm(prid) {
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getfilesbyform&pid=" + prid)
            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getSubmissionPage(prid) {
        try {
            const responseLink = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getsubmittionpage", {
                params: {
                    prid: prid
                }
            })

            const link = responseLink.data.link.replace("fabrik", "emundus_onboard");
            const responseVueJsonClean = await client().get(link + "&format=vue_jsonclean");

            const response = {
                link: responseLink.data.link,
                rgt: responseLink.data.rgt,
                responseAfterVueJsonClean: responseVueJsonClean
            }

            return response;

        } catch (e) {
            return false;
        }
    },

    async getFormsByProfileId(profile_id) {
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getFormsByProfileId", { params: { profile_id: profile_id } });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getDocuments(pid) {
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getDocuments", { params: { pid: pid } });

            return response.data;

        } catch (e) {
            return false;
        }
    },

    async removeDocumentFromProfile(docId) {
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=form&task=removeDocumentFromProfile", { did: docId });

            return response;
        } catch (e) {
            return false;
        }
    },

    async getProfilelabelByProfileId(profile_id) {
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getProfileLabelByProfileId", { params: { profile_id: profile_id } });

            return response.data;
        } catch (e) {
            return false;
        }
    },

    async getAssociatedCampaign(pid) {
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=form&task=getassociatedcampaign", { params: { pid: pid } });

            return response.data;

        } catch (e) {
            return false;
        }
    },

    async reorderDocument(documents) {
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=form&task=reorderDocuments", { documents: documents }
            );
            return response;
        } catch (error) {
            return false;
        }

    }


}