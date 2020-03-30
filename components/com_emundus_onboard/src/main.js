import Vue from "vue";
import Vuelidate from "vuelidate";
import List from "./views/list.vue";
import AddProgram from "./views/addProgram.vue";
import AddCampaign from "./views/addCampaign.vue";
import AddEmail from "./views/addEmail.vue";
import AddForm from "./views/addForm.vue";
import formBuilder from "./views/formBuilder.vue";
import VueJsModal from "vue-js-modal";
import VueDraggable from "vue-draggable";

import Notifications from "vue-notification";
import velocity from "velocity-animate";

Vue.use(Notifications, { velocity });
Vue.use(Vuelidate);
Vue.use(VueJsModal);
Vue.use(VueDraggable);

Vue.config.productionTip = false;

if (document.getElementById("em-list-vue")) {
  new Vue({
    el: "list",
    render(h) {
      return h(List, {
        props: {
          type: this.$el.attributes.type.value
        }
      });
    }
  });
}

if (document.getElementById("em-addProgram-vue")) {
  new Vue({
    el: "#em-addProgram-vue",
    render(h) {
      return h(AddProgram, {
        props: {
          prog: this.$el.attributes.prog.value
        }
      });
    }
  });
}

if (document.getElementById("em-addCampaign-vue")) {
  new Vue({
    el: "#em-addCampaign-vue",
    render(h) {
      return h(AddCampaign, {
        props: {
          campaign: this.$el.attributes.campaign.value,
          actualLanguage: this.$el.attributes.actualLanguage.value
        }
      });
    }
  });
}

if (document.getElementById("em-addEmail-vue")) {
  new Vue({
    el: "#em-addEmail-vue",
    render(h) {
      return h(AddEmail, {
        props: {
          email: this.$el.attributes.email.value,
          actualLanguage: this.$el.attributes.actualLanguage.value
        }
      });
    }
  });
}

if (document.getElementById("em-addForm-vue")) {
  new Vue({
    el: "#em-addForm-vue",
    render(h) {
      return h(AddForm, {
        props: {
          formulaireEmundus: this.$el.attributes.form.value,
          actualLanguage: this.$el.attributes.actualLanguage.value
        }
      });
    }
  });
}

if (document.getElementById("em-formBuilder-vue")) {
  new Vue({
    el: "#em-formBuilder-vue",
    render(h) {
      return h(formBuilder, {
        props: {
          prid: this.$el.attributes.prid.value,
          index: this.$el.attributes.index.value
        }
      });
    }
  });
}
