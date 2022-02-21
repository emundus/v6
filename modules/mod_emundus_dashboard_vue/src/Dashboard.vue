<template>
  <div id="app">
    <!--    <a style="margin-left: 1em" class="cta-block pointer" @click="enableDrag = !enableDrag">
          <em class="fas fa-pen" data-toggle="tooltip" data-placement="top"></em>
        </a>-->
    <draggable
        v-model="widgets"
        :disabled="!enableDrag"
        handle=".handle"
        drag-class="plugin-drag"
        chosen-class="plugin-chosen"
        ghost-class="plugin-ghost">
      <div v-if="this.programmeFilter == 1" class="program-filter">
        <label>{{translations.filterByProgram}}</label>
        <select v-model="selectedProgramme">
          <option :value="null" selected>{{translations.all}}</option>
          <option v-for="programme in programmes" v-bind:key="programme.id" :value="programme.code">{{programme.label}}</option>
        </select>
      </div>
      <template v-if="widgets.length > 0">
        <div v-for="(widget,index) in widgets" :id="widget.name + '_' + index" 
        :class="enableDrag ? 'jello-horizontal handle' : widget.name + '-' + widget.class" :key="widget.name + '_' + index">
          <Custom v-if="widget.name === 'custom'" :widget="widget" @forceUpdate="$forceUpdate"/>

          <!-- Sciences Po widgets -->
          <KeyFigures v-if="widget.name === 'key_figures'" :program="selectedProgramme" :colors="colors"/>
          <FilesNumberByDate v-if="widget.name === 'files_number_by_status_and_date'" :program="selectedProgramme" :colors="colors"/>
          <FilesBySession v-if="widget.name === 'files_by_session'" :colors="colors"/>
          <FilesBySessionPrecollege v-if="widget.name === 'files_by_session_precollege'" :colors="colors"/>
          <FilesByCourses v-if="widget.name === 'files_by_courses'" :colors="colors" :session="1"/>
          <FilesByCourses v-if="widget.name === 'files_by_courses'" :colors="colors" :session="2"/>
          <FilesByCoursesPrecollege v-if="widget.name === 'files_by_courses_precollege'" :colors="colors" :session="1"/>
          <FilesByCoursesPrecollege v-if="widget.name === 'files_by_courses_precollege'" :colors="colors" :session="2"/>
          <FilesByNationalities v-if="widget.name === 'files_by_nationalities'" :program="selectedProgramme" :colors="colors"/>
        </div>
      </template>
    </draggable>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import axios from "axios";
import KeyFigures from "@/components/sciencespo/KeyFigures";
import FilesNumberByDate from "@/components/sciencespo/FilesNumberByStatusAndDate";
import FilesBySession from "@/components/sciencespo/FilesBySession";
import FilesBySessionPrecollege from "@/components/sciencespo/FilesBySessionPrecollege";
import FilesByCourses from "@/components/sciencespo/FilesByCourses";
import FilesByCoursesPrecollege from "@/components/sciencespo/FilesByCoursesPrecollege";
import FilesByNationalities from "@/components/sciencespo/FilesByNationalities";
import Custom from "@/components/Custom";

export default {
  name: 'App',
  props: {
    programmeFilter: Number
  },
  components: {
    Custom,
    draggable,
    KeyFigures,
    FilesNumberByDate,
    FilesBySession,
    FilesBySessionPrecollege,
    FilesByCourses,
    FilesByCoursesPrecollege,
    FilesByNationalities,
  },
  data() {
    return {
      programmes: [],
      selectedProgramme: null,
      widgets: [],
      colors: "",
      translations:{
        all: "",
        filterByProgram: "",
      },
      status: null,
      enableDrag: false
    }
  },
  created() {
    this.getTranslations();
    this.getWidgets();
    this.getPaletteColors();
    if(this.programmeFilter == 1){
      this.getProgrammes();
    }
  },
  methods: {
    getTranslations() {
      this.translations = {
        all: this.translate("COM_EMUNDUS_DASHBOARD_ALL_PROGRAMMES"),
        filterByProgram: this.translate("COM_EMUNDUS_DASHBOARD_FILTER_BY_PROGRAMMES"),
      };
    },
    getWidgets(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getwidgets",
      }).then(response => {
        this.widgets = response.data.data;
      });
    },

    getPaletteColors(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getpalettecolors",
      }).then(response => {
        this.colors = response.data.data;
      });
    },

    getProgrammes(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=program&task=getallprogram",
      }).then(response => {
        this.programmes = response.data.data;
      });
    },
  }
}
</script>

<style scoped>
#app > div{
  display: flex;
  flex-wrap: wrap;
  flex-direction: row;
}

#app > div > div{
  width: 100%;
  margin: 0 0 30px 0;
}

.tchooz-widget{
  height: 400px;
}

.cta-block{
  position: absolute;
  right: 20px;
  top: 30px;
  color: #b0b0bf;
}
.pointer{
  cursor: pointer;
}
.jello-horizontal {
  -webkit-animation: vibrate-1 1s infinite;
  animation: vibrate-1 1s infinite;
}
/* ----------------------------------------------
 * Generated by Animista on 2020-12-24 16:21:1
 * Licensed under FreeBSD License.
 * See http://animista.net/license for more info.
 * w: http://animista.net, t: @cssanimista
 * ---------------------------------------------- */

/**
 * ----------------------------------------
 * animation vibrate-1
 * ----------------------------------------
 */
@-webkit-keyframes vibrate-1 {
  0% {
    -webkit-transform: translate(0);
    transform: translate(0);
  }
  20% {
    -webkit-transform: translate(-2px, 2px);
    transform: translate(-2px, 2px);
  }
  40% {
    -webkit-transform: translate(-2px, -2px);
    transform: translate(-2px, -2px);
  }
  60% {
    -webkit-transform: translate(2px, 2px);
    transform: translate(2px, 2px);
  }
  80% {
    -webkit-transform: translate(2px, -2px);
    transform: translate(2px, -2px);
  }
  100% {
    -webkit-transform: translate(0);
    transform: translate(0);
  }
}
@keyframes vibrate-1 {
  0% {
    -webkit-transform: translate(0);
    transform: translate(0);
  }
  20% {
    -webkit-transform: translate(-2px, 2px);
    transform: translate(-2px, 2px);
  }
  40% {
    -webkit-transform: translate(-2px, -2px);
    transform: translate(-2px, -2px);
  }
  60% {
    -webkit-transform: translate(2px, 2px);
    transform: translate(2px, 2px);
  }
  80% {
    -webkit-transform: translate(2px, -2px);
    transform: translate(2px, -2px);
  }
  100% {
    -webkit-transform: translate(0);
    transform: translate(0);
  }
}

/**** END ****/

.handle{
  cursor: grab;
}

.program-filter{
  text-align: center;
  margin-bottom: 10px;
}
.program-filter label{
  margin-bottom: 0 !important;
  margin-right: 10px;
}
</style>
