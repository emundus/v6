<template>
  <div id="form-builder-currency">
    <div v-if="loading" class="em-loader"></div>
    <div v-else class="w-full relative flex items-center currency-block">
      <input class="currency" readonly type="text" value="test">
      <span class="currency-icon">
        {{ currencyIcon }}
      </span>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../../services/formbuilder';
import Editor from "../../editor";

export default {
  props: {
    element: {
      type: Object,
      required: true
    },
    type: {
      type: String,
      required: true
    }
  },
  components: {
    Editor
  },
  data() {
    return {
      loading: false,

      editable: false,
      dynamicComponent: 0,
    };
  },
  created () {},
  methods: {
  },
  watch: {},
  computed: {
    currencyIcon() {
      switch (this.element.params['all_currencies_options']['all_currencies_options0'].iso3) {
        case 'USD':
          return '$';
        case 'EUR':
          return '€';
        case 'GBP':
          return '£';
        case 'JPY':
          return '¥';
        default:
          return '€';
      }
    }
  }
}
</script>

<style lang="scss">
#form-builder-currency {
  .currency-icon {
    position: absolute;
    right: 0;
    width: auto !important;
    padding-right: var(--em-spacing-4);
  }

  .currency-block {
    height: var(--em-form-height);
    font-size: var(--em-form-font-size);
  }
}
</style>
