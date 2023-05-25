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
            client.post('newsavefilters', {
                filters:  JSON.stringify(filters),
                name: name,
                item_id: moduleId
            }).then(data => {
               if (data.status) {
                   saved = true;
               }

                return saved;
            });
        } else {
            return saved;
        }
    },
    async updateFilter(filters, moduleId, filterId) {
        let updated = false;

        if (filters) {
            client.post('updatefilter', {
                filters:  JSON.stringify(filters),
                item_id: moduleId,
                id: filterId
            }).then(data => {
               if (data.status) {
                   updated = true;
               }

                return updated;
            });
        } else {
            return updated;
        }
    },
    async getRegisteredFilters(moduleId) {
        let filters = [];

        return client.get('getsavedfilters', {item_id: moduleId}).then(data => {
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