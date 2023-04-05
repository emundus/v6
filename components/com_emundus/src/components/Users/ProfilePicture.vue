<template>
  <div class="em-flex-row em-small-flex-column em-small-align-items-start">
    <div class="em-profile-picture-big em-pointer"
         :key="dynamicPP"
         @click="openBrowser()"
         :style="background_pp"
         @mouseover="displayEdit = true"
         @mouseleave="displayEdit = false">
      <span class="em-flex-row" v-show="displayEdit">
        <span class="material-icons-outlined em-mr-8">edit</span>
        {{ translate('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE') }}
      </span>
    </div>
    <div class="em-ml-32 em-m-xs-0 em-flex-column em-flex-col-start em-mt-xs-8">
      <h3 class="em-h3">{{ user.lastname.toUpperCase() }} {{ user.firstname.charAt(0).toUpperCase() + user.firstname.slice(1) }}</h3>
    </div>
  </div>
</template>

<script>

/** IMPORT YOUR SERVICES **/
import user from "com_emundus/src/services/user";

export default {
  name: "ProfilePicture",
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    dynamicPP: 0,
    displayEdit: false,
    profile_picture : null,
    background_pp: 'background-image:url(' + window.location.origin + '/media/com_emundus/images/profile/default-profile.jpg)',
  }),
  created() {
    this.profile_picture = window.location.origin + '/' + this.$props.user.profile_picture;
    if(this.$props.user.profile_picture != null){
      this.background_pp = 'background-image:url(' + window.location.origin + '/' + this.$props.user.profile_picture + ')';
    }
  },
  methods: {
    openBrowser() {
      let input = document.createElement('input');
      let mimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpeg'];
      input.type = 'file';
      input.accept=".png, .jpg, .jpeg, .gif";
      input.onchange = _this => {
        let files =   Array.from(input.files);
        let error = false;
        let title = '';
        let text = '';
        if(files[0].size > 2097152) {
          title = "COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TITLE";
          text = "COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TEXT";
          error = true;
        }
        if (!mimeTypes.includes(files[0].type)){
          title = "COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TITLE";
          text = "COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_WRONG_TYPE_TEXT";
          error = true;
        }

        if(error) {
          Swal.fire({
            title: this.translate(title),
            text: this.translate(text),
            type: "error",
            confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
            timer: 4000,
            customClass: {
              title: 'em-swal-title',
              confirmButton: 'em-swal-confirm-button',
              actions: "em-swal-single-action",
            },
          });
        } else {
          this.updateProfilePicture(files[0]);
        }
      };
      input.click();
    },

    updateProfilePicture(file){
      this.$emit('loading',true)
      user.updateProfilePicture(file).then(response => {
				if (response.data.status) {
					const date = new Date();
					const newProfileUrl =  window.location.origin + '/' + response.data.profile_picture + '?' + date.getTime();
					this.profile_picture = newProfileUrl;
					this.background_pp = 'background-image:url(' + newProfileUrl + ')';
					document.getElementById('userDropdownLabel').style.backgroundImage = 'url(' + newProfileUrl + ')';
				} else {
					Swal.fire({
						title: this.translate('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TITLE'),
						text: this.translate('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_UPDATE_TEXT'),
						type: "error",
						confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
						timer: 4000,
						customClass: {
							title: 'em-swal-title',
							confirmButton: 'em-swal-confirm-button',
							actions: "em-swal-single-action",
						},
					});
				}

        this.$emit('loading',false);
      });
    }
  },

  watch: {
    profile_picture: function(value){
      this.dynamicPP++;
    }
  },
}
</script>

<style>
 .em-h3 {
   font-weight: 600 !important;
 }
</style>
