import { stringify } from 'qs';
import client from './axiosClient';
const qs = require("qs");

export default {

    async updateDocument(data) {

        try {

            const response = await client().post("index.php?option=com_emundus_onboard&controller=campaign&task=updatedocument",
               qs.stringify({
                    data:data
                })
            );

            return response.data;

        } catch (e) {

            return false;
            
        }
    },
}