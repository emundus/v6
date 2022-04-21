import moment from 'moment';

export default {
    methods: {
        updateLastSave() {
            moment.locale('fr');
            this.$store.dispatch('formBuilder/updateLastSave', moment().format('LT'));
        }
    }
};
