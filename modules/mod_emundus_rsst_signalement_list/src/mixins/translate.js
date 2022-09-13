export default {
    methods: {
        translate(key) {
            if (typeof key != undefined && key != null) {
                return Joomla.JText._(key.toUpperCase()) ? Joomla.JText._(key.toUpperCase()) : key;
            } else {
                return '';
            }
        },
    }
};
