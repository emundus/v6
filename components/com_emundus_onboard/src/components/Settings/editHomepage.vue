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
        <div class="form-group controls" v-if="indexHighlight == 0">
            <editor :text="form.content.fr" :lang="actualLanguage" :id="'editor_fr'" :key="dynamicComponent" v-model="form.content.fr"></editor>
        </div>
        <div class="form-group controls" v-if="indexHighlight == 1">
            <editor :text="form.content.en" :lang="actualLanguage" :id="'editor_en'" :key="dynamicComponent" v-model="form.content.en"></editor>
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
                        fr: '',
                        en: ''
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
                        this.dynamicComponent++;
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
<style>
    .menus-home-row{
        display: flex;
        flex-direction: row;
        padding-left: 0 !important;
        padding-top: 1em;
        margin: 0 auto;
        overflow-x: scroll;
    }

    .MenuFormHome {
        list-style: none;
        text-decoration: none;
        margin: 10px 10px 30px 10px;
        min-width: 100px;
    }

    .MenuFormItemHome {
        text-decoration: none;
        color: black;
        cursor: pointer;
        padding: 5px;
        border-radius: 5px;
        white-space: nowrap;
    }
    .MenuFormItemHome:not(.MenuFormItemHome_current):hover {
        color: grey;
    }
    .MenuFormItemHome_current {
        color: white;
        cursor: pointer;
        background-color: #de6339;
    }
    .MenuFormItemHome_current:after, .MenuFormItemHome_current:before {
        opacity: 1 !important;
        width: 50% !important;
    }
</style>
