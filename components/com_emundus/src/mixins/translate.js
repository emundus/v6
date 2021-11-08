export default {
    methods: {
        translate(key) {
            return Joomla.JText._(key) ? Joomla.JText._(key) : key;
        },
    }
}