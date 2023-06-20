export default {
    methods: {
        translate(key) {
            return key.length > 0 && Joomla.JText._(key) ? Joomla.JText._(key) : key;
        },
    },
}