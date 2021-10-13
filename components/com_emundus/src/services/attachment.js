import client from './axiosClient';

export default {
    async getAttachments() {
        try {
            const response = await client.get('index.php?');
            console.log(response);
            return [];
        } catch (e) {
            console.error(e);
        }
    }
}