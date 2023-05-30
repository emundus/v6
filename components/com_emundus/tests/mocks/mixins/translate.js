export default {
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
            if (typeof key != undefined && key != null) {
                return key;
            } else {
                return '';
            }
        },
    }
}