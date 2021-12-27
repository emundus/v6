<template>
	<div id="list-head">
		<div class="list-head-container">
			<h2> {{ translations.title[data.type] }}</h2>
			<a @click="redirectToAddElement" class="bouton-ajouter pointer">
    	  <div class="add-button-div">	
					{{ translations.add[data.type] }}
				</div>
			</a>
		</div>
		<div class="loading-form" style="top: 10vh" v-if="loading">
      <Ring-Loader :color="'#12db42'" />
    </div>
	</div>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
	props: {
		data: {
			type: Object,
			required: true
		},
	},
	data: function () {
		return {
			title: 'List',
			loading: false,
			translations: {
				title: {
					campaign: Joomla.JText._("COM_EMUNDUS_ONBOARD_CAMPAIGNS"),
					email: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAILS"),
					formulaire: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORMS"),
					grilleEval: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORMS"),
					form: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORMS"),
				},
				add: {
					campaign: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN"),
					email: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_EMAIL"),
					formulaire: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_FORM"),
					grilleEval: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_FORM"),
					form: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_FORM"),
				}
			}
		}
	},
	methods: {
		redirectToAddElement: function () {
			if (this.data.add_url == 'index.php?option=com_emundus_onboard&view=form&layout=add'){
        this.getAddUrlToCreateForm();
      } else {
        this.redirect();
      }
		},
		getAddUrlToCreateForm() {
			this.loading = true;

			const body = {
				label: "Nouveau formulaire",
      	description: "",
      	published: 1
			};

			// find add_url
			axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=form&task=createform",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
						body: body
					})
        }).then(response => {
          this.loading = false;
          const profileId = response.data.data;

					this.data.add_url = 'index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=' + profileId + '&index=0&cid=';
          this.redirect();
        }).catch(error => {
          console.log(error);
					this.loading = false;
        });
		},
		redirect() {
			axios({
          method: "get",
          url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
          params: {
            link: this.data.add_url,
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
          window.location.href = window.location.pathname + response.data.data;
        });		
		},
	}
}
</script>

<style lang="scss" scoped>
#list-head {
	margin-bottom: 15px;

	.list-head-container{
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 1.5rem 0;
		border-bottom: 1px solid #e5e5e5;

		h2 {
			margin: 0;
		}
	}
}
</style>