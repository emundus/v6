export default {
    data() {
        return {
            shortDefaultLang: 'fr'
        };
    },
    beforeMount() {
        if (this.$data.translations !== null && typeof this.$data.translations !== 'undefined') {
            Object.entries(this.$data.translations).forEach(([key, value]) => {
                this.$data.translations[key] = this.translate(value);
            });
        }
    },
    mounted() {
        this.shortDefaultLang = this.$store.getters['global/defaultLang'].substring(0, 2);
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
