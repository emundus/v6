import axios from 'axios';

export const commonMixin = {
    data: function() {
        return {
            id: '',
        }
    },

    created() {
        this.id = this.getURLParams('id');
    },

    methods: {
        getURLParams: function(_param) {
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            return urlParams.get(_param);
        },
    }
}

export default commonMixin