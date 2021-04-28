export const commonMixin = {
    data: function() {
        return { id: '',}
    },

    created() { this.id = window.location.href.split('id=')[1]; },
}

export default commonMixin