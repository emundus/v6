<template>
  <div id="form-builder-rules-js-condition" class="self-start w-full">
    <div class="flex justify-between items-center">
      <h2>{{ conditionLabel }}</h2>
      <button v-if="index !== 0" type="button" @click="$emit('remove-condition', index)" class="w-auto">
        <span class="material-icons-outlined text-red-500">close</span>
      </button>
    </div>

  </div>
</template>

<script>
import formService from '../../../../services/form';

import formBuilderMixin from '../../../../mixins/formbuilder';
import globalMixin from '../../../../mixins/mixin';
import errorMixin from '../../../../mixins/errors';
import Swal from 'sweetalert2';

export default {
  components: {},
  props: {
    page: {
      type: Object,
      default: {}
    },
    condition: {
      type: Object,
      default: {}
    },
    index: {
      type: Number,
      default: 0
    }
  },
  mixins: [formBuilderMixin, globalMixin, errorMixin],
  data() {
    return {
      loading: false,
    };
  },
  mounted() {
    if (this.page.id) {}
  },
  methods: {},
  computed: {
    conditionLabel() {
      return this.condition.label !== '' ? this.condition.label : `Condition n°${this.index + 1}`;
    }
  }
}
</script>

<style lang="scss">
</style>
