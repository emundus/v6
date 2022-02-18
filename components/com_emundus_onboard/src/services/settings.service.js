import { stringify } from 'qs';
import client from './axiosClient';
const qs = require("qs");

export default {

    async redirectjRoute(link) {
        try {
            const response = await client().get("index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute", { params:{ link:link } } );
            return response.data;
        } catch (e) {
            return false;
        }
    },
}