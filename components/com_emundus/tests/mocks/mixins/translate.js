export default {
    beforeMount() {
        if (this.$data.translations !== null && typeof this.$data.translations !== 'undefined') {
            Object.entries(this.$data.translations).forEach(([key, value]) => {
                this.$data.translations[key] = this.translate(value);
            });
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