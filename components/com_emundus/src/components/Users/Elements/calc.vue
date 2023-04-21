<template>
  <div class="control-group fabrikElementContainer">
	  <label class="fabrikLabel" :for="'input_' + element.id" v-html="element.label"></label>
	  <div class="fabrikElement">
      <span v-if="tip.length > 0" class="em-tip">{{ tip }}</span>
      <p>{{ element.value }}</p>
    </div>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */
/* IMPORT YOUR SERVICES */

export default {
  name: "calc",
  components: {},
  mixins: [],
  props: {
    element: {
      type: Object,
      required: true
    },
  },
  data: () => ({
    params: {},
  }),
  created() {
    this.params = JSON.parse(this.element.params);
  },
  methods: {},
  computed: {
    tip: function(){
      return this.params.rollover;
    },
  },
}
</script>

