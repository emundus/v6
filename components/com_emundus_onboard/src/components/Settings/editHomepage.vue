<template>
    <div class="container-evaluation">
        <ul class="menus-home-row">
            <li v-for="(value, index) in languages" :key="index" class="MenuFormHome">
                <a class="MenuFormItemHome"
                   @click="changeTranslation(index)"
                   :class="indexHighlight == index ? 'MenuFormItemHome_current' : ''">
                    {{value}}
                </a>
            </li>
        </ul>
        <div class="form-group controls" v-if="indexHighlight == 0 && this.form.content.fr != null">
            <editor :text="form.content.fr" :lang="actualLanguage" :enable_variables="false" :id="'editor_fr'" :key="dynamicComponent" v-model="form.content.fr"></editor>
        </div>
        <div class="form-group controls" v-if="indexHighlight == 1 && this.form.content.en != null">
            <editor :text="form.content.en" :lang="actualLanguage" :enable_variables="false" :id="'editor_en'" :key="dynamicComponent" v-model="form.content.en"></editor>
        </div>
    </div>
</template>

<script>
    import axios from "axios";
    import Editor from "../../components/editor";

    const qs = require("qs");

    export default {
        name: "editHomepage",

        components: {
            Editor
        },

        props: {
            actualLanguage: String
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
                axios.get("index.php?option=com_emundus_onboard&controller=settings&task=gethomepagearticle")
                    .then(response => {
                        this.form.content.fr = response.data.data.introtext;
                        this.form.content.en = response.data.data.introtext_en;
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
