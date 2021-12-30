<template>
    <div class="em-settings-menu">
      <div class="em-w-80">
        <ul class="menus-home-row" v-if="manyLanguages !== '0'">
            <li v-for="(value, index) in languages" :key="index" class="MenuFormHome">
                <a class="MenuFormItemHome"
                   @click="changeTranslation(index)"
                   :class="indexHighlight === index ? 'MenuFormItemHome_current' : ''">
                    {{value}}
                </a>
            </li>
        </ul>
        <div class="form-group controls" style="margin-top: 5em" v-if="indexHighlight === 0 && this.form.content.fr != null">
            <editor :height="'30em'" :text="form.content.fr" :lang="actualLanguage" :enable_variables="false" :id="'editor_fr'" :key="dynamicComponent" v-model="form.content.fr"></editor>
        </div>
        <div class="form-group controls" style="margin-top: 5em" v-if="indexHighlight === 1 && this.form.content.en != null">
            <editor :height="'30em'" :text="form.content.en" :lang="actualLanguage" :enable_variables="false" :id="'editor_en'" :key="dynamicComponent" v-model="form.content.en"></editor>
        </div>
      </div>
    </div>
</template>

<script>
    import axios from "axios";
    import Editor from "../../components/editor";

    const qs = require("qs");

    export default {
        name: "editCGV",

        components: {
            Editor
        },

        props: {
            actualLanguage: String,
            manyLanguages: Number
        },

        data() {
            return {
                dynamicComponent: 0,
                indexHighlight: 0,
                form: {
                    content: {
                        fr: null,
                        en: null
                    }
                },
                languages: [
                    "Français",
                    "Anglais"
                ],
                TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
            };
        },

        methods: {
            getArticle() {
                axios.get("index.php?option=com_emundus_onboard&controller=settings&task=getcgvarticle")
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
            }
        },

        created() {
            this.getArticle();
        }
    };
</script>
<style scoped>
</style>
