<template>
  <div>
    <select v-if="parameter.type === 'select'" class="dropdown-toggle w-select !mb-0"
            :id="'param_' + parameter.param"
            v-model="value"
            :disabled="parameter.editable === false">
      <option v-for="option in parameter.options" :key="option.value" :value="option.value">{{
          translate(option.label)
        }}
      </option>
    </select>

    <multiselect
        :class="'cursor-pointer'"
        v-else-if="parameter.type === 'keywords'"
        v-model="value"
        label="name"
        track-by="code"
        :options="tagOptions"
        :multiple="true"
        :taggable="true"
        :placeholder="parameter.placeholder"
        @tag="addTag"
        :key="parameter.value.length"
    ></multiselect>

    <textarea v-else-if="parameter.type === 'textarea'"
              :id="'param_' + parameter.param"
              v-model="value"
              class="!mb-0"
              :maxlength="parameter.maxlength"
              :readonly="parameter.editable === false">
    </textarea>

    <div v-else-if="parameter.type === 'timezone'">
      <multiselect
          :class="'cursor-pointer'"
          v-model="value"
          label="label"
          track-by="value"
          :options="timezoneOptions"
          :multiple="false"
          :taggable="false"
          select-label=""
          selected-label=""
          deselect-label=""
          :placeholder="''"
          :close-on-select="true"
          :clear-on-select="false"
          :searchable="true"
          :allow-empty="false"
      ></multiselect>
    </div>

    <div v-else-if="parameter.type === 'yesno'">
      <fieldset  data-toggle="buttons" class="flex items-center gap-2">
        <label :for="'param_' + parameter.param + '_input_0'" :class="[value == 0 ? 'bg-red-700' : 'bg-white border-neutral-400']" class="w-60 h-10 p-2.5 rounded-lg border justify-center items-center gap-2.5 inline-flex">
          <input v-model="value" type="radio" class="fabrikinput !hidden" :name="'param_' + parameter.param + '[]'" :id="'param_' + parameter.param + '_input_0'" value="0" :checked="value === 0">
          <span :class="[value == 0 ? 'text-white' : 'text-green-700']">{{ translate('JNO') }}</span>
        </label>

        <label :for="'param_' + parameter.param + '_input_1'" :class="[value == 1 ? 'bg-green-700' : 'bg-white border-neutral-400']" class="w-60 h-10 p-2.5 rounded-lg border justify-center items-center gap-2.5 inline-flex">
          <input v-model="value" type="radio" class="fabrikinput !hidden" :name="'param_' + parameter.param + '[]'" :id="'param_' + parameter.param + '_input_1'" value="1" :checked="value === 1">
          <span :class="[value == 1 ? 'text-white' : 'text-green-700']">{{ translate('JYES') }}</span></label>
      </fieldset>
    </div>

    <div v-else-if="parameter.type === 'toggle'" class="mb-4 flex items-center">
      <div class="em-toggle">
        <input type="checkbox"
               class="em-toggle-check"
               :id="'published'"
               v-model="value"
        />
        <strong class="b em-toggle-switch"></strong>
        <strong class="b em-toggle-track"></strong>
      </div>
      <span for="published" class="ml-2">{{ translate(parameter.label) }}</span>
      </div>

    <div v-else>
    <input  :type="parameter.type" class="form-control !mb-0"
           :placeholder="parameter.placeholder"
           :id="'param_' + parameter.param"
           v-model="value"
           :maxlength="parameter.maxlength"
           :readonly="parameter.editable === false"
            @keydown.enter="validate(parameter)"
            @focusout="validate(parameter)"
    >
      <div v-if="parameter.type==='email'" :id="'emailCheck-'+parameter.param"
           :style="{ color: emailValidationColor[parameter.param] }">
        {{
          translate(validationString)
        }}
      </div>

    </div>
  </div>
</template>

<script>
import Multiselect from "vue-multiselect";
import settingsService from "../../services/settings";

export default {
  name: "Parameter",
  components: {Multiselect},
  props: {
    parameter: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      initValue: null,
      value: null,

      tagOptions: [],
      timezoneOptions: [],
      emailValidationMessage: [],
      emailValidationColor: [],
      validationString :""
    }
  },
  created() {
    if(this.$props.parameter && this.$props.parameter.type === 'timezone') {
      settingsService.getTimezoneList().then((response) => {
        if(response.data.status) {
          this.timezoneOptions = response.data.data;
          this.value = this.timezoneOptions.find((timezone) => timezone.value === this.$props.parameter.value);
          this.initValue = this.value;
        }
      });
    } else if (this.$props.parameter) {
      this.value = this.$props.parameter.value;
      this.initValue = this.value;
    }
  },
  methods: {
    addTag(newTag) {
      const tag = {
        name: newTag,
        code: newTag
      }
      this.tagOptions.push(tag)
      this.parameter.value.push(tag)
    },
    validateEmail(email) {
      let res = /^[\w-.]+@([\w-]+\.)+[\w-]{2,4}$/;
      return res.test(email);
    },
    validate(paramEmail) {
      let paramEmailId = paramEmail.param;
      let email = this.value;
      this.emailValidationMessage[paramEmail.param] = "";

      if (this.validateEmail(email)) {
         this.$set(this.emailValidationMessage, paramEmail.param,   email );
        this.validationString = "COM_EMUNDUS_GLOBAL_PARMAS_SECTIONS_MAIL_CHECK_INPUT_MAIL_YES"
         this.$set(this.emailValidationColor, paramEmail.param, "green");
        //this.saveEmundusParam(paramEmail);

      } else {
         this.$set(this.emailValidationMessage, paramEmail.param,  email)
        this.validationString = "COM_EMUNDUS_GLOBAL_PARMAS_SECTIONS_MAIL_CHECK_INPUT_MAIL_NO"
         this.$set(this.emailValidationColor, paramEmail.param, "red");

      }
      return false;
    },

  },
  watch: {
    value: {
      handler: function (val, oldVal) {
        this.$props.parameter.value = val;

        if(oldVal !== null) {
          if (oldVal !== val) {
            this.$emit('needSaving', true, this.$props.parameter)
          } else {
            this.$emit('needSaving', false, this.$props.parameter)
          }
        }
      },
      deep: true
    }
  }
}
</script>

<style scoped>

</style>