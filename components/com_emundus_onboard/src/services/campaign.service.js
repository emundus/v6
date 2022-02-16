import { stringify } from 'qs';
import client from './axiosClient';
const qs = require("qs");

export default {

    async redirectjRoute(link) {
        try {
            const response = await client().post("index.php?option=com_emundus_onboard&controller=campaign&task=updatedocument", {
                params:qs.stringify({
                    link:link
                })
            }
            );
            return response.data;
        } catch (e) {
            return false;
        }
    },
}