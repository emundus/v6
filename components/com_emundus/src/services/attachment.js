import client from './axiosClient';

export default {
    async getAttachments() {
        try {
            const response = await client.get('index.php?');
            return response.data;
        } catch (e) {
            console.error(e);
        }
    }
}