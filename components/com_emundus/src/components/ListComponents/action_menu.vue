<template>
  <div>
    <div class="container-2 w-container" style="max-width: unset">
      <transition :name="'slide-down'" type="transition">
        <div>
            <nav aria-label="action" class="actions-dropdown">
              <a v-on:click="publishSelected(checkItem)" class="action-submenu" v-if="!['formulaire','email'].includes(data.type) && !published">
                {{ translations.ActionPublish }}
              </a>
              <a v-on:click="unpublishSelected(checkItem)" class="action-submenu" v-if="!['formulaire','email'].includes(data.type) && published">
                {{ translations.ActionUnpublish }}
              </a>
              <a v-on:click="publishSelected(checkItem)" class="action-submenu" style="border-right: 0" v-if="data.type === 'formulaire' && !published">
                {{ translations.Restore }}
              </a>
              <a v-if="data.type === 'campaign' || data.type === 'formulaire'"
                 v-on:click="duplicateSelected(checkItem)"
                 class="action-submenu w-dropdown-link" style="border-left: 0;border-right: 0">
                {{ translations.ActionDuplicate }}
              </a>
              <a v-on:click="deleteSelected(checkItem)" class="action-submenu" v-if="data.type !== 'formulaire' && data.type !== 'campaign'">
                {{ translations.ActionDelete }}
              </a>
              <a v-on:click="deleteSelected(checkItem)" class="action-submenu" v-if="data.type === 'campaign' && filesCount == 0">
                {{ translations.ActionDelete }}
              </a>
              <a v-on:click="unpublishSelected(checkItem)" class="action-submenu" style="border-left: 0"  v-if="data.type === 'formulaire' && published">
                {{ translations.Archive }}
              </a>
            </nav>
        </div>
      </transition>
    </div>
    <div class="loading-form" style="top: 10vh" v-if="loading">
      <Ring-Loader :color="'#12db42'" />
    </div>
  </div>
</template>

<script>
  import axios from "axios";
  import Swal from "sweetalert2";
  ;

  const qs = require("qs");

  export default {
    name: "action_menu",

    props: {
      data: Object,
      isEmpty: Boolean,
      selected: String,
      published: Boolean,
    },

    computed: {
      checkItem() {
        return [this.selected];
      },
    },

    data() {
      return {
        loading: false,
        filesCount: null,
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
    mounted() {
      if(this.data.type === 'campaign'){
        this.filesNumber();
      }
    },

    methods: {
      filesNumber() {
        axios({
          method: "get",
          url: "index.php?option=com_emundus&controller=dashboard&task=getfilesbycampaign",
          params: {
            cid: this.selected,
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
          this.filesCount = parseInt(response.data.data);
        });
      },

      deleteSelected(id) {
        switch (this.data.type) {
          case "program":
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_PROGDELETE"),
              text: this.translate("COM_EMUNDUS_ONBOARD_CANT_REVERT"),
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: '#de6339',
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true
            }).then(result => {
              if (result.value) {
                this.$emit("updateLoading",true);
                axios({
                  method: "post",
                  url: "index.php?option=com_emundus&controller=program&task=deleteprogram",
                  data: qs.stringify({ id })
                }).then(response => {
                  this.$emit("updateLoading",false);
                  this.$store.commit("lists/deleteSelected", id);
                  Swal.fire({
                    title: this.translate("COM_EMUNDUS_ONBOARD_PROGDELETED"),
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                }).then(() => {
                  axios.get(
                          "index.php?option=com_emundus&controller=program&task=getprogramcount"
                  ).then(response => {
                    this.total = response.data.data;
                    this.updateTotal(this.total);
                  });
                }).catch(error => {
                  console.log(error);
                });
              }
            });
            break;

          case "campaign":
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_CAMPDELETE"),
              text: this.translate("COM_EMUNDUS_ONBOARD_CANT_REVERT"),
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: '#de6339',
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true
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
                  axios.get(
                          "index.php?option=com_emundus&controller=campaign&task=getcampaigncount"
                  ).then(response => {
                    this.total = response.data.data;
                    this.updateTotal(this.total);
                    this.$emit('validateFilters');
                  });
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
              confirmButtonColor: '#de6339',
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true
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
                  axios.get("index.php?option=com_emundus&controller=email&task=getemailcount")
                          .then(response => {
                            this.total = response.data.data;
                            this.updateTotal(this.total);
                            this.$emit('validateFilters');
                          });
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
              confirmButtonColor: '#de6339',
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true
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
                  axios.get("index.php?option=com_emundus&controller=form&task=getformcount")
                          .then(response => {
                            this.total = response.data.data;
                            this.updateTotal(this.total);
                            this.$emit('validateFilters');
                          });
                }).catch(error => {
                  console.log(error);
                });
              }
            });

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
              confirmButtonColor: '#de6339',
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true
            }).then(result => {
              if (result.value) {
                this.$emit("updateLoading",true);
                axios({
                  method: "post",
                  url:
                          "index.php?option=com_emundus&controller=program&task=unpublishprogram",
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
              confirmButtonColor: '#de6339',
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true
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
              confirmButtonColor: '#de6339',
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true
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
              url: "index.php?option=com_emundus&controller=program&task=publishprogram",
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
              confirmButtonColor: '#de6339',
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true
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
              confirmButtonColor: '#de6339',
              confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
              cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
              reverseButtons: true
            }).then(result => {
              if (result.value) {
                this.$emit("updateLoading",true);
                axios({
                  method: "post",
                  url: "index.php?option=com_emundus&controller=form&task=duplicateform",
                  data: qs.stringify({id})
                }).then(response => {
                  window.location.reload();
                })
              }
            });
            break;
        }
      },
    },
  };
</script>

<style scoped>
  div nav a:hover {
    cursor: pointer;
  }
</style>
