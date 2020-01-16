// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from "vue";
import Add from "./Add";
import Add2 from "./Add2";
import Dossiers from "./Dossiers";

Vue.config.productionTip = false;

new Vue({
  el: "add",
  components: {
    Add
  },
  template: "<Add/>"
});

new Vue({
  el: "add2",
  components: {
    Add2
  },
  template: "<Add2/>"
});

new Vue({
  el: "dossiers",
  render(h) {
    "use strict";
    return h(Dossiers, {
      props: {
        dossiers: JSON.parse(this.$el.attributes.dossiers.value),
        forms: JSON.parse(this.$el.attributes.forms.value),
        attachements: JSON.parse(this.$el.attributes.attachements.value),
        firstpage: JSON.parse(this.$el.attributes.firstpage.value)
      }
    });
  }
});
