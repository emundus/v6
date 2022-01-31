<template>
    <div class="em-settings-menu">

      <div class="em-w-80">
        <label class="mb-1">{{HomeContent}}</label>

        <ul class="menus-home-row" v-if="manyLanguages !== '0'">
            <li v-for="(value, index) in languages" :key="index" class="MenuFormHome">
                <a class="MenuFormItemHome"
                   @click="changeTranslation(index)"
                   :class="indexHighlight === index ? 'MenuFormItemHome_current' : ''">
                    {{value}}
                </a>
            </li>
        </ul>
        <div class="form-group controls" v-if="indexHighlight === 0 && this.form.content.fr != null">
            <editor :height="'30em'" :text="form.content.fr" :lang="actualLanguage" :enable_variables="false" :id="'editor_fr'" :key="dynamicComponent" v-model="form.content.fr"></editor>
        </div>
        <div class="form-group controls" v-if="indexHighlight === 1 && this.form.content.en != null">
            <editor :height="'30em'" :text="form.content.en" :lang="actualLanguage" :enable_variables="false" :id="'editor_en'" :key="dynamicComponent" v-model="form.content.en"></editor>
        </div>
      </div>
    </div>
</template>

<script>
    import axios from "axios";
    import Editor from "@/components/editor";
    import ModalUpdateImage from "@/components/AdvancedModals/ModalUpdateImage";
    import Translation from "@/components/translation";

    const qs = require("qs");

    export default {
        name: "editHomepage",

        components: {
            Editor,
          ModalUpdateImage,
          Translation
        },

        props: {
            actualLanguage: String,
            manyLanguages: Number
        },

        data() {
            return {
                backgroundLink: '/images/custom/home_background.png',
                enableBackground: 0,
                dynamicComponent: 0,
                indexHighlight: 0,
                form: {
                  titleColor: '#fff',
                  label: {
                    fr : '',
                    en : '',
                  },
                    content: {
                        fr: null,
                        en: null
                    }
                },
                languages: [
                    "Français",
                    "Anglais"
                ],
                translate: {
                  label: false,
                },
                TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
                DisplayBackground: Joomla.JText._("COM_EMUNDUS_ONBOARD_DISPLAY_BACKGROUND"),
                Background: Joomla.JText._("COM_EMUNDUS_ONBOARD_BACKGROUND"),
                HomeTitle: Joomla.JText._("COM_EMUNDUS_ONBOARD_HOME_TITLE"),
                HomeContent: Joomla.JText._("COM_EMUNDUS_ONBOARD_HOME_CONTENT"),
                InsertHeaderImage: Joomla.JText._("COM_EMUNDUS_ONBOARD_INSERT_HEADER_IMAGE"),
            };
        },

        methods: {
          updateView(image) {
            this.backgroundLink = image;
            this.$forceUpdate();
          },

            getArticle() {
                axios.get("index.php?option=com_emundus_onboard&controller=settings&task=gethomepagearticle")
                    .then(response => {
                        this.form.content.fr = response.data.data.introtext;
                        this.form.content.en = response.data.data.introtext_en;
                        if(this.actualLanguage == 'fr'){
                          this.indexHighlight = 0;
                        } else {
                          this.indexHighlight = 1;
                        }
                    });
            },

            changeTranslation(index) {
                this.indexHighlight = index;
                this.dynamicComponent++;
            },

          getBackground(){
            axios.get("index.php?option=com_emundus_onboard&controller=settings&task=getbackgroundoption")
                .then(response => {
                  this.enableBackground = response.data.data;
                  let content = response.data.content;
                  let parser = new DOMParser();
                  let htmlDoc = parser.parseFromString(content, 'text/html');
                  if(typeof htmlDoc.getElementsByClassName('welcome-message')[0] != 'undefined') {
                    this.form.label.fr = htmlDoc.getElementsByClassName('welcome-message')[0].textContent;
                  }
                });
          },

          updateBackgroundVisibility(){
            axios({
              method: "post",
              url: "index.php?option=com_emundus_onboard&controller=settings&task=updatebackgroundmodule",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({
                published: this.enableBackground,
              })
            });
          },

          enableLabelTranslation(){
            this.translate.label = !this.translate.label
            if(this.translate.label){
              setTimeout(() => {
                document.getElementById('label_en').focus();
              },100);
            }
          },
        },

        created() {
            this.getArticle();
            this.getBackground();
        }
    };
</script>
<style scoped>
.section-sub-menu{
  padding: 20px;
  margin: 0;
  height: 200px;
}
.settings-edit-icon{
  display: block;
  width: 100%;
  text-align: end;
  font-size: 20px;
}
.logo-settings{
  max-width: 100%;
  margin: 0;
  position: relative;
  max-height: 100px;
  padding-top: 20px;
}
.title-color-picker{
  width: 50px;
  padding: 5px !important;
  height: 50px;
  margin-left: 10px;
  border-radius: 5px !important;
  background: white;
  border: solid 2px #ccc !important;
}
</style>
