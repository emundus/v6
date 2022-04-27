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
        <div class="form-group controls" v-if="indexHighlight == 0 && this.form.content.col1 != null">
            <editor :height="'30em'" :text="form.content.col1" :lang="actualLanguage" :enable_variables="false" :id="'editor_fr'" :key="dynamicComponent" v-model="form.content.col1"></editor>
        </div>
        <div class="form-group controls" v-if="indexHighlight == 1 && this.form.content.col2 != null">
            <editor :height="'30em'" :text="form.content.col2" :lang="actualLanguage" :enable_variables="false" :id="'editor_en'" :key="dynamicComponent" v-model="form.content.col2"></editor>
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
                        col1: null,
                        col2: null
                    }
                },
                columns: [
                  Joomla.JText._("COM_EMUNDUS_ONBOARD_COLUMN") + ' 1',
                  Joomla.JText._("COM_EMUNDUS_ONBOARD_COLUMN") + ' 2',
                ],
                TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
            };
        },

        methods: {
            getArticles() {
                axios.get("index.php?option=com_emundus_onboard&controller=settings&task=getfooterarticles")
                    .then(response => {
                        this.form.content.col1 = response.data.data.column1;
                        this.form.content.col2 = response.data.data.column2;
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
<style scoped>
</style>
