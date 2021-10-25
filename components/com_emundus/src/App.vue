<template>
    <div class="com_emundus_vue">
        <router-view></router-view>
    </div>
</template>

<script>
import moment from 'moment';

export default {
    props: {
        componentName: {
            type: String,
            required: true
        },
        data: {
            type: Object,
            default: {}
        }
    },
    mounted() {
        if (this.data.lang) {
            this.$store.dispatch('global/setLang', this.data.lang.split('-')[0]);
        } else {
            this.$store.dispatch('global/setLang', 'fr');
        }
        
        moment.locale(this.$store.state.global.lang);

        this.$router.push({
            name: this.componentName,
            params: this.data
        });
    }
}
</script>

<style lang="scss">
@import url('./assets/css/main.scss');
</style>