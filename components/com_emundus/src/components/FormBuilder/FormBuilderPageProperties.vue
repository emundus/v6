<template>
  <div id="form-builder-page-properties">
    <div class="em-flex-row em-flex-space-between em-p-16">
      <p>{{ translate("COM_EMUNDUS_FORM_BUILDER_PAGE_PROPERTIES") }}</p>
      <span class="material-icons-outlined em-pointer" @click="$emit('close')">close</span>
    </div>
    <ul id="page-properties-tabs" class="em-flex-row em-flex-space-between em-p-16 em-w-90">
      <li
          v-for="tab in tabs"
          :key="tab.id"
          :class="{ 'is-active': tab.active }"
          class="em-p-16 em-pointer"
          @click="selectTab(tab)"
      >
        {{ translate(tab.label) }}
      </li>
    </ul>
    <div id="properties">
      <div v-if="tabs[0].active" id="element-parameters" class="em-p-16">
        <div v-if="errors.length" class="em-mb-16">
          <p v-for="error in errors" class="em-red-500-color">{{ translate(error) }}</p>
        </div>

        <label for="page-label">{{ translate('COM_EMUNDUS_FORM_BUILDER_PAGE_LABEL') }}</label>
        <input id="page-label" class="em-w-100" maxlength="50" minlength="3" name="page-label" type="text" v-model="page.label[shortDefaultLang]"/>

        <label for="page-intro" class="em-mt-8">{{ translate('COM_EMUNDUS_FORM_BUILDER_PAGE_INTRO') }}</label>
        <textarea id="page-intro" class="em-w-100" name="page-intro" v-model="page.intro[shortDefaultLang]"></textarea>
      </div>
    </div>
    <div class="em-flex-row em-flex-space-between actions em-m-16">
      <button id="page-form-button" class="em-primary-button" @click="addPage()">
        {{ translate("COM_EMUNDUS_FORM_BUILDER_PAGE_PROPERTIES_ADD") }}
      </button>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';
import formBuilderMixin from "../../mixins/formbuilder";

export default {
  name: 'FormBuilderPageProperties',
  components: {},
  props: {
    profile_id: {
      type: Number,
      required: true
    },
    pages: {
      type: Array,
      required: true
    }
  },
  mixins: [formBuilderMixin],
  data() {
    return {
      page:{
        label: {
          fr: 'Nouvelle page',
          en: 'New page'
        },
        intro: {
          fr: '',
          en: ''
        },
        prid: this.profile_id,
        modelid: -1,
        template: 0
      },
      tabs: [
        {
          id: 0,
          label: "COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_GENERAL",
          active: true,
        }
      ],
      errors: [],
    };
  },
  methods: {
    selectTab(tab) {
      this.tabs.forEach(t => {
        t.active = false;
      });
      tab.active = true;
    },
    addPage() {
      this.errors = [];

      if(this.page.label.fr === '' || this.page.label.en === '') {
        this.errors.push('COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_ERROR_LABEL_EMPTY');
        document.getElementById('page-label').focus();
      }

      let stop = false;
      this.pages.forEach((page) => {
        if((page.label === this.page.label.fr || page.label === this.page.label.en) && !stop) {
          this.errors.push('COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_ERROR_LABEL_EXISTS');
          stop = true;
        }
      });

      if(this.errors.length === 0) {
        formBuilderService.addPage(this.page).then(response => {
					if (!response.status) {
						this.errors.push(response.msg);
						stop = true;
					} else {
						this.$emit('close', response.data);
						this.updateLastSave();
					}
        });
      }
    },
  },
  watch: {
    'page.label[shortDefaultLang]': function(newValue, oldValue) {
      this.errors = [];
	    this.page.label.fr = newValue;
			this.page.label.en = newValue;
      if(newValue.length >= 50 || newValue.length < 3) {
        document.getElementById('page-label').style.borderColor = '#DB333E';
        document.getElementById('page-form-button').disabled = true;
      } else {
        document.getElementById('page-label').style.borderColor = '#ccc';
        document.getElementById('page-form-button').disabled = false;
      }
    },
  }
}
</script>

<style lang="scss">
#page-properties-tabs {
  list-style-type: none;
  margin: auto;
  align-items: center;

  li {
    text-align: center;
    width: 100%;
    border-bottom: 2px solid transparent;
    transition: all .3s;

    &.is-active {
      border-bottom: 2px solid black;
    }
  }
}

#page-form-button[disabled]{
  background-color: #C5C8CE;
}
</style>
