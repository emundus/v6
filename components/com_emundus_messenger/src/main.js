import Vue from 'vue';
import Messages from "./components/Messages";

Vue.config.productionTip = false;
Vue.config.devtools = true;

if (document.getElementById('em-messages-vue')) {
  new Vue({
    el: '#em-messages-vue',
    render(h) {
      return h(Messages, {
        props: {}
      });
    }
  });
}

if (document.getElementById('em-addProgram-vue')) {
  new Vue({
    el: '#em-addProgram-vue',
    render(h) {
      return h(AddProgram, {
        props: {
          prog: this.$el.attributes.prog.value,
          actualLanguage: this.$el.attributes.actualLanguage.value
        }
      });
    }
  });
}

if (document.getElementById('em-addProgramAdvancedSettings-vue')) {
  new Vue({
    el: '#em-addProgramAdvancedSettings-vue',
    render(h) {
      return h(AddProgramAdvancedSettings, {
        props: {
          prog: this.$el.attributes.prog.value,
          actualLanguage: this.$el.attributes.actualLanguage.value,
          coordinatorAccess: this.$el.attributes.coordinatorAccess.value,
        }
      });
    }
  });
}

if (document.getElementById('em-addCampaign-vue')) {
  new Vue({
    el: '#em-addCampaign-vue',
    render(h) {
      return h(AddCampaign, {
        props: {
          campaign: this.$el.attributes.campaign.value,
          actualLanguage: this.$el.attributes.actualLanguage.value,
          coordinatorAccess: this.$el.attributes.coordinatorAccess.value,
          manyLanguages: this.$el.attributes.manyLanguages.value,
        }
      });
    }
  });
}

if (document.getElementById('em-addEmail-vue')) {
  new Vue({
    el: '#em-addEmail-vue',
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

if (document.getElementById('em-addForm-vue')) {
  new Vue({
    el: '#em-addForm-vue',
    render(h) {
      return h(AddForm, {
        props: {
          profileId: this.$el.attributes.profileId.value,
          campaignId: this.$el.attributes.campaignId.value,
        }
      });
    }
  });
}

if (document.getElementById('em-addFormNextCampaign-vue')) {
  new Vue({
    el: '#em-addFormNextCampaign-vue',
    render(h) {
      return h(AddFormNextCampaign, {
        props: {
          campaignId: this.$el.attributes.campaignId.value,
          actualLanguage: this.$el.attributes.actualLanguage.value,
          index: this.$el.attributes.index.value,
          manyLanguages: this.$el.attributes.manyLanguages.value,
        }
      });
    }
  });
}

if (document.getElementById('em-formBuilder-vue')) {
  new Vue({
    el: '#em-formBuilder-vue',
    render(h) {
      return h(formBuilder, {
        props: {
          prid: this.$el.attributes.prid.value,
          index: this.$el.attributes.index.value,
          cid: this.$el.attributes.cid.value,
          eval: this.$el.attributes.eval.value,
          actualLanguage: this.$el.attributes.actualLanguage.value,
          manyLanguages: this.$el.attributes.manyLanguages.value
        }
      });
    }
  });
}

if (document.getElementById('em-evaluationBuilder-vue')) {
  new Vue({
    el: '#em-evaluationBuilder-vue',
    render(h) {
      return h(evaluationBuilder, {
        props: {
          prid: this.$el.attributes.prid.value,
          index: this.$el.attributes.index.value,
          cid: this.$el.attributes.cid.value,
          eval: this.$el.attributes.eval.value,
          actualLanguage: this.$el.attributes.actualLanguage.value,
          manyLanguages: this.$el.attributes.manyLanguages.value
        }
      });
    }
  });
}

if (document.getElementById('em-globalSettings-vue')) {
  new Vue({
    el: '#em-globalSettings-vue',
    render(h) {
      return h(GlobalSettings, {
        props: {
          actualLanguage: this.$el.attributes.actualLanguage.value,
          coordinatorAccess: this.$el.attributes.coordinatorAccess.value,
          manyLanguages: this.$el.attributes.manyLanguages.value,
        }
      });
    }
  });
}
