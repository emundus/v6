<template>
	<div class="list-bloc-item">
		<div class="title em-mb-16">
			<h3>{{ title }}</h3>
			<div class="dates" v-if="data.start_date && data.end_date">
				{{ translations.from }} <span>{{ formatDate(data.start_date) }}</span> {{ translations.to }} <span>{{ formatDate(data.end_date) }}</span>
			</div>
		</div>

		<div class="informations">
			<div v-if="data.short_description" class="description" v-html="data.short_description"></div>

			<div v-if="campaign.associatedCampaigns !== null && campaign.associatedCampaigns.length === 1"
				class="associated-campaigns em-w-100 em-flex-row"
				:title="translations.associatedCampaigns">
				<div
					v-for="campaign in campaign.associatedCampaigns"
					:key="campaign.id"
					class="tag campaign-item em-p-4-8"
				>
					{{ campaign.label }}
				</div>
			</div>
      <div v-else-if="campaign.associatedCampaigns !== null && campaign.associatedCampaigns.length > 1" @click="displayAssociatedCampaigns">
        <span class="em-pointer em-hover-underline">{{ campaign.associatedCampaigns.length }} {{ translate('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED') }}</span>
      </div>
      <div v-else-if="campaign.associatedCampaigns !== null && campaign.associatedCampaigns.length === 0">{{ translate('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED_NOT') }}</div>

			<div class="tags em-flex-row em-flex-start">
				<div v-if="displayPublished && isPublished !== null"
					:class="{
						published: isPublished,
						unpublished: !isPublished
					}"
        >
          {{ isPublished ? translations.publishedTag : translations.unpublishedTag }}
				</div>

				<div v-if="displayPublished && isActive !== null"
					:class="{
						published: isActive,
						unpublished: !isActive
					}"
				>
					{{ isActive ? translations.active : translations.inactive }}
				</div>

				<div v-if="type == 'campaign'" :class="campaignStateClass">
					{{ campaignState }}
				</div>

				<div v-if="data.nb_files">
					<div>
						{{ data.nb_files }}
						<span v-if="data.nb_files > 1">{{ translations.files }}</span>
						<span v-else>{{ translations.file }}</span>
					</div>
				</div>

				<div v-if="type == 'email' && data.category && data.category.length > 0">
					<div>
						{{ data.category }}
					</div>
				</div>
			</div>
		</div>

		<hr class="em-w-100">

		<div class="actions em-flex-row em-w-100"
		  :class="{
				'em-flex-row-justify-end': getEditUrlByType() === '',
	      'em-flex-space-between': getEditUrlByType() !== ''
			}"
		>
			<a v-if="getEditUrlByType() !== ''" @click="redirectToEditItem" class="em-primary-button em-font-size-14 em-pointer">
					{{ translations.edit }}
			</a>
			<list-action-menu
				:type="type"
				:itemId="data.id"
				:isPublished="actionMenuIsPublished"
				:showTootlip="hasActionMenu"
				:nb_files="type === 'campaign' ? parseInt(data.nb_files) : 0"
				:pinned="type === 'campaign' ? parseInt(data.pinned) : 0"
				@validateFilters="validateFilters"
				@updateLoading="updateLoading"
				@showModalPreview="showModalPreview"
			></list-action-menu>
		</div>
	</div>
</template>

<script>
import moment from "moment";
import axios from "axios";
import rows from '../../../../data/tableRows';
import ListActionMenu from '../ListActionMenu.vue';
import Swal from "sweetalert2";

const qs = require("qs");

export default {
	components: { ListActionMenu },
	props: {
		data: {
			type: Object,
			required: true
		},
		type: {
			type: String,
			required: true
		},
		actions: {
			type: Object,
			required: true
		},
	},
	data() {
		return {
			title: "",
			lang: 'fr',
			translations: {
				publishedTag: "COM_EMUNDUS_ONBOARD_FILTER_PUBLISH",
				unpublishedTag: "COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH",
				active: "COM_EMUNDUS_ONBOARD_FILTER_PUBLISH_FORM",
				inactive: "COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH_FORM",
				isFinished: "COM_EMUNDUS_ONBOARD_FILTER_CLOSE",
				edit: "COM_EMUNDUS_ONBOARD_MODIFY",
				files: "COM_EMUNDUS_ONBOARD_FILES",
      	file: "COM_EMUNDUS_ONBOARD_FILE",
				campaignAssociated: "COM_EMUNDUS_ONBOARD_CAMPAIGN_ASSOCIATED",
        campaignsAssociated: "COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED",
				from: "COM_EMUNDUS_ONBOARD_FROM",
				to: "COM_EMUNDUS_ONBOARD_TO",
			},
			campaign: {
				associatedCampaigns: null
			}
		};
	},
	mounted() {
		this.lang = this.$store.getters['global/shortLang'];
		this.getTitle();

		if (this.type === "formulaire" || this.type === "form" || this.type === "grilleEval") {
			this.getAssociatedCampaigns();
		}
	},
	methods: {
		getTitle() {
			if (this.data.label && typeof this.data.label === "string") {
				this.title = this.data.label;
			} else if (this.data.label && this.data.label[this.lang]) {
				this.title = this.data.label[this.lang];
			} else if (this.data.subject) {
				this.title = this.data.subject;
			}
		},
		redirectToEditItem() {
      window.location.href = this.getEditUrlByType();
		},
		getEditUrlByType() {
			let url = '';

			switch(this.type) {
				case 'campaign':
					url = 'index.php?option=com_emundus&view=campaigns&layout=addnextcampaign&cid=' + this.data.id + '&index=0';
				break;
				case 'form':
				case 'formulaire':
					url = 'index.php?option=com_emundus&view=form&layout=formbuilder&prid=' + this.data.id + '&index=0&cid=';
				break;
				case 'grilleEval':
					url =  "index.php?option=com_emundus&view=form&layout=formbuilder&prid=&index=0&cid=" + "" + "&evaluation=" + this.data.id
				break;
				case 'email':
					url = 'index.php?option=com_emundus&view=emails&layout=add&eid=' + this.data.id;
				break;
			}

			return url;
		},

		// Specific methods for campaigns
		getAssociatedCampaigns() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=form&task=getassociatedcampaign",
        params: {
          pid: this.data.id,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.campaign.associatedCampaigns = response.data.data;
      });
		},

		validateFilters() {
      this.$emit('validateFilters');
    },
		updateLoading(value) {
      this.$emit('updateLoading',value);
    },
		showModalPreview() {
			this.$emit('showModalPreview');
		},
		formatDate(date) {
			return moment(date).format('DD/MM/YYYY');
		},
    displayAssociatedCampaigns() {
      let campaigns = '<div class="em-flex-col-start" style="text-align: left">';
      this.campaign.associatedCampaigns.forEach((campaign) => {
        campaigns += '<p>'+campaign.label+'</p><hr style="width: 100%; margin: 4px 0">';
      })
      campaigns += '</div>';
      Swal.fire({
        title: this.translate('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED_TITLE'),
        html: campaigns,
        type: "info",
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        customClass: {
          title: 'em-swal-title',
          confirmButton: 'em-swal-confirm-button',
          actions: "em-swal-single-action",
        },
      });
    }
	},
	computed: {
		isPublished() {
			if (this.type == "campaign" || this.type == "email") {
				return this.data.published == 1;
			}

			return null;
    },
		isActive() {
			if (this.type == "form" || this.type == "formulaire" || this.type == "grilleEval") {
				return this.data.status == 1;
			}

			return null;
		},
		isFinished() {
			if (this.type == "campaign") {
				return moment(this.data.end_date) < moment();
			}

			return false;
		},
    campaignState() {
      if (this.type == "campaign") {
        let message = "";

        if (moment(this.data.end_date) < moment()) {
          message = this.translate("COM_EMUNDUS_ONBOARD_FILTER_CLOSE");
        } else if (moment(this.data.start_date) > moment()) {
          message = this.translate("COM_EMUNDUS_CAMPAIGN_YET_TO_COME");
        } else {
          message = this.translate("COM_EMUNDUS_CAMPAIGN_ONGOING");
        }

        return message;
      }

      return false;
    },
    campaignStateClass() {
      if (this.type == "campaign") {
        let classe = "";

        if (moment(this.data.end_date) < moment()) {
          classe = "finished";
        }

        return classe;
      }

      return false;
    },
		hasActionMenu() {
			let hasActionMenu = true;

			if (this.type == "email") {
				if (this.data.lbl.startsWith('custom_') === false && this.data.lbl.startsWith('email_') === false) {
					hasActionMenu = false;
				}
			} else if (this.type == "grilleEval") {
				hasActionMenu = false;
			}

			return hasActionMenu;
		},
		actionMenuIsPublished() {
			if (this.type == "email" || this.type == "campaign") {
				return this.isPublished;
			} else if (this.type == "form" || this.type == "formulaire" || this.type == "grilleEval") {
				return this.isActive;
			}

			return this.data.published == 1;
		},
    displayPublished() {
      const display = rows[this.type].filter(row => (row.value == 'published' || row.value == 'status'))
      return display.length;
    }
	}
}
</script>

<style lang="scss" scoped>
.em-hover-underline{
  &:hover{
    text-decoration: underline;
  }
}
.not-displayed {
	.list-bloc-item {
		display: none;
	}
}
.list-bloc-item {
  min-height: 199px;
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	justify-content: space-between;
	padding: 16px;
	background: #FFFFFF;
  //box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07), 0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);
  border: solid 1px #e0e0e5;
	box-sizing: border-box;
	border-radius: 4px;

	hr {
		z-index: 1;
	}

	.title {
		h3 {
			font-size: 18px;
			font-weight: 600;
			color: #080C12;
			margin-bottom: 8px;
      -webkit-line-clamp: 2;
      overflow: hidden;
      -webkit-box-orient: vertical;
      max-height: 48px;
      display: -webkit-box;
      line-height: 140%;
      min-height: 46px;
      width: 100%;
      white-space: normal;
		}

		.dates {
			font-size: 12px;
      color: var(--neutral-800);
      font-family: var(--font);
		}
	}

	p {
		max-height: 20px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 240px;
	}

  .description {
    font-family: var(--font);
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    max-height: 100px;
  }

	.informations {
		font-size: 12px;
    display: flex;
    flex-direction: column;
    justify-content: end;
    height: 100%;
    color: var(--neutral-800);

		.associated-campaigns {
			margin: 0 0 24px 0;
    	height: 30px;
    	overflow: hidden;
    	text-overflow: ellipsis;
    	flex-wrap: wrap;

			.campaign-item {
    		padding: 4px 8px;
    		border-radius: 4px;
    		margin: 0 8px 16px 0;
				box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07),
    		0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);
			}
		}
	}

	.tags {
    flex-wrap: wrap;
    margin: 8px 0 0 0;
		font-size: 10px;
		line-height: 13px;

		>div {
			padding: 4px 8px;
			border-radius: 4px;
			color: #080C12;
			height: fit-content;
			background: #F2F2F3;
			box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07),
    		0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);
      margin-left: 8px;
       font-family: var(--font);
      &:nth-child(1){
        margin-left: 0;
      }

			&.published {
				background: var(--main-100);
			}

			&.unpublished {
				color: #ACB1B9;
			}

			&.finished {
				color: #FFFFFF;
				background: #080C12;
			}
		}
	}

	.edit {
		color: #FFFFFF;
	}

	.actions {
		&.finished {
			pointer-events: none;
			opacity: 0.3;

			a, div {
				cursor: not-allowed !important;
			}
		}

		&.not-published {
			a {
				pointer-events: none;
				opacity: 0.3;
				cursor: not-allowed !important;
			}
		}

		.em-primary-button {
			width: fit-content;
			border: 1px solid var(--main-500);

			&:hover {
				background-color: white;
				color: var(--main-500);
			}
		}
	}
}

</style>
