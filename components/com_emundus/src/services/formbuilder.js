import client from './axiosClient';

export default {
    async createSimpleElement(params) {
        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=createsimpleelement',
                params
            );

            return response.data;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getElement(gid, element) {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=formbuilder&task=getElement', {
                params: {
                    gid: gid,
                    element: element
                }
            });

            return response.data;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async updateParams(element) {
      try {
        const response = await client().post('index.php?option=com_emundus&controller=formbuilder&task=updateparams',
            {
                element: element
            }
        );

        return response.data;
      } catch (e) {
        return {
          status: false,
            message: e.message
        };
      }
    },
};