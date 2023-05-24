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
        if (this.$store !== null && typeof this.$store !== 'undefined') {
            this.shortDefaultLang = this.$store.getters['global/defaultLang'].substring(0, 2);
        }
    },
    methods: {
        translate(key) {
            if (typeof key != undefined && key != null && Joomla !== null && typeof Joomla !== 'undefined') {
                return Joomla.JText._(key) ? Joomla.JText._(key) : key;
            } else {
                return '';
            }
        },
    }
};
