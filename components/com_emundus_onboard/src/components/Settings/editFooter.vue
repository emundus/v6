<template>
    <div class="container-evaluation">
        <ul class="menus-home-row">
            <li v-for="(value, index) in columns" :key="index" class="MenuFormHome">
                <a class="MenuFormItemHome"
                   @click="changeColumn(index)"
                   :class="indexHighlight == index ? 'MenuFormItemHome_current' : ''">
                    {{value}}
                </a>
            </li>
        </ul>
        <div class="form-group controls" v-if="indexHighlight == 0">
            <editor :text="form.content.col1" :lang="actualLanguage" :id="'editor_fr'" :key="dynamicComponent" v-model="form.content.col1"></editor>
        </div>
        <div class="form-group controls" v-if="indexHighlight == 1">
            <editor :text="form.content.col2" :lang="actualLanguage" :id="'editor_en'" :key="dynamicComponent" v-model="form.content.col2"></editor>
        </div>
    </div>
</template>

<script>
    import axios from "axios";
    import Editor from "../../components/editor";

    const qs = require("qs");

    export default {
        name: "editFooter",

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
                        col1: '',
                        col2: ''
                    }
                },
                columns: [
                  Joomla.JText._("COM_EMUNDUS_ONBOARD_COLUMN") + ' 1',
                  Joomla.JText._("COM_EMUNDUS_ONBOARD_COLUMN") + ' 2',
                  Joomla.JText._("COM_EMUNDUS_ONBOARD_PREVIEW"),
                ],
                TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
            };
        },

        methods: {
            getArticles() {
                axios.get("index.php?option=com_emundus_onboard&controller=settings&task=getfooterarticles")
                    .then(response => {
                        this.form.content.col1 = response.data.data.column1.content;
                        this.form.content.col2 = response.data.data.column2.content;
                    });
            },

            changeColumn(index) {
                this.indexHighlight = index;
                this.dynamicComponent++;
            }
        },

        created() {
            this.getArticles();
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
