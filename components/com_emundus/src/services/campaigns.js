/* jshint esversion: 8 */
import client from './axiosClient';
export default {
    async getAllCampaigns(filter = '',sort = 'DESC',recherche = '',lim = 9999,page = 0,program = 'all') {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=campaign&task=getallcampaign', {
                params: {
                    filter: filter,
                    sort: sort,
                    recherche: recherche,
                    lim: lim,
                    page: page,
                    program: program,
                }
            });

            return response.data;
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
}
