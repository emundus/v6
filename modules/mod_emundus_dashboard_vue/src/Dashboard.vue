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
          <option v-for="programme in programmes" :value="programme.code">{{programme.label}}</option>
        </select>
      </div>
      <div v-for="(widget,index) in widgets" :id="widget.name + '_' + index" :class="enableDrag ? 'jello-horizontal handle' : ''" :key="widget.name + '_' + index">
        <Faq v-if="widget.name === 'faq'"/>
        <FilesNumberByStatus v-if="widget.name === 'files_number_by_status'" :colors="colors"/>
        <UsersByMonth v-if="widget.name === 'users_by_month'" :colors="colors"/>
        <Tips v-if="widget.name === 'tips'"/>
        <DemoCounter v-if="widget.name === 'demo_counter'"/>

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
    </draggable>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import axios from "axios";
import Faq from "@/components/Faq";
import FilesNumberByStatus from "@/components/FilesNumberByStatus";
import Tips from "@/components/Tips";
import UsersByMonth from "@/components/UsersByMonth";
import DemoCounter from "@/components/DemoCounter";
import KeyFigures from "@/components/sciencespo/KeyFigures";
import FilesNumberByDate from "@/components/sciencespo/FilesNumberByStatusAndDate";
import FilesBySession from "@/components/sciencespo/FilesBySession";
import FilesBySessionPrecollege from "@/components/sciencespo/FilesBySessionPrecollege";
import FilesByCourses from "@/components/sciencespo/FilesByCourses";
import FilesByCoursesPrecollege from "@/components/sciencespo/FilesByCoursesPrecollege";
import FilesByNationalities from "@/components/sciencespo/FilesByNationalities";

export default {
  name: 'App',
  props: {
    programmeFilter: Number
  },
  components: {
    DemoCounter,
    UsersByMonth,
    Tips,
    FilesNumberByStatus,
    Faq,
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
      campaigns: [],
      programmes: [],
      selectedProgramme: null,
      widgets: [],
      colors: "",
      translations:{
        all: Joomla.JText._("COM_EMUNDUS_DASHBOARD_ALL_PROGRAMMES"),
        filterByProgram: Joomla.JText._("COM_EMUNDUS_DASHBOARD_FILTER_BY_PROGRAMMES"),
      },
      status: null,
      lastCampaigns: 0,
      enableDrag: false
    }
  },
  created() {
    this.getWidgets();
    this.getPaletteColors();
    if(this.programmeFilter == 1){
      this.getProgrammes();
    }
  },
  methods: {
    getWidgets(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getwidgets",
      }).then(response => {
        response.data.data.forEach((data) => {
          switch (data) {
            case 'last_campaign_active':
              if(this.campaigns.length == 0) {
                this.getLastCampaignsActive();
              }
              this.widgets.push({
                name: data,
                cindex: this.lastCampaigns
              });
              this.lastCampaigns++;
              break;
            default:
              this.widgets.push({
                name: data,
              });
          }
        });
        if(response.data.data.indexOf('last_campaign_active') !== -1){
          this.getLastCampaignsActive();
        }
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

    getLastCampaignsActive(){
      axios.get(
          'index.php?option=com_emundus_onboard&controller=dashboard&task=getLastCampaignActive'
      ).then(response => {
        this.campaigns = response.data.data;
      }).catch(e => {
        console.log(e);
      });
    }
  }
}
</script>

<style scoped>
.tchooz-widget{
  height: 25vh;
  margin-bottom: 30px !important;
  margin-left: 0px !important;
  padding-left: 30px !important;
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
