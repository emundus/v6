<template>
  <div id="form-builder-phone-number">
    <div v-if="loading" class="em-loader"></div>
    <div v-else>
      <div class="flex items-center">
        <div class="h-10 country-select flex items-center p-2 justify-center">
          <img v-if="element.params.default_country" :src="'images/emundus/flags/'+element.params.default_country.toLowerCase()+'.png'">
          <img v-else src="images/emundus/flags/fr.png">
        </div>
        <div class="w-full">
          <input class="phonenumber" readonly type="text" value="+33 6 12 34 56 78">
        </div>
      </div>
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
  watch: {}
}
</script>

<style lang="scss">
#form-builder-phone-number {
  .country-select {
    height: 42px;
    width: 80px;
    border-top: solid 1px;
    border-bottom: solid 1px;
    border-left: solid 1px;
    border-color: #e0e0e5;
    border-top-left-radius: var(--em-coordinator-br);
    border-bottom-left-radius: var(--em-coordinator-br);

    img {
      width: 24px;
    }
  }

  .phonenumber {
    height: 42px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }
}
</style>
