<template>
  <Component
      :is="element.plugin"
      :element="element"
  ></Component>
</template>

<script>
import field from "../formClean/Plugin/field";
import birthday from "../formClean/Plugin/birthday";
import checkbox from "../formClean/Plugin/checkbox";
import dropdown from "../formClean/Plugin/dropdown";
import radiobtn from "../formClean/Plugin/radiobtn";
import textarea from "../formClean/Plugin/textarea";
import display from "../formClean/Plugin/display";
import file from "../formClean/Plugin/fileupload";
import yesno from '../formClean/Plugin/yesno';

export default {
  components: {
    field,
    birthday,
    checkbox,
    dropdown,
    radiobtn,
    textarea,
    display,
    file,
    yesno
  },
  props: {
    element: {
      type: Object,
      default: () => ({})
    },
  },
}
</script>