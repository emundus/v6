<template>
  <div class="section-principale">
    <div class="w-container">
      <form id="campaign-form" @submit.prevent="submit">
        <div class="sous-container">
          <div class="icon-title"></div>
          <h2 class="heading">{{ Parameter }}</h2>
          <p class="paragraphe-sous-titre">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
            elementum tristique.
          </p>
          <div class="form-group">
            <input
              type="text"
              class="form__input field-general w-input"
              :placeholder="CampName"
              v-model="form.label"
              :class="{ 'is-invalid': submitted && $v.form.label.$error }"
            />
          </div>
          <div class="w-row">
            <div class="w-col w-col-6">
              <div class="w-form">
                <datetime
                  :placeholder="StartDate"
                  type="datetime"
                  id="start_date"
                  v-model="form.start_date"
                  :language="fr"
                ></datetime>
              </div>
            </div>
            <div class="w-col w-col-6">
              <div class="w-form">
                <datetime
                  :placeholder="EndDate"
                  type="datetime"
                  id="end_date"
                  v-model="form.end_date"
                  :language="fr"
                ></datetime
                ><label class="w-checkbox"
                  ><input
                    type="checkbox"
                    id="checkbox-2"
                    name="checkbox-2"
                    data-name="Checkbox 2"
                    class="w-checkbox-input"
                    :checked="this.form.end_date == '' ? true : false"
                    @click="
                      endDateCheckbox = !endDateCheckbox;
                      changeEndDate();
                    "
                  /><span for="checkbox-2" class="checkbox-label w-form-label">{{
                    PasDeFin
                  }}</span></label
                >
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="published">{{ Publish }}</label>
            <div class="toggle">
              <input
                type="checkbox"
                true-value="1"
                false-value="0"
                class="check"
                id="published"
                name="published"
                v-model="form.published"
              />
              <strong class="b switch"></strong>
              <strong class="b track"></strong>
            </div>
          </div>
          <div class="icon-title"></div>
        </div>
        <div class="divider"></div>
        <div class="sous-container">
          <h2 class="heading">{{ Information }}</h2>
          <p class="paragraphe-sous-titre">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
            elementum tristique.
          </p>
          <div class="form-group">
            <textarea
              type="textarea"
              class="form__input field-general w-input"
              :placeholder="Resume"
              v-model="form.short_description"
            />
          </div>
          <div class="form-group">
            <textarea
              type="textarea"
              class="form__input field-general w-input"
              :placeholder="Description"
              v-model="form.description"
            />
          </div>
          <div class="icon-title informations"></div>
        </div>
        <div class="divider"></div>
        <div class="sous-container last-container">
          <h2 class="heading">{{ Program }}</h2>
          <p class="paragraphe-sous-titre">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
            elementum tristique.
          </p>
          <div class="form-group container-flexbox-choisir-ou-plus w-clearfix">
            <select
              class="dropdown-toggle w-select"
              v-model="form.training"
              v-on:change="setCategory"
            >
              <option value="">{{ ChooseProg }}</option>
              <option
                v-for="(item, index) in this.programs"
                v-bind:value="item.code"
                v-bind:data-category="item.programmes"
                :key="index"
                >{{ item.label }}</option
              >
            </select>
            <button
              @click="isHiddenProgram = !isHiddenProgram"
              id="add-program"
              class="plus w-inline-block"
              type="button"
            >
              +
            </button>
          </div>

          <div class="sous-container program-addCampaign" v-if="isHiddenProgram">
            <h2 class="heading">{{ AddProgram }}</h2>
            <p class="paragraphe-sous-titre">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in
              eros elementum tristique.
            </p>
            <div class="w-form">
              <div class="form-group">
                <input
                  type="text"
                  class="form__input field-general w-input"
                  :placeholder="ProgName"
                  v-model="programForm.label"
                />
              </div>

              <div class="form-group">
                <input
                  type="text"
                  class="form__input field-general w-input"
                  :placeholder="ProgCode"
                  v-model="programForm.code"
                />
              </div>

              <div class="form-group container-flexbox-choisir-ou-plus w-clearfix">
                <autocomplete
                  @searched="onSearchCategory"
                  :name="ChooseCategory"
                  :items="this.categories"
                  :year="programForm.programmes"
                />
              </div>

              <input
                v-if="isHiddenCategory"
                type="text"
                class="form__input field-general w-input"
                :placeholder="NameCategory"
                v-model="new_category"
              />

              <div class="form-group controls">
                <editor :text="programForm.notes" v-model="programForm.notes"></editor>
              </div>

              <div class="form-group">
                <label for="published">{{ Publish }}</label>
                <div class="toggle">
                  <input
                    type="checkbox"
                    true-value="1"
                    false-value="0"
                    class="check"
                    id="published"
                    name="published"
                    v-model="programForm.published"
                  />
                  <strong class="b switch"></strong>
                  <strong class="b track"></strong>
                </div>
              </div>

              <div class="form-group">
                <label for="apply">{{ DepotDeDossier }}</label>
                <div class="toggle">
                  <input
                    type="checkbox"
                    true-value="1"
                    false-value="0"
                    class="check"
                    id="apply"
                    name="apply"
                    v-model="programForm.apply_online"
                  />
                  <strong class="b switch"></strong>
                  <strong class="b track"></strong>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group container-flexbox-choisir-ou-plus w-clearfix">
            <autocomplete
              @searched="onSearchYear"
              :name="PickYear"
              :items="this.session"
              :year="form.year"
            />
          </div>

          <div class="form-group container-flexbox-choisir-ou-plus w-clearfix">
            <autocomplete
              @searched="onSearchProfile"
              :name="ChooseProfile"
              :items="this.profiles"
              :year="form.profileLabel"
            />
          </div>
          <div class="icon-title programme"></div>
        </div>
        <div class="section-sauvegarder-et-continuer">
          <div class="w-container">
            <div class="container-evaluation w-clearfix">
              <button
                type="button"
                class="bouton-sauvergarder-et-continuer w-button"
                @click="quit = 1; submit()"
              >
                {{ Continuer }}
              </button>
              <button
                type="button"
                class="bouton-sauvergarder-et-continuer w-quitter w-button"
                @click="quit = 0; submit()"
              >
                {{ Quitter }}
              </button>
              <button
                type="button"
                class="bouton-sauvergarder-et-continuer w-retour w-button"
                onclick="window.location.href='campaigns'"
              >
                {{ Retour }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { required } from "vuelidate/lib/validators";
import axios from "axios";
import { Datetime } from "vue-datetime";
import { Settings } from "luxon";
import Editor from "../components/editor";
import Autocomplete from "../components/autocomplete";

const qs = require("qs");

export default {
  name: "addCampaign",

  components: {
    Datetime,
    Editor,
    Autocomplete
  },

  quit: 1,

  props: {
    campaign: Number,
    actualLanguage: String
  },

  data: () => ({
    isHiddenYear: false,
    isHiddenProgram: false,
    isHiddenCategory: false,

    endDateCheckbox: true,
    olderDate: "",

    programs: [],
    years: [],
    profiles: [],
    categories: [],

    allProfiles: [],

    new_category: "",
    new_program: "",

    session: [],
    cats: [],
    search: "",

    form: {
      label: "",
      start_date: "",
      end_date: "",
      short_description: "",
      description: "",
      training: "",
      year: "",
      profile_id: "",
      profileLabel: "",
      published: 1
    },

    programForm: {
      code: "",
      label: "",
      notes: "",
      programmes: "",
      published: 1,
      apply_online: 1
    },

    year: {
      label: "",
      code: "",
      schoolyear: "",
      published: 1,
      profile_id: "",
      programmes: ""
    },

    Parameter: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_PARAMETER"),
    CampName: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_CAMPNAME"),
    StartDate: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_STARTDATE"),
    EndDate: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_ENDDATE"),
    PasDeFin: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_PASDEFIN"),
    Information: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_INFORMATION"),
    Resume: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_RESUME"),
    Description: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_DESCRIPTION"),
    Program: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_PROGRAM"),
    AddProgram: Joomla.JText._("COM_EMUNDUSONBOARD_ADDPROGRAM"),
    ChooseProg: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_CHOOSEPROG"),
    PickYear: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_PICKYEAR"),
    ChooseProfile: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_CHOOSEPROFILE"),
    Retour: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_RETOUR"),
    Quitter: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_QUITTER"),
    Continuer: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_CONTINUER"),
    Publish: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER_PUBLISH"),
    DepotDeDossier: Joomla.JText._("COM_EMUNDUSONBOARD_DEPOTDEDOSSIER"),
    ProgName: Joomla.JText._("COM_EMUNDUSONBOARD_PROGNAME"),
    ProgCode: Joomla.JText._("COM_EMUNDUSONBOARD_PROGCODE"),
    ChooseCategory: Joomla.JText._("COM_EMUNDUSONBOARD_CHOOSECATEGORY"),
    NameCategory: Joomla.JText._("COM_EMUNDUSONBOARD_NAMECATEGORY"),

    submitted: false
  }),

  validations: {
    form: {
      label: { required },
      start_date: { required },
      training: { required },
      year: { required },
      profile_id: { required }
    }
  },

  created() {
    Settings.defaultLocale = this.actualLanguage;
    if (this.campaign !== "") {
      axios
        .get(
          `index.php?option=com_emundus_onboard&controller=campaign&task=getcampaignbyid&id=${this.campaign}`
        )
        .then(response => {
          this.form.label = response.data.data.label;
          this.form.published = response.data.data.published;
          this.form.description = response.data.data.description;
          this.form.short_description = response.data.data.short_description;
          this.form.start_date = response.data.data.start_date;
          this.form.end_date = response.data.data.end_date;
          this.form.training = response.data.data.training;
          this.form.year = response.data.data.year;
          this.form.profile_id = response.data.data.profile_id;
          this.form.profileLabel = response.data.data.profileLabel;
          this.form.start_date = this.changeDate(this.form.start_date);
          this.form.end_date = this.changeDate(this.form.end_date);
          if (this.form.end_date == "0000-00-00T00:00:00.000Z") {
            this.form.end_date = "";
          } else {
            this.olderDate = this.form.end_date;
          }
        })
        .catch(e => {
          console.log(e);
        });
    }
    axios
      .get("index.php?option=com_emundus_onboard&controller=campaign&task=getallprofiles")
      .then(response => {
        this.allProfiles = response.data.data;
      })
      .catch(e => {
        console.log(e);
      });
    axios
      .get("index.php?option=com_emundus_onboard&controller=program&task=getallprogram")
      .then(response => {
        this.programs = response.data.data;
      })
      .catch(e => {
        console.log(e);
      });

    axios
      .get("index.php?option=com_emundus_onboard&controller=campaign&task=getyears")
      .then(response => {
        this.years = response.data.data;

        for (var i = 0; i < this.years.length; i++) {
          this.session.push(this.years[i].schoolyear);
        }
      })
      .catch(e => {
        console.log(e);
      });

    axios
      .get("index.php?option=com_emundus_onboard&controller=campaign&task=getapplicantprofiles")
      .then(response => {
        for (var i = 0; i < response.data.data.length; i++) {
          this.profiles.push(response.data.data[i].label);
        }
      })
      .catch(e => {
        console.log(e);
      });

    axios
      .get("index.php?option=com_emundus_onboard&controller=program&task=getprogramcategories")
      .then(response => {
        this.categories = response.data.data;

        for (var i = 0; i < this.categories.length; i++) {
          this.cats.push(this.categories[i]);
        }
      })
      .catch(e => {
        console.log(e);
      });
  },

  methods: {
    setCategory(e) {
      this.year.programmes = e.target.options[e.target.options.selectedIndex].dataset.category;
    },

    submit() {
      this.submitted = true;

      if (this.form.end_date == "") {
        this.form.end_date = null;
      }

      if (this.programForm.code !== "") {
        this.form.training = this.programForm.code;
      }

      if (!this.profiles.includes(this.form.profileLabel)) {
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=campaign&task=createprofile",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({ profile: this.form.profileLabel })
        })
          .then(response => {
            this.form.profile_id = response.data.data;
          })
          .catch(error => {
            console.log(error);
          });
      } else {
        for (var i = 0; i < this.allProfiles.length; i++) {
          if (this.allProfiles[i].label == this.form.profileLabel) {
            this.form.profile_id = this.allProfiles[i].id;
          }
        }
      }

      // stop here if form is invalid
      this.$v.$touch();
      if (this.$v.$invalid) {
        return;
      }

      // Set year object values
      this.year.label = this.form.label;
      this.year.code = this.form.training;
      this.year.schoolyear = this.form.year;
      this.year.published = this.form.published;
      this.year.profile_id = this.form.profile_id;

      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=program&task=createprogram",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({ body: this.programForm })
      })
        .then(() => {
          if (this.campaign !== "") {
            axios({
              method: "post",
              url: "index.php?option=com_emundus_onboard&controller=campaign&task=updatecampaign",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({ body: this.form, cid: this.campaign })
            })
              .then(response => {
                this.quitFunnelOrContinue(this.quit);
              })
              .catch(error => {
                console.log(error);
              });
          } else {
            axios({
              method: "post",
              url: "index.php?option=com_emundus_onboard&controller=campaign&task=createcampaign",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({ body: this.form })
            })
              .then(response => {
                axios
                  .get(
                    "index.php?option=com_emundus_onboard&controller=campaign&task=getcreatedcampaign"
                  )
                  .then(response => {
                    this.campaign = response.data.data.id;
                    this.quitFunnelOrContinue(this.quit);
                  })
                  .catch(e => {
                    console.log(e);
                  });
              })
              .catch(error => {
                console.log(error);
              });
          }
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=campaign&task=createyear",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({ body: this.year })
          })
            .then(response => {})
            .catch(error => {
              console.log(error);
            });
        })
        .catch(error => {
          console.log(error);
        });
    },

    quitFunnelOrContinue(quit) {
      if (quit == 0) {
        window.location.replace(
          "campaigns"
        );
      }
      else if (quit == 1) {
        window.location.replace(
          "index.php?option=com_emundus_onboard&view=form&layout=add&fid=" + this.campaign
        );
      }
    },

    changeDate(dbDate) {
      const regexDate = /\d{4}-\d{2}-\d{2}/gm;
      const regexHour = /\d{2}:\d{2}:\d{2}/gm;
      const str = dbDate;
      let m;
      var formatDate = "";

      while ((m = regexDate.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexHour.lastIndex) {
          regexHour.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((yy_MM_dd, groupIndex) => {
          formatDate = `${yy_MM_dd}T`;
        });
      }

      while ((m = regexHour.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexHour.lastIndex) {
          regexHour.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((HH_mm, groupIndex) => {
          formatDate = formatDate + `${HH_mm}.000Z`;
        });
      }
      return formatDate;
    },

    onSearchYear(value) {
      this.form.year = value;
    },
    onSearchCategory(value) {
      this.programForm.programmes = value;
    },
    onSearchProfile(value) {
      this.form.profileLabel = value;
    },

    changeEndDate() {
      if (this.form.end_date == "") {
        this.form.end_date = this.olderDate;
      } else {
        this.olderDate = this.form.end_date;
        this.form.end_date = "";
      }
    }
  },

  mounted() {
    var cid = this.campaign;
    jQuery(document).ready(function($) {
      if (window.history && window.history.pushState) {
        window.history.pushState(
          "forward",
          null,
          "index.php?option=com_emundus_onboard&view=campaign&layout=add&cid=" + cid + "#forward"
        );

        $(window).on("popstate", function() {
          window.location.replace("./campaigns");
        });
      }
    });
  }
};
</script>

<style scoped>
.container-evaluation {
  position: relative;
  width: 85%;
  margin-right: auto;
  margin-left: auto;
}

h2 {
  color: #1b1f3c !important;
}

.w-checkbox-input {
  float: left;
  margin-bottom: 0px;
  margin-left: -20px;
  margin-right: 0px;
  margin-top: 4px;
  line-height: normal;
  width: 4% !important;
}

.checkbox-label {
  color: #696969;
  font-size: 12px;
}

.w-form-label {
  display: inline-block;
  cursor: pointer;
  font-weight: normal;
  margin-bottom: 0;
  margin-top: 5.5%;
}

.w-checkbox {
  display: block;
  margin-bottom: 5px;
  padding-left: 20px;
}

.w-select,
.plus.w-inline-block {
  background-color: white;
  border-color: #cccccc;
}

.w-input,
.w-select {
  min-height: 55px;
  padding: 12px;
  font-weight: 300;
}

.bouton-sauvergarder-et-continuer {
  position: relative;
  padding: 10px 30px;
  float: right;
  border-radius: 4px;
  background-color: #1b1f3c;
  -webkit-transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
  transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
}

.last-container {
  padding-bottom: 30px;
}

.section-principale {
  padding-bottom: 0;
}

.toggle > b {
  display: block;
}

.toggle {
  position: relative;
  width: 40px;
  height: 20px;
  border-radius: 100px;
  background-color: #ddd;
  overflow: hidden;
  box-shadow: inset 0 0 2px 1px rgba(0, 0, 0, 0.05);
}

.check {
  position: absolute;
  display: block;
  cursor: pointer;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  z-index: 6;
}

.check:checked ~ .track {
  box-shadow: inset 0 0 0 20px #4bd863;
}

.check:checked ~ .switch {
  right: 2px;
  left: 22px;
  transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
  transition-property: left, right;
  transition-delay: 0.05s, 0s;
}

.switch {
  position: absolute;
  left: 2px;
  top: 2px;
  bottom: 2px;
  right: 22px;
  background-color: #fff;
  border-radius: 36px;
  z-index: 1;
  transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
  transition-property: left, right;
  transition-delay: 0s, 0.05s;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.track {
  position: absolute;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
  box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.05);
  border-radius: 40px;
}

.w-retour {
  margin-right: 5%;
  background: none !important;
  border: 1px solid #af2929;
  color: #af2929;
}

.w-quitter {
  margin-right: 5%;
  background: none !important;
  border: 1px solid #1b1f3c;
  color: #1b1f3c;
}

.program-addCampaign {
  padding: 2%;
  margin-bottom: 5%;
}
</style>
