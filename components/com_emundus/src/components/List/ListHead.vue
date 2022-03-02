<template>
	<div id="list-head">
		<div class="list-head-container">
			<h2> {{ translations['title_' + data.type] }}</h2>
			<a :href="data.add_url" v-if="data.type !== 'form'">
    	  <div class="em-primary-button">
					{{ translations['add_' + data.type] }}
				</div>
			</a>
      <a @click="getAddUrlToCreateForm()" v-else>
        <div class="em-primary-button">
          {{ translations['add_' + data.type] }}
        </div>
      </a>
		</div>
    <div class="em-page-loader" v-if="loading"></div>
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
				title_campaign: "COM_EMUNDUS_ONBOARD_CAMPAIGNS",
				title_email: "COM_EMUNDUS_ONBOARD_EMAILS",
				title_formulaire: "COM_EMUNDUS_ONBOARD_FORMS",
				title_grilleEval: "COM_EMUNDUS_ONBOARD_FORMS",
				title_form: "COM_EMUNDUS_ONBOARD_FORMS",
				add_campaign: "COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN",
				add_email: "COM_EMUNDUS_ONBOARD_ADD_EMAIL",
				add_formulaire: "COM_EMUNDUS_ONBOARD_ADD_FORM",
				add_grilleEval: "COM_EMUNDUS_ONBOARD_ADD_FORM",
				add_form: "COM_EMUNDUS_ONBOARD_ADD_FORM"
			}
		}
	},
	methods: {
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
          url: "index.php?option=com_emundus&controller=form&task=createform",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
						body: body
					})
        }).then(response => {
          this.loading = false;
          const profileId = response.data.data;

          window.location.href = 'index.php?option=com_emundus&view=form&layout=formbuilder&prid=' + profileId + '&index=0&cid=';
        }).catch(error => {
          console.log(error);
					this.loading = false;
        });
		},
	}
}
</script>

<style lang="scss" scoped>
#list-head {
	margin-bottom: 15px;

	.em-primary-button {
		border: 1px solid #20835F;
		cursor: pointer;
		transition: all 0.3s ease;

		&:hover {
			background-color: white;
			color: #20835F;
		}
	}

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
