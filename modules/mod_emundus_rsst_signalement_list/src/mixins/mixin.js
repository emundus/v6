import moment from 'moment';

export default {

    methods: {
        formattedDate: function (date = '', format = 'LLLL') {
            let formattedDate = '';

            if (date !== null) {
                if (date !== '') {
                    formattedDate = moment(date).format(format);
                } else {
                    formattedDate = moment().format(format);
                }
            }

            return formattedDate;
        },

    }

};

