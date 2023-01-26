<template>

</template>

<script>
export default {
  name: "Comments",
  props: {
    user: {
      type: String,
      required: true,
    },
    fnum: {
      type: String,
      required: true,
    },
    access: {
      type: Array,
      required: true,
    },
  },
}
</script>

<style scoped>

</style>