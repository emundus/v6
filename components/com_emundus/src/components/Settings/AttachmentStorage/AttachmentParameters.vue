<template>
  <div class="em-container">
    <div class="em-h4"> {{ attachment.value }} </div>
    <AttachmentAspectsMapping :attachment="attachment" v-if="attachment.sync == 1"></AttachmentAspectsMapping>
  </div>
</template>

<script>
import AttachmentAspectsMapping from "./GED/AttachmentAspectsMapping";

export default {
  name: 'AttachmentParameters',
  components: {AttachmentAspectsMapping},
  props: {
    attachment: {
      type: Object,
      required: true
    },
  },
  data() {
    return {
      syncType: ""
    }
  },
}
</script>