<template>
  <div>
    <div class="container-2 w-container" style="max-width: unset">
      <transition :name="'slide-down'" type="transition">
        <div>
            <nav aria-label="action" class="em-flex-col-start">
              <a @click="publishSelected(checkItem)" class="action-submenu" v-if="!['formulaire','email', 'formModels'].includes(data.type) && !published">
                {{ translations.ActionPublish }}
              </a>
              <a @click="unpublishSelected(checkItem)" class="action-submenu" v-if="!['formulaire','email', 'formModels'].includes(data.type) && published">
                {{ translations.ActionUnpublish }}
              </a>
              <a @click="publishSelected(checkItem)" class="action-submenu" style="border-right: 0" v-if="data.type === 'formulaire' && !published">
                {{ translations.Restore }}
              </a>
              <a v-if="data.type === 'campaign' || data.type === 'formulaire'"
                 @click="duplicateSelected(checkItem)"
                 class="action-submenu w-dropdown-link" style="border-left: 0;border-right: 0">
                {{ translations.ActionDuplicate }}
              </a>
              <a v-else @click="deleteSelected(checkItem)" class="action-submenu">
                {{ translations.ActionDelete }}
              </a>
              <a v-if="data.type === 'campaign' && nb_files === 0" class="action-submenu" @click="deleteSelected(checkItem)">
                {{ translations.ActionDelete }}
              </a>
            </nav>
        </div>
      </transition>
    </div>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';
import axios from "axios";
import Swal from "sweetalert2";

const qs = require("qs");

export default {
    name: "action_menu",

    props: {
      data: Object,
      isEmpty: Boolean,
      selected: String,
      published: Boolean,
      nb_files: Number,
    },

    computed: {
      checkItem() {
        return [this.selected];
      },
    },

    data() {
      return {
        loading: false,
        translations: {
          ActionPublish: this.translate("COM_EMUNDUS_ONBOARD_ACTION_PUBLISH"),
          ActionUnpublish: this.translate("COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH"),
          ActionDuplicate: this.translate("COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE"),
          ActionDelete: this.translate("COM_EMUNDUS_ONBOARD_ACTION_DELETE"),
          Archive: this.translate("COM_EMUNDUS_ONBOARD_ARCHIVE"),
          Restore: this.translate("COM_EMUNDUS_ONBOARD_RESTORE"),
        },
      };
    },

    methods: {
      deleteSelected(id) {
        switch (this.data.type) {
          case "campaign":
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_CAMPDELETE"),
              text: this.translate("COM_EMUNDUS_ONBOARD_CANT_REVERT"),
              type: "warning",
              showCancelButton: true,
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true,
              customClass: {
                title: 'em-swal-title',
                cancelButton: 'em-swal-cancel-button',
                confirmButton: 'em-swal-confirm-button',
              },
            }).then(result => {
              if (result.value) {
                this.$emit("updateLoading",true);
                axios({
                  method: "post",
                  url: "index.php?option=com_emundus&controller=campaign&task=deletecampaign",
                  data: qs.stringify({ id })
                }).then(response => {
                  this.$emit("updateLoading",false);
                  this.$store.dispatch("lists/deleteSelected", id).then(() => {
                    this.$store.commit("lists/resetSelectedItemsList");
                  });
                  Swal.fire({
                    title: this.translate("COM_EMUNDUS_ONBOARD_CAMPDELETED"),
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                }).then(() => {
                    this.$emit('validateFilters');
                }).catch(error => {
                  console.log(error);
                });
              }
            });
            break;

          case "email":
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_EMAILDELETE"),
              text: this.translate("COM_EMUNDUS_ONBOARD_CANT_REVERT"),
              type: "warning",
              showCancelButton: true,
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true,
              customClass: {
                title: 'em-swal-title',
                cancelButton: 'em-swal-cancel-button',
                confirmButton: 'em-swal-confirm-button',
              },
            }).then(result => {
              if (result.value) {
                this.$emit("updateLoading",true);
                axios({
                  method: "post",
                  url: "index.php?option=com_emundus&controller=email&task=deleteemail",
                  data: qs.stringify({ id })
                }).then(response => {
                  this.$emit("updateLoading",false);
                  this.$store.dispatch("lists/deleteSelected", id).then(() => {
                    this.$store.commit("lists/resetSelectedItemsList");
                  });
                  Swal.fire({
                    title: this.translate("COM_EMUNDUS_ONBOARD_EMAILDELETED"),
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                }).then(() => {
                  this.$emit('validateFilters');
                }).catch(error => {
                  console.log(error);
                });
              }
            });
            break;

          case "formulaire":
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_FORMDELETE"),
              text: this.translate("COM_EMUNDUS_ONBOARD_CANT_REVERT"),
              type: "warning",
              showCancelButton: true,
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true,
              customClass: {
                title: 'em-swal-title',
                cancelButton: 'em-swal-cancel-button',
                confirmButton: 'em-swal-confirm-button',
              },
            }).then(result => {
              if (result.value) {
                this.$emit("updateLoading",true);
                axios({
                  method: "post",
                  url: "index.php?option=com_emundus&controller=form&task=deleteform",
                  data: qs.stringify({ id })
                }).then(response => {
                  this.$emit("updateLoading",false);
                  this.$store.dispatch("lists/deleteSelected", id).then(() => {
                    this.$store.commit("lists/resetSelectedItemsList");
                  });
                  Swal.fire({
                    title: this.translate("COM_EMUNDUS_ONBOARD_FORMDELETED"),
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                }).then(() => {
                  this.$emit('validateFilters');
                }).catch(error => {
                  console.log(error);
                });
              }
            });
            break;

	        case 'formModels':
		        Swal.fire({
			        title: this.translate('COM_EMUNDUS_FORM_BUILDER_DELETE_MODEL'),
			        text: this.translate('COM_EMUNDUS_ONBOARD_CANT_REVERT'),
			        type: 'warning',
			        showCancelButton: true,
			        confirmButtonText: this.translate('COM_EMUNDUS_ONBOARD_OK'),
			        cancelButtonText: this.translate('COM_EMUNDUS_ONBOARD_CANCEL'),
			        reverseButtons: true,
			        customClass: {
				        title: 'em-swal-title',
				        cancelButton: 'em-swal-cancel-button',
				        confirmButton: 'em-swal-confirm-button',
			        },
		        }).then(result => {
			        if (result.value) {
								formBuilderService.deleteFormModelFromId(id).then(response => {
									if (!response.status) {
										Swal.fire({
											title: this.translate('COM_EMUNDUS_FORM_DELETE_MODEL_FAILURE'),
											type: 'warning',
											showConfirmButton: false,
											timer: 2000
										});
									} else {
										Swal.fire({
											title: this.translate('COM_EMUNDUS_FORM_DELETE_MODEL_SUCCESS'),
											type: 'success',
											showConfirmButton: false,
											timer: 2000
										});
										this.$emit('validateFilters');
									}
								});
			        }
		        });

						break;
	        default:
						console.warn('Unhandled case ' + type + ' for delete action');
						break;
        }
      },

      unpublishSelected(id) {
        switch (this.data.type) {
          case "program":
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_PROGUNPUBLISH"),
              type: "warning",
              showCancelButton: true,
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true,
              customClass: {
                title: 'em-swal-title',
                cancelButton: 'em-swal-cancel-button',
                confirmButton: 'em-swal-confirm-button',
              },
            }).then(result => {
              if (result.value) {
                this.$emit("updateLoading",true);
                axios({
                  method: "post",
                  url:
                          "index.php?option=com_emundus&controller=programme&task=unpublishprogram",
                  data: qs.stringify({ id })
                }).then(response => {
                  this.$emit("updateLoading",false);
                  this.$store.commit("lists/unpublish", id);
                  Swal.fire({
                    title: this.translate("COM_EMUNDUS_ONBOARD_PROGUNPUBLISHED"),
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                  this.$emit('validateFilters');
                }).catch(error => {
                  console.log(error);
                });
              }
            });
            break;

          case "campaign":
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGNUNPUBLISH"),
              type: "warning",
              showCancelButton: true,
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true,
              customClass: {
                title: 'em-swal-title',
                cancelButton: 'em-swal-cancel-button',
                confirmButton: 'em-swal-confirm-button',
              },
            }).then(result => {
              if (result.value) {
                this.$emit("updateLoading",true);
                axios({
                  method: "post",
                  url: "index.php?option=com_emundus&controller=campaign&task=unpublishcampaign",
                  data: qs.stringify({id})
                }).then(response => {
                  this.$emit("updateLoading",false);
                  this.$store.commit("lists/unpublish", id);
                  Swal.fire({
                    title: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGNUNPUBLISHED"),
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                  this.$emit('validateFilters');
                }).catch(error => {
                  console.log(error);
                });
              }
            });
            break;

          case "email":
            axios({
              method: "post",
              url: "index.php?option=com_emundus&controller=email&task=unpublishemail",
              data: qs.stringify({ id })
            }).then(response => {
              this.$store.commit("lists/unpublish", id);
              Swal.fire({
                title: this.translate("COM_EMUNDUS_ONBOARD_EMAILUNPUBLISHED"),
                type: "success",
                showConfirmButton: false,
                timer: 2000
              });
              this.$emit('validateFilters');
            }).catch(error => {
              console.log(error);
            });
            break;

          case "formulaire":
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_FORMUNPUBLISH"),
              type: "warning",
              showCancelButton: true,
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true,
              customClass: {
                title: 'em-swal-title',
                cancelButton: 'em-swal-cancel-button',
                confirmButton: 'em-swal-confirm-button',
              },
            }).then(result => {
              if (result.value) {
                this.$emit("updateLoading",true);
                axios({
                  method: "post",
                  url: "index.php?option=com_emundus&controller=form&task=unpublishform",
                  data: qs.stringify({id})
                }).then(response => {
                  this.$emit("updateLoading",false);
                  this.$store.commit("lists/unpublish", id);
                  Swal.fire({
                    title: this.translate("COM_EMUNDUS_ONBOARD_FORMUNPUBLISHED"),
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                  this.$emit('validateFilters');
                }).catch(error => {
                  console.log(error);
                });
              }
            });
            break;
        }
      },

      publishSelected(id) {
        switch (this.data.type) {
          case "program":
            axios({
              method: "post",
              url: "index.php?option=com_emundus&controller=programme&task=publishprogram",
              data: qs.stringify({ id })
            }).then(response => {
              this.$store.commit("lists/publish", id);
              Swal.fire({
                title: this.translate("COM_EMUNDUS_ONBOARD_PROGPUBLISHED"),
                type: "success",
                showConfirmButton: false,
                timer: 2000
              });
              this.$emit('validateFilters');
            }).catch(error => {
              console.log(error);
            });
            break;

          case "campaign":
            axios({
              method: "post",
              url: "index.php?option=com_emundus&controller=campaign&task=publishcampaign",
              data: qs.stringify({ id })
            }).then(response => {
              this.$store.commit("lists/publish", id);
              Swal.fire({
                title: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGNPUBLISHED"),
                type: "success",
                showConfirmButton: false,
                timer: 2000
              });
              this.$emit('validateFilters');
            }).catch(error => {
              console.log(error);
            });
            break;

          case "email":
            axios({
              method: "post",
              url: "index.php?option=com_emundus&controller=email&task=publishemail",
              data: qs.stringify({ id })
            }).then(response => {
              this.$store.commit("lists/publish", id);
              Swal.fire({
                title: this.translate("COM_EMUNDUS_ONBOARD_EMAILPUBLISHED"),
                type: "success",
                showConfirmButton: false,
                timer: 2000
              });
              this.$emit('validateFilters');
            }).catch(error => {
              console.log(error);
            });
            break;

          case "formulaire":
            axios({
              method: "post",
              url: "index.php?option=com_emundus&controller=form&task=publishform",
              data: qs.stringify({ id })
            }).then(response => {
              this.$store.commit("lists/publish", id);
              Swal.fire({
                title: this.translate("COM_EMUNDUS_ONBOARD_FORMPUBLISHED"),
                type: "success",
                showConfirmButton: false,
                timer: 2000
              });
              this.$emit('validateFilters');
            }).catch(error => {
              console.log(error);
            });
            break;
        }
      },

      duplicateSelected(id) {
        switch (this.data.type) {
          case "campaign":
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGNDUPLICATE"),
              type: "warning",
              showCancelButton: true,
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true,
              customClass: {
                title: 'em-swal-title',
                cancelButton: 'em-swal-cancel-button',
                confirmButton: 'em-swal-confirm-button',
              },
            }).then(result => {
              if (result.value) {
                this.$emit("updateLoading",true);
                axios({
                  method: "post",
                  url: "index.php?option=com_emundus&controller=campaign&task=duplicatecampaign",
                  data: qs.stringify({id})
                }).then(response => {
                  window.location.reload();
                });
              }
            });
            break;

          case "formulaire":
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_FORMDUPLICATE"),
              type: "warning",
              showCancelButton: true,
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true,
              customClass: {
                title: 'em-swal-title',
                cancelButton: 'em-swal-cancel-button',
                confirmButton: 'em-swal-confirm-button',
              },
            }).then(result => {
              if (result.value) {
                this.$emit('updateLoading',true);
                axios({
                  method: 'post',
                  url: 'index.php?option=com_emundus&controller=form&task=duplicateform',
                  data: qs.stringify({id})
                }).then(response => {
									if (response.data.status) {
										window.location.reload();
									} else {
										this.$emit('updateLoading', false);

										Swal.fire({
											title: this.translate('COM_EMUNDUS_ONBOARD_FORMDUPLICATE_FAILED'),
											type: 'error',
											text: response.data.msg,
											confirmButtonText: this.translate('COM_EMUNDUS_ONBOARD_OK'),
											reverseButtons: true,
											customClass: {
												title: 'em-swal-title',
												confirmButton: 'em-swal-confirm-button',
												actions: "em-swal-single-action",
											}
										});
									}
                });
              }
            });
            break;
        }
      },
    },
};
</script>

<style lang="scss" scoped>
  div nav a:hover {
    cursor: pointer;
  }

  .action-submenu {
    padding: 7px !important;
    background-color: transparent;
    -webkit-transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
    transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
    font-size: 12px;
    color: black;
    font-family: Lato, 'Helvetica Neue', Arial, Helvetica, sans-serif !important;
    &:hover {
     color: var(--main-500);
   }
  }
</style>
