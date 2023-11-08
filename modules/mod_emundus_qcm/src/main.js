import Vue from 'vue'
import Qcm from '@/Qcm'

Vue.config.productionTip = true;
Vue.config.devtools = false;

if (document.getElementById('em-qcm-vue')) {
    new Vue({
        el: '#em-qcm-vue',
        render(h) {
            return h(Qcm, {
                props: {
                    questions: this.$el.attributes.questions.value,
                    formid: this.$el.attributes.formid.value,
                    step: this.$el.attributes.step.value,
                    pending: this.$el.attributes.pending.value,
                    module: this.$el.attributes.module.value,
                    tierstemps: this.$el.attributes.tierstemps.value,
                }
            });
        }
    });
}

