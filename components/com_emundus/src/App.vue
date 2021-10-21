<template>
    <div class="com_emundus_vue">
        <router-view></router-view>
    </div>
</template>

<script>

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