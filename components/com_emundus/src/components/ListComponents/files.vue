<template>
  <div>
    <div class="section-recherche" :class="total == 0 ? 'noneDiscoverRecherche' : ''">
      <div class="w-container">
        <div class="wrapper-rechercher w-row">
          <div class="column-recherche w-col w-col-6">
            <div class="form-recherche w-form">
              <form id="email-form-2" name="email-form-2" data-name="Email Form 2" class="form">
                <div class="w-row">
                  <div class="column-5 w-col w-col-8">
                    <input
                      type="text"
                      class="text-recherche-hp w-input"
                      maxlength="256"
                      name="Rechercher"
                      data-name="Rechercher"
                      placeholder="Rechercher..."
                      id="Rechercher"
                      required=""
                    />
                  </div>
                  <div class="w-clearfix w-col w-col-4">
                    <a href="#" class="sauvegarder w-inline-block"></a
                    ><a href="#" class="recherche w-inline-block" data-ix="show-result"></a>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <div class="column-recherche w-col w-col-6">
            <div class="form-recherche w-form">
              <form id="email-form-2" name="email-form-2" data-name="Email Form 2" class="form">
                <select id="Profil" name="Profil" data-name="Profil" class="profil-search w-select"
                  ><option value="">Dossier</option
                  ><option value="">Evaluation</option
                  ><option value="Décision">Décision</option></select
                >
              </form>
            </div>
          </div>
        </div>
        <div class="wrapper-add-recherche">
          <div class="w-form">
            <form id="email-form" name="email-form" data-name="Email Form">
              <div class="container-flexbox-choisir-ou-plus-droite w-clearfix">
                <div class="wraper-dropdown-search">
                  <a
                    @click="
                      prog = 'reset';
                      onProgChange();
                    "
                    class="link"
                    >x</a
                  ><select
                    id="Programme"
                    name="Programme"
                    data-name="Programme"
                    class="dropdown-toggle-recherche w-select"
                    @change="
                      prog = '';
                      onProgChange($event);
                    "
                    ><option :selected="this.prog == 'reset' ? true : false" value=""
                      >Programme</option
                    ><option v-for="(ProgCat, index) in distinctProg" :key="index">{{
                      ProgCat
                    }}</option></select
                  >
                </div>
                <div class="wraper-dropdown-search">
                  <select
                    id="Campagne"
                    name="Campagne"
                    data-name="Campagne"
                    class="dropdown-toggle-recherche w-select"
                    @change="
                      camp = '';
                      onCampChange($event);
                    "
                    ><option :selected="this.camp == 'reset' ? true : false" value=""
                      >Campagne</option
                    ><option v-for="(CampCat, index) in distinctCamp" :key="index">{{
                      CampCat
                    }}</option></select
                  ><a
                    @click="
                      camp = 'reset';
                      onCampChange();
                    "
                    class="link"
                    >x</a
                  >
                </div>
                <div class="wraper-dropdown-search">
                  <select
                    id="Ann-e"
                    name="Ann-e"
                    data-name="Année"
                    class="dropdown-toggle-recherche w-select"
                    @change="
                      session = '';
                      onSessionChange($event);
                    "
                    ><option :selected="this.session == 'reset' ? true : false" value=""
                      >Session</option
                    ><option v-for="(SessionCat, index) in distinctSession" :key="index">{{
                      SessionCat
                    }}</option></select
                  ><a
                    @click="
                      session = 'reset';
                      onSessionChange();
                    "
                    class="link"
                    >x</a
                  >
                </div>
                <div class="wraper-dropdown-search">
                  <select
                    id="Statut"
                    name="Statut"
                    data-name="Statut"
                    class="dropdown-toggle-recherche w-select"
                    @change="
                      status = '';
                      onStatusChange($event);
                    "
                    ><option :selected="this.status == 'reset' ? true : false" value=""
                      >Statut</option
                    ><option v-for="(StatusCat, index) in distinctStatus" :key="index">{{
                      StatusCat
                    }}</option></select
                  ><a
                    @click="
                      status = 'reset';
                      onStatusChange();
                    "
                    class="link"
                    >x</a
                  >
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <table v-show="total != 0" class="table">
      <thead class="content-results">
        <tr>
          <th class="row-check" scope="col"></th>
          <th
            v-for="(categorie, index) in categoriesName"
            :key="index"
            scope="col"
            v-show="newCategories.includes(index)"
          >
            {{ categorie }}
          </th>
          <div class="pin-add-column">
            <a @click="show()" class="pin-add w-inline-block"><div class="add-col">+</div></a>
          </div>
        </tr>
      </thead>
      <tbody class="title-results">
        <tr v-for="(file, index) in files" :key="index">
          <th class="row-check" scope="row">
            <input
              type="checkbox"
              id="checkbox-77"
              name="checkbox"
              data-name="Checkbox"
              class="w-checkbox-input"
            />
          </th>
          <th
            v-for="(catValue, valueIndex) in categoriesValue"
            :key="valueIndex"
            scope="row"
            v-show="newCategories.includes(valueIndex)"
          >
            {{
              catValue == "lastname" || catValue == "firstname"
                ? filesUsers[index][catValue]
                : file[catValue]
            }}
          </th>
        </tr>
      </tbody>
    </table>

    <div v-show="total == 0" class="noneDiscover">
      {{ noDossiers }}
    </div>

    <modal name="modalAddCat">
      <div class="modalHeader">Select a new category</div>
      <div class="hr"></div>
      <div v-for="(categorie, index) in categoriesName" :key="index" class="modalContent">
        <div
          class="modalNewCategory"
          :id="index"
          :class="newCategories.includes(index) ? 'selectedElem' : ''"
          @click="toggleActive(index)"
        >
          {{ categorie }}
        </div>
      </div>
      <div class="hr"></div>
      <div class="modalFooter">
        <button class="modalAdd" @click="resetCategories()" type="button">{{ ResetButton }}</button>
        <button class="modalCancel" @click="removeCategories()" type="button">
          {{ RemoveButton }}
        </button>
        <button class="modalCancel" @click="cancelCategories()" type="button">
          {{ CloseButton }}
        </button>
      </div>
    </modal>
  </div>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "files",

  data() {
    return {
      total: -1,

      prog: "",
      camp: "",
      session: "",
      status: "",

      files: [],
      filesUsers: [],
      campaignsId: [],
      distinctProg: [],
      distinctCamp: [],
      distinctStatus: [],
      distinctSession: [],

      filtersProg: "&prog=",
      filtersCamp: "&camp=",
      filtersSession: "&session",
      filtersStatus: "&status=",

      newCategories: [0, 1, 2, 3, 4],

      categoriesName: [
        "Nom",
        "Prénom",
        "Campagne",
        "Programme",
        "Statut",
        "Session",
        "Created",
        "Profile Label",
        "Start date",
        "End date"
      ],

      categoriesValue: [
        "lastname",
        "firstname",
        "label",
        "program_label",
        "status_value",
        "year",
        "create_date_time",
        "profile_label",
        "start_date",
        "end_date"
      ],

      currentElement: "",
      publishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
      unpublishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
      Lastname: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_LASTNAME"),
      Firstname: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_FIRSTNAME"),
      Campaign: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_CAMPAIGN"),
      Program: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_PROGRAM"),
      Status: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_STATUS"),
      Start: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_START"),
      End: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_END"),
      Created_at: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_CREATED"),
      Profile_name: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_PROFILE"),
      CloseButton: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_CLOSE"),
      ResetButton: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_RESET"),
      RemoveButton: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_REMOVE"),
      noDossiers: this.translate("COM_EMUNDUS_ONBOARD_NOFILES")
    };
  },

  computed: {
    console: () => console
  },

  created() {
    axios
      .get(`index.php?option=com_emundus&controller=files&task=getallfiles`)
      .then(response => {
        this.total = response.data.data.length;

        for (let i = 0; i < response.data.data.length; i++) {
          this.files.push(response.data.data[i][0]);
          this.filesUsers.push(response.data.data[i][1]);
          this.campaignsId.push(response.data.data[i][0].id);
        }
      })
      .then(() => {
        axios
          .get("index.php?option=com_emundus&controller=files&task=getdistincts", {
            params: {
              ids: this.campaignsId
            },
            paramsSerializer: function(params) {
              return qs.stringify(params);
            }
          })
          .then(response => {
            for (let i = 0; i < response.data.data.length; i++) {
              this.distinctProg.push(response.data.data[i].distinctProg);
              this.distinctCamp.push(response.data.data[i].distinctCamp);
              this.distinctStatus.push(response.data.data[i].distinctStatus);
              this.distinctSession.push(response.data.data[i].distinctSession);
            }
            const distinct = (value, index, self) => {
              return self.indexOf(value) === index;
            };
            this.distinctProg = this.distinctProg.filter(distinct);
            this.distinctCamp = this.distinctCamp.filter(distinct);
            this.distinctStatus = this.distinctStatus.filter(distinct);
            this.distinctSession = this.distinctSession.filter(distinct);
          })
          .catch(e => {
            console.log(e);
          });
      })
      .catch(e => {
        console.log(e);
      });
  },

  methods: {
    changeDate(dbDate) {
      const regexYear = /\d{4}/gm;
      const regexMonth = /-\d{2}-/gm;
      const regexDay = /-\d{2} /gm;
      const str = dbDate;
      let m;
      var formatDate = "";

      while ((m = regexDay.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexDay.lastIndex) {
          regexDay.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((DD, groupIndex) => {
          formatDate = DD.substring(1, 3) + "/";
        });
      }

      while ((m = regexMonth.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexMonth.lastIndex) {
          regexMonth.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((MM, groupIndex) => {
          formatDate = formatDate + MM.substring(1, 3) + "/";
        });
      }

      while ((m = regexYear.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexYear.lastIndex) {
          regexYear.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((yy, groupIndex) => {
          formatDate = formatDate + yy;
        });
      }

      return formatDate;
    },

    changeFullDate(dbDate) {
      const regexYear = /\d{4}/gm;
      const regexMonth = /-\d{2}-/gm;
      const regexDay = /-\d{2} /gm;
      const regexHours = /\d{2}:\d{2}:\d{2}/gm;
      const str = dbDate;
      let m;
      var formatDate = "";

      while ((m = regexDay.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexDay.lastIndex) {
          regexDay.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((DD, groupIndex) => {
          formatDate = DD.substring(1, 3) + "/";
        });
      }

      while ((m = regexMonth.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexMonth.lastIndex) {
          regexMonth.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((MM, groupIndex) => {
          formatDate = formatDate + MM.substring(1, 3) + "/";
        });
      }

      while ((m = regexYear.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexYear.lastIndex) {
          regexYear.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((yy, groupIndex) => {
          formatDate = formatDate + yy;
        });
      }

      while ((m = regexHours.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexHours.lastIndex) {
          regexHours.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((HH, groupIndex) => {
          formatDate = formatDate + " à " + HH;
        });
      }

      return formatDate;
    },

    show() {
      this.$modal.show("modalAddCat");
    },

    hide() {
      this.$modal.hide("modalAddCat");
    },

    toggleActive(elementId) {
      this.currentElement = document.getElementById(elementId);

      if (this.newCategories.includes(elementId)) {
        this.deselectElement(elementId);
      } else {
        this.selectElement(elementId);
      }
    },

    selectElement(elementId) {
      this.newCategories.push(elementId);
    },

    deselectElement(elementId) {
      const index = this.newCategories.indexOf(elementId);
      if (index > -1) {
        this.newCategories.splice(index, 1);
      }
    },

    resetCategories() {
      this.newCategories = [0, 1, 2, 3, 4];
    },

    removeCategories() {
      this.newCategories = [0, 1];
    },

    cancelCategories() {
      this.hide();
    },

    onProgChange(event) {
      if (event == undefined) {
        this.filtersProg = "&prog=";
      } else {
        this.filtersProg = "&prog=" + event.target.value;
      }
      this.validateFilters();
    },

    onCampChange(event) {
      if (event == undefined) {
        this.filtersCamp = "&camp=";
      } else {
        this.filtersCamp = "&camp=" + event.target.value;
      }
      this.validateFilters();
    },

    onSessionChange(event) {
      if (event == undefined) {
        this.filtersSession = "&session=";
      } else {
        this.filtersSession = "&session=" + event.target.value;
      }
      this.validateFilters();
    },

    onStatusChange(event) {
      if (event == undefined) {
        this.filtersStatus = "&status=";
      } else {
        this.filtersStatus = "&status=" + event.target.value;
      }
      this.validateFilters();
    },

    validateFilters() {
      this.filters = this.filtersProg + this.filtersCamp + this.filtersSession + this.filtersStatus;
      this.allFilters(this.filters);
    },

    allFilters(filters) {
      axios
        .get("index.php?option=com_emundus&controller=files&task=getallfiles" + filters)
        .then(response => {
          this.total = response.data.data.length;

          this.files = [];
          this.filesUsers = [];

          for (let i = 0; i < response.data.data.length; i++) {
            this.files.push(response.data.data[i][0]);
            this.filesUsers.push(response.data.data[i][1]);
          }
        })
        .catch(e => {
          console.log(e);
        });
    }
  }
};
</script>

<style scoped>
.noneDiscover {
  position: absolute;
  top: 45%;
  left: 40%;
  font-size: 20px;
  color: #1b1f3c;
}

.noneDiscoverRecherche {
  margin-top: -10%;
}

.link:hover {
  cursor: pointer;
}
</style>
