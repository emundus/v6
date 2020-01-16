<template>
  <div id="dossiersListing">
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
    />
    <div id="dossiers" v-for="(dossier, index) in dossiers" :key="index">
      <div class="col-md-12 main-page-application-title">{{ dossier.label }}</div>
      <div class="col-xs-12 col-md-6 main-page-file-info">
        <p class="em-tags-display">{{ dossier.fnum }}</p>
        <section class="col-xs-6 col-md-2" style="width:180px; float: left; margin-left: 550px;">
          <svg
            v-bind:id="dossier.fnum"
            style="position: absolute; margin-top: -100px;"
            xmlns="http://www.w3.org/2000/svg"
            version="1.1"
            viewBox="0 0 194 186"
            class="circliful"
          >
            undefined
            <circle
              cx="100"
              cy="100"
              r="57"
              class="border"
              fill="none"
              stroke="#ccc"
              stroke-width="15"
              stroke-dasharray="360"
              transform="rotate(-90,100,100)"
            />
            <circle
              class="circle"
              cx="100"
              cy="100"
              r="57"
              fill="none"
              stroke="#5A879E"
              stroke-width="15"
              stroke-dasharray="180 20000"
              transform="rotate(-90,100,100)"
            />
            <circle cx="100" cy="100" r="28.5" fill="none" />undefined
            <text
              class="timer"
              text-anchor="middle"
              x="100"
              y="110"
              style="font-size: 22px;"
              fill="#aaa"
            >
              {{
              Math.round(
              (forms[dossier.fnum] + attachements[dossier.fnum]) / 2
              )
              }}%
            </text>
          </svg>
        </section>
        <a
          style="margin-right: -1px;"
          class="btn btn-warning"
          @click="openfile(dossier.fnum, firstpage[dossier.fnum])"
        >
          <em class="far fa-folder-open"></em>Ouvrir le dossier
        </a>
        <a class="btn btn-info btn-xs" @click="printfile(dossier.fnum)">
          <em style="font-size: 17px; width: 12px; line-height: 20px;" class="icon-print"></em>
        </a>
        <button class="trashAdd" @click="deletefile(dossier.fnum)">
          <div class="far fa-trash-alt"></div>
        </button>
      </div>
      <div
        class="main-page-file-progress-label"
        style="margin-left: 585px; position: absolute; margin-top: 130px;"
      >Statut : {{ dossier.value }}</div>
    </div>
  </div>
</template>

<script>
import axios from "../../node_modules/axios";

export default {
  name: "DossiersListing",
  props: {
    dossiers: Object,
    forms: Object,
    attachements: Object,
    firstpage: Object
  },
  methods: {
    deletefile: function(fnum) {
      axios
        .post("index.php?option=com_emundus&task=deletefile&fnum=" + fnum)
        .then(response => this.listdossiers());
    },
    openfile: function(fnum, firstPage) {
      location.replace(
        "index.php?option=com_emundus&task=openfile&fnum=" +
          fnum +
          "&redirect=" +
          btoa(firstPage["link"])
      );
    },
    printfile: function(fnum) {
      location.replace("index.php?option=com_emundus&task=pdf&fnum=" + fnum);
    },
    listdossiers: function() {
      axios
        .get(
          "index.php?option=com_ajax&module=emundus_applications&format=json&method=getdossiers"
        )
        .then(response => (this.dossiers = response.data))
        .catch(function(error) {
          console.log(error);
        });
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
#dossiers {
  height: 200px;
}

button.trashAdd {
  color: white;
  background-color: #1b1f3c;
  height: 38px;
  width: 40px;
  font-size: 17px;
  border: none;
}

em.fa-folder-open {
  margin-right: 5px;
}

button.trashAdd:hover {
  color: #1b1f3c;
  background-color: white;
  border: 1px solid #1b1f3c;
  cursor: pointer;
}
</style>
