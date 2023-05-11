<template>
  <div class="em-w-90 em-container-profile-view em-mt-24" v-if="user">
    <div class="em-mb-32" style="width: max-content" v-if="isapplicant == 1">
      <a class="em-back-button em-pointer" :title="translate('COM_EMUNDUS_USERS_BACK_TO_FILES')" href="/"><span class="material-icons em-mr-4">navigate_before</span>{{ translate('COM_EMUNDUS_USERS_BACK_TO_FILES') }}</a>
    </div>

    <div class="em-container-profile-view-pict em-flex-row em-flex-space-between em-mb-44 em-small-flex-column em-small-align-items-start">
      <ProfilePicture :user="user" @loading="updateLoading" />
    </div>

    <div class="em-container-profile-view-intro em-flex-row em-flex-space-between em-mb-24 em-small-flex-column em-small-align-items-start">
      <span class="em-font-size-14 em-applicant-text-color em-applicant-default-font">{{ translate('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_TIP') }}</span>
    </div>

    <Form :user="user" @input="updateValue" />

    <div class="em-float-right em-w-auto" style="align-items: unset">
      <button class="em-w-auto em-mt-xs-8" :class="isapplicant == 1 ? 'btn btn-primary' : 'em-primary-button'" @click="saveProfile">{{ translate('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE') }}</button>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import ProfilePicture from "../../components/Users/ProfilePicture";
import Swal from "sweetalert2";

/* SERVICES */
import user from "com_emundus/src/services/user";
import Form from "../../components/Users/Form";

export default {
  name: "Edit",
  components: {Form, ProfilePicture},
  data: () => ({
    user: null,
    loading: false
  }),
  created() {
    this.getCurrentUser();
  },
  methods: {
    getCurrentUser() {
      this.loading = true;
      user.getUserById().then((response) => {
        this.user = response.user[0];
        this.loading = false;
      })
    },
    saveProfile() {
      let form = document.querySelector("[name^='form_']");
      if(!form.checkValidity()){
        form.reportValidity();
        return;
      }

      this.loading = true;
      user.saveUser(this.user).then((response) => {
        this.loading = false;
        if(response.data.status === true){
          Swal.fire({
            title: this.translate('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE_SUCCESS'),
            text: this.translate('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE_SUCCESS_TEXT'),
            type: "success",
            showCancelButton: false,
            showConfirmButton: false,
            customClass: {
              title: 'em-swal-title',
            },
            timer: 2000
          });
        } else {
          Swal.fire({
            title: this.translate('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE_FAILED'),
            text: this.translate('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE_FAILED_TEXT'),
            type: "error",
            showCancelButton: false,
            showConfirmButton: false,
            customClass: {
              title: 'em-swal-title',
            },
            timer: 3000
          });
        }
      });
    },
    updateValue(element) {
      this.user[element.name] = element.value;
    },
    updateLoading(value){
      this.loading = value;
    }
  },
  computed: {
    isapplicant() {
      return this.$store.getters['global/datas'].isapplicant.value;
    }
  }
}
</script>

<style scoped>

.em-container-profile-view {
  width: 84% !important;
  margin-left: auto;
  margin-right: auto;
}

.em-back-button.em-pointer {
  padding: 0;
}

</style>
