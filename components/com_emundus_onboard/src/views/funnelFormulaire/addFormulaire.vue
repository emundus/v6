<template>
  <div class="container-evaluation">
    <h2 class="heading">{{ funnelCategorie }}</h2>
    <p class="paragraphe-sous-titre">
      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
      elementum tristique.
    </p>
    <div class="w-form">
      <form id="email-form" name="email-form" data-name="Email Form">
        <div class="container-flexbox-choisir-ou-plus w-clearfix">
          <select id="Formulaire" name="Formulaire" data-name="Formulaire" class="dropdown-toggle">
            <option value>{{ ChooseForm }}</option>
            <option value="Formulaire 1">Formulaire 1</option>
            <option value="Formulairee 2">Formulaire 2</option>
            <option value="Formulaire 3">Formulaire 3</option>
            <option value="Preset-1">Preset-1</option>
            <option value="Preset-2">Preset-2</option>
          </select>

          <button href="/formulaire" class="plus w-inline-block">+</button>
          <button @click.prevent="formbuilder()" class="plus w-inline-block">
            <em class="fas fa-pencil-alt"></em>
          </button>
        </div>
      </form>
    </div>
    <div class="icon-title espace-candidature"></div>
    <FormCarrousel :formList="this.formList" v-if="this.formList" @getEmitIndex="getEmitIndex" />
  </div>
</template>

<script>
import FormCarrousel from "../../components/Form/FormCarrousel";
import axios from "axios";
const qs = require("qs");

import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";

export default {
  name: "addFormulaire",

  props: {
    funnelCategorie: String,
    profileId: String
  },
  components: {
    FormCarrousel
  },

  data() {
    return {
      ChooseForm: Joomla.JText._("COM_EMUNDUSONBOARD_CHOOSE_FORM"),
      EmitIndex: "0",
      formList: ""
    };
  },
  methods: {
    getEmitIndex(value) {
      this.EmitIndex = value;
    },
    formbuilder() {
      window.location.replace(
        "index.php?option=com_emundus_onboard&view=form&layout=formBuilder&prid=" +
          this.profileId +
          "&index=" +
          this.EmitIndex
      );
    },
    getForms(profile_id) {
      axios({
        method: "get",
        url:
          "index.php?option=com_emundus_onboard&controller=form&task=getFormsByProfileId",
        params: {
          profile_id: profile_id
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      })
        .then(response => {
          this.formList = response.data.data;
        })
        .catch(e => {
          console.log(e);
        });
    }
  },
  created() {
    this.getForms(this.profileId);
  },
  watch: {
    profileId: function() {
      this.getForms(this.profileId);
    }
  }
};
</script>
