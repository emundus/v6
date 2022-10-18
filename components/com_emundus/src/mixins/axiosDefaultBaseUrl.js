export default {
    created() {
        if (typeof axios != 'undefined' && axios !== null) {
            const SystemPath = typeof Joomla != undefined && Joomla !== null ? Joomla.getOptions('system.paths') : {base: ''};
            axios.defaults.baseURL = SystemPath.base !== undefined && SystemPath.base !== '' ? SystemPath.base : '/';
        }
    }
};