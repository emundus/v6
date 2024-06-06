import {FetchClient} from './fetchClient.js';

const client = new FetchClient('files');

export default {
    async applyFilters(filters, search_filters, successEvent) {
        let applied = false;

        if (filters) {
            filters = JSON.parse(JSON.stringify(filters));
            filters = filters.map(filter => {
                delete filter.values;
                return filter;
            });

            return client.post('applyfilters', {
                filters: JSON.stringify(filters),
                search_filters: JSON.stringify(search_filters)
            }).then(data => {
                if (data.status) {
                    applied = true;

                    window.dispatchEvent(successEvent);
                    return applied;
                }
            });
        } else {
            return applied;
        }
    },
    async saveFilters(filters, name, moduleId) {
        let saved = false;

        if (filters && name.length > 0) {
            return client.post('newsavefilters', {
                filters:  JSON.stringify(filters),
                name: name,
                item_id: moduleId
            }).then(data => {
               if (data.status) {
                   saved = true;
               }

                return saved;
            }).catch((error) => {
                console.log(error);
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
    async deleteFilter(filterId) {
        let deleted = false;

        if (filterId) {
            client.delete('deletefilters', {id: filterId}).then(data => {
               if (data.status) {
                   deleted = true;
               }

                return deleted;
            });
        } else {
            return deleted;
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
    },
    async countFiltersValues(moduleId) {
        return client.post('setFiltersValuesAvailability', {
            module_id: moduleId
        }).then(data => {
            if (data.status) {
                return data;
            }
        }).catch((error) => {
            console.log(error);
            return {
                status: false,
                message: 'Error'
            };
        });
    },
    async getFilterValues(filterId) {
        let values = [];

        return client.get('getfiltervalues', {id: filterId}).then(data => {
            if (data.status) {
                values = data.data;
            }

            return values;
        }).catch(error => {
            console.log(error);
            return values;
        });
    },
    async getFiltersAvailable(moduleId) {
        let filters = [];

        if (moduleId > 0) {
            return client.get('getFiltersAvailable', {
                module_id: moduleId
            }).then(data => {
                if (data.status) {
                    filters = data.data;
                }

                return filters;
            }).catch(error => {
                throw new Error('Error occured while getting filters : ' . error.message);
            });
        } else {
            throw new Error('Module id is not valid');
        }
    }
};