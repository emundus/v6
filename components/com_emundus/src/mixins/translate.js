export default {
    beforeMount() {
        if (this.translations !== null && typeof this.translations !== "undefined") {
            Object.entries(this.translations).forEach(([key, value]) => {
                this.translations[key] = this.translate(value);
            });
        }
    },

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
