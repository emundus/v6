<template>
  <div class="fabrikGroup" v-if="elements.length">
    <legend>{{ translate(group.label) }}</legend>
     <div v-for="element in filteredElements" class="em-profile-element row-fluid" :id="'element_' + element.id" :key="element.id">
      <!-- RENDER BY PLUGIN -->
      <component v-bind:is="element.plugin" :element="element" :value="user[element.name]" :key="element.id" @input="updateValue" />
    </div>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */
import field from './Elements/field'
import radiobutton from './Elements/radiobutton'
import databasejoin from "./Elements/databasejoin";
import checkbox from "./Elements/checkbox";
import calc from "./Elements/calc";
/* IMPORT YOUR SERVICES */
import user from "com_emundus/src/services/user";

export default {
  name: "Section",
  components: {
    field,
    radiobutton,
    databasejoin,
    checkbox,
    calc
  },
  props: {
    group: Object,
    user: Object
  },
  data: () => ({
    elements: [],
  }),
  created() {
    user.getProfileElements(this.group.id).then(elements => {
      this.elements = elements.elements;
    });
  },
  methods: {
    updateValue(element) {
      this.$emit('input', element)
    }
  },
  computed: {
    filteredElements() {
      return this.elements.filter((element) => {
        return ['id','date_time','fnum','user'].includes(element.name) == false;
      });
    }
  }
}
</script>

<style scoped>

.fabrikGroup {
  margin-bottom: 0 !important;
  margin-top: 0 !important;
}

legend {
font-weight: 600;
}

</style>
