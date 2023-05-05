/* jshint esversion: 8 */
import client from './axiosClient';

export default {
    async getActiveLanguages() {
        try {
            return await client().get('index.php?option=com_emundus&controller=settings&task=getactivelanguages');
        } catch (e) {
            return {
                status: false,
                error: e
            };
        }
    },
    async removeParameter(param) {
        try {
            const response = await client().post(
            'index.php?option=com_emundus&controller=settings&task=removeparam',
            {
                param: param
            });

            return response;
        } catch (e) {
            return {
                status: false,
                error: e
            };
        }
    },
    async checkFirstDatabaseJoin() {
        try {
            const response = await client().get('index.php?option=com_emundus&controller=settings&task=checkfirstdatabasejoin');

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getEmundusParams() {
        try {
            return await client().get('index.php?option=com_emundus&controller=settings&task=getemundusparams');
        } catch (e) {
            return false;
        }
    },

    async getOffset() {
        try {
            return await client().get('index.php?option=com_emundus&controller=settings&task=getOffset');
        } catch (e) {
            return false;
        }
    }
};
