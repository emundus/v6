<template>
  <div id="form-builder-rules-js" class="self-start w-full">
    <h2>{{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_ADD_JS') }}</h2>
    <div class="mt-2 rounded-lg bg-white px-3 py-4 flex flex-col gap-6">
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
}
</script>

<style lang="scss">
</style>
