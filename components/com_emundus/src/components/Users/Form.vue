<template>
  <div>
    <form class="em-form em-container-profile-view-form em-grid-2 em-small-flex-column" :name="'form_' + formId" :id="'form_' + formId" v-if="!loading">
      <Section v-for="group in groups" :group="group" :user="user" @input="updateValue" />
      <Attachments />
      <div class="fabrikGroup" v-if="user.login_type == 'internal'">
        <legend>{{ translate('COM_EMUNDUS_USERS_EDIT_PROFILE_PASSWORD_TITLE') }}</legend>
        <a class="em-mt-8 em-w-auto btn btn-danger" href="/index.php?option=com_users&view=profile&layout=edit">{{ translate('COM_EMUNDUS_USERS_EDIT_PROFILE_PASSWORD') }}</a>
      </div>
    </form>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */

/* IMPORT YOUR SERVICES */
import user from "com_emundus/src/services/user";
import Section from "./Section";
import Attachments from "./Attachments";

export default {
  name: "Form",
  components: {Attachments, Section},
  props: {
    user: Object
  },
  data: () => ({
    formId: null,
    groups: [],

    noFormFound: false,
    loading: false,
  }),
  created() {
    this.loading = true;
    user.getProfileForm().then((response) => {
      this.formId = parseInt(response.form);
      if(this.formId === 0){
        // TODO : SI aucun formulaire n'est trouvé il faut afficher les informations du profil en lecture seule par défaut
        this.noFormFound = true;
        this.loading = false;
      } else {
        user.getProfileGroups(this.formId).then((response_2) => {
          this.groups = response_2.groups;
          this.loading = false;
        })
      }
    });
  },
  methods: {
    updateValue(element) {
      this.$emit('input', element)
    }
  }
}
</script>

<style scoped>

.fabrikGroup {
  margin-bottom: 0 !important;
  margin-top: 0 !important;
}

.em-form {
  margin-top: 0 !important;
}

legend {
  font-weight: 600;
}

</style>
