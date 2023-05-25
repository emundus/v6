import {FetchClient} from './fetchClient.js';

const client = new FetchClient('files');

export default {
    async applyFilters(filters) {
        let applied = false;

        if (filters) {
            client.post('applyfilters', {filters:  JSON.stringify(filters)}).then(data => {
               if (data.status) {
                   applied = true;
                   window.location.reload();
               }
            });
        }

        return applied;
    },
    async saveFilters(filters, name, moduleId) {
        let saved = false;

        if (filters && name.length > 0) {
            client.post('newsavefilters', {filters:  JSON.stringify(filters),  name: name, item_id: moduleId}).then(data => {
               if (data.status) {
                   saved = true;
               }

                return saved;
            });
        } else {
            return saved;
        }
    },
    async getRegisteredFilters() {
        let filters = [];

        return client.get('getregisteredfilters').then(data => {
            if (data.status) {
                filters = data.data;
            }

            return filters;
        }).catch(error => {
            console.log(error);
            return filters;
        });
    }
};