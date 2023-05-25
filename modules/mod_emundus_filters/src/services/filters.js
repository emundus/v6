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
    }
};