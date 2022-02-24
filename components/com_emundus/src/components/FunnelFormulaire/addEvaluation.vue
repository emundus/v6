<template>
  <div>
    <div class="container-evaluation">
      <h2 class="heading">{{ funnelCategorie[0] }}</h2>
      <p class="paragraphe-sous-titre">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
        elementum tristique.
      </p>
      <div class="w-row">
        <div class="w-col w-col-6">
          <div class="w-form">
            <form id="email-form" name="email-form" data-name="Email Form" class="form-2">
              <datetime
                :placeholder="StartDate"
                type="datetime"
                id="start_date"
                v-model="form.start_date"
                :language="fr"
              ></datetime>
            </form>
          </div>
        </div>
        <div class="w-col w-col-6">
          <div class="w-form">
            <form id="email-form" name="email-form" data-name="Email Form">
              <datetime
                :placeholder="EndDate"
                type="datetime"
                id="end_date"
                v-model="form.end_date"
                :language="fr"
              ></datetime>
            </form>
          </div>
        </div>
      </div>
      <div class="icon-title evaluation"></div>
    </div>
    <div class="divider"></div>
    <div class="container-evaluation">
      <h2 class="heading">{{ funnelCategorie[1] }}</h2>
      <p class="paragraphe-sous-titre">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
        elementum tristique.
      </p>
      <div class="w-form">
        <form id="email-form" name="email-form" data-name="Email Form">
          <div class="container-flexbox-choisir-ou-plus w-clearfix">
            <select
              id="Grille-d-valuation"
              name="Grille-d-valuation"
              data-name="Grille D Valuation"
              class="dropdown-toggle"
              ><option value="">{{ ChooseEvalutationGrid }}</option
              ><option value="Grille d'évaluation 1">Grille d'évaluation 1</option
              ><option value="Grille d'évaluation 2">Grille d'évaluation 2</option
              ><option value="Grille d'évaluation 3">Grille d'évaluation 3</option></select
            ><button class="plus w-inline-block">+</button>
          </div>
        </form>
      </div>
      <div class="icon-title evaluation"></div>
    </div>
  </div>
</template>

<script>
import { Datetime } from "vue-datetime";

export default {
  name: "addEvaluation",

  components: {
    Datetime
  },

  props: {
    funnelCategorie: Object,
    form: Object
  },

  data() {
    return {
      ChooseEvalutationGrid: this.translate("COM_EMUNDUS_ONBOARD_CHOOSE_EVALUATION_GRID"),
      StartDate: this.translate("COM_EMUNDUS_ONBOARD_ADDCAMP_STARTDATE"),
      EndDate: this.translate("COM_EMUNDUS_ONBOARD_ADDCAMP_ENDDATE")
    };
  },

  methods: {},

  created() {}
};
</script>
