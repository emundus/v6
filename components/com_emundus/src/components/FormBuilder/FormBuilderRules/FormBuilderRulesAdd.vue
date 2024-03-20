<template>
  <div id="form-builder-rules-add" class="self-start w-full">
    <div class="p-8">
      <div class="flex flex-col gap-3">
        <div class="flex items-center gap-1 cursor-pointer mb-2" :title="translate('COM_EMUNDUS_FORM_BUILDER_RULE_GO_BACK')" @click="$emit('close-rule-add')">
          <span class="material-icons-outlined">chevron_left</span>
          <p>{{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_GO_BACK') }}</p>
        </div>

        <form-builder-rules-js
          v-if="rule.value === 'js'"
          :page="page"
          :elements="elements"
          />
      </div>
    </div>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import formService from '../../../services/form';

import formBuilderMixin from '../../../mixins/formbuilder';
import globalMixin from '../../../mixins/mixin';
import errorMixin from '../../../mixins/errors';
import Swal from 'sweetalert2';
import FormBuilderRulesJs from "@/components/FormBuilder/FormBuilderRules/FormBuilderRulesType/FormBuilderRulesJs.vue";

export default {
  components: {FormBuilderRulesJs},
  props: {
    page: {
      type: Object,
      default: {}
    },
	  mode: {
		  type: String,
		  default: 'forms'
	  },
    rule: {
      type: Object,
      default: {}
    }
  },
  mixins: [formBuilderMixin, globalMixin, errorMixin],
  data() {
    return {
      fabrikPage: {},
      elements: [],

      loading: false,
    };
  },
  mounted() {
    if (this.page.id) {
      this.loading = true;

      formService.getPageObject(this.page.id).then(response => {
        if (response.status && response.data != '') {
          this.fabrikPage = response.data;
        } else {
          this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR'), this.translate(response.msg));
        }

        Object.entries(this.fabrikPage.Groups).forEach(([key, group]) => {
          Object.entries(group.elements).forEach(([key,element]) => {
            if(!element.hidden) {
              this.elements.push(element);
            }
          });
        });

        this.loading = false;
      });
    }
  },
  methods: {},
}
</script>

<style lang="scss">
</style>
