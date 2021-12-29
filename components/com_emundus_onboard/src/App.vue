<template>
  <div>
    <transition name="slide-right">
      <component v-bind:is="$props.component"/>
    </transition>
      <div class="loading-form" v-if="this.loading">
        <RingLoader :color="'#12DB42'" />
      </div>
  </div>
</template>

<script>
import {global} from "./store/global";

import "./assets/css/normalize.css";
import "./assets/css/emundus-webflow.scss";
import "./assets/css/bootstrap.css";
import "./assets/css/codemirror.css";
import "./assets/css/codemirror.min.css";
import "./assets/css/views_emails.css";
import "./assets/css/date-time.css";

import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";

//Register my components
import list from "./views/list";
import addcampaign from "./views/addCampaign"
import addemail from "./views/addEmail"
import addformnextcampaign from "./views/addFormNextCampaign"
import formbuilder from "./views/formBuilder"
import evaluationbuilder from "./views/evaluationBuilder"
import settings from "./views/globalSettings"
//

export default {
  name: "App",
  props: {
    component: String,
    datas: Object,
    actualLanguage: String,
    manyLanguages: String,
    coordinatorAccess: String,
  },
  components: {
    list,
    addcampaign,
    addformnextcampaign,
    addemail,
    formbuilder,
    evaluationbuilder,
    settings,
  },
  data: () => ({
    loading: false,
  }),

  created() {
    global.commit("initDatas", this.$props.datas);
    global.commit("initCurrentLanguage", this.$props.actualLanguage);
    global.commit("initManyLanguages", this.$props.manyLanguages);
    global.commit("initCoordinatorAccess", this.$props.coordinatorAccess);
  },

  watch: {
    loading: function (value) {
      this.loading = value;
    }
  }
}
</script>
