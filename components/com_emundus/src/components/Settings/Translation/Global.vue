<template>
  <div>
    <div class="mt-3 mb-6">
      <div class="mb-2">
        <p class="font-medium mb-1">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_DEFAULT') }}</p>
        <p class="text-sm">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_DEFAULT_DESC') }}</p>
      </div>
      <div class="w-1/3">
        <multiselect
            v-model="defaultLang"
            label="title_native"
            track-by="lang_code"
            :options="allLanguages"
            :multiple="false"
            :taggable="false"
            select-label=""
            selected-label=""
            deselect-label=""
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="false"
            :allow-empty="false"
            @select="updateDefaultLanguage"
        ></multiselect>
      </div>
    </div>

    <div class="mb-6">
      <div class="mb-2">
        <p class="font-medium mb-1">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SECONDARY') }}</p>
        <p class="text-sm">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SECONDARY_DESC') }}</p>
      </div>
      <div class="w-1/3">
        <multiselect
            v-model="secondaryLanguages"
            label="title_native"
            track-by="lang_code"
            :options="otherLanguages"
            :multiple="true"
            :taggable="false"
            select-label=""
            selected-label=""
            deselect-label=""
            :close-on-select="false"
            :clear-on-select="false"
            :searchable="false"
            @remove="unpublishLanguage"
            @select="publishLanguage"
        ></multiselect>
        <a class="cursor-pointer mt-3 text-xs hover:blue-500 em-profile-color underline" @click="purposeLanguage">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_OTHER_LANGUAGE') }}</a>
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>

import client from "com_emundus/src/services/axiosClient";
import translationsService from "com_emundus/src/services/translations";
import Multiselect from 'vue-multiselect';
import Swal from "sweetalert2";

export default {
  name: "global",
  props: { },
  components: {
    Multiselect
  },
  data() {
    return {
      defaultLang: null,
      secondaryLanguages: [],
      allLanguages: [],
      otherLanguages: [],

      orphelins_count: 0,

      loading: false
    }
  },

  created() {
    this.loading = true;
    translationsService.getDefaultLanguage().then((response) => {
      this.defaultLang = response;
      this.getAllLanguages();
      this.loading = false;
    });
  },

  methods:{
    async getAllLanguages() {
      try {
        const response = await client().get('index.php?option=com_emundus&controller=translations&task=getlanguages');

        this.allLanguages = response.data;
        this.allLanguages.forEach((lang) => {
          if(lang.lang_code !== this.defaultLang.lang_code){
            if(lang.published == 1){
              this.secondaryLanguages.push(lang);
            }
            this.otherLanguages.push(lang);
          }
        })
        this.secondaryLanguages.forEach((sec_lang) => {
          translationsService.getOrphelins(this.defaultLang.lang_code,sec_lang.lang_code).then((orphelins) => {
            this.orphelins_count = orphelins.data.length;
            this.$emit('updateOrphelinsCount',this.orphelins_count);
          })
        })
      } catch (e) {
        return false;
      }
    },
    unpublishLanguage(option){
      translationsService.updateLanguage(option.lang_code,0);
    },
    publishLanguage(option){
      translationsService.updateLanguage(option.lang_code,1);
    },
    updateDefaultLanguage(option){
      translationsService.updateLanguage(option.lang_code,1, 1).then(() => {
        let valuesToRemove = this.secondaryLanguages.findIndex(lang => lang.lang_code == option.lang_code);
        if(valuesToRemove !== -1) {
          this.secondaryLanguages.splice(valuesToRemove, 1);
        }
        this.getAllLanguages();
      })
    },
    async purposeLanguage(){
      const { value: formValues } = await Swal.fire({
        title: this.translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SUGGEST_LANGUAGE'),
        html:
            '<p class="em-body-16-semibold em-mb-8 em-text-align-left">' + this.translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SUGGEST_LANGUAGE_FIELD') + '</p>' +
            '<input id="language_purpose" class="em-input">',
        showCancelButton: true,
        cancelButtonText: this.translate('COM_EMUNDUS_ONBOARD_CANCEL'),
        confirmButtonText: this.translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SUGGEST_LANGUAGE_SEND'),
        showLoaderOnConfirm: false,
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
        preConfirm: () => {
          const language = document.getElementById('language_purpose').value;
          return translationsService.sendMailToInstallLanguage(language);
        },
        allowOutsideClick: () => !Swal.isLoading()
      })

      if (formValues) {
        await Swal.fire({
          title: this.translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SUGGEST_LANGUAGE_SENDED'),
          text: this.translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SUGGEST_LANGUAGE_SENDED_TEXT'),
          showCancelButton: false,
          showConfirmButton: false,
          timer: 3000,
          customClass: {
            title: 'em-swal-title',
          },
        });
      }
    }
  }
}
</script>

<style scoped lang="scss">
</style>
