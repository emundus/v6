export default {
    created() {
        if (typeof axios != 'undefined' && axios !== null) {
            const SystemPath = Joomla.getOptions('system.paths');
            axios.defaults.baseURL = SystemPath.base !== undefined && SystemPath.base !== '' ? SystemPath.base : '/';
        }
    }
};