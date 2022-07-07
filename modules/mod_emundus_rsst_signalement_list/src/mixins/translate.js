export default {
  

    methods: {
        translate(key) {
            if (typeof key != undefined && key != null) {
                return Joomla.JText._(key) ? Joomla.JText._(key) : key;
            } else {
                return '';
            }
        },
    }
};
