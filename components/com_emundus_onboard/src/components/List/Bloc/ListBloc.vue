<template>
	<div class="list-bloc-item">
		<div class="title">
			<h3>{{ title }}</h3>
			<div class="dates" v-if="data.start_date && data.end_date">
				{{ translations.from }} <span>{{ formatDate(data.start_date) }}</span> {{ translations.to }} <span>{{ formatDate(data.end_date) }}</span>
			</div>
		</div>

		<div class="informations">
			<p v-if="data.short_description" class="description">
				{{ data.short_description }}
			</p>

			<div
				v-if="campaign.associatedCampaigns !== null && campaign.associatedCampaigns.length > 0"
				class="associated-campaigns"
				:title="translations.associatedCampaigns">
				<div
					v-for="campaign in campaign.associatedCampaigns"
					:key="campaign.id"
					class="tag campaign-item"
				>
					{{ campaign.label }}
				</div>

			</div>

			<div class="tags">
				<div
					v-if="!isFinished && isPublished !== null"
					:class="{
						published: isPublished,
						unpublished: !isPublished
					}"
				>
					{{ isPublished ? translations.publishedTag : translations.unpublishedTag }}
				</div>

				<div
					v-if="isActive !== null"
					:class="{
						published: isActive,
						unpublished: !isActive
					}"
				>
					{{ isActive ? translations.active : translations.inactive }}
				</div>

				<div v-if="isFinished" class="finished">
					{{ translations.isFinished }}
				</div>

				<div v-if="data.nb_files">
					<div>
						{{ data.nb_files }}
						<span v-if="data.nb_files > 1">{{ translations.files }}</span>
						<span v-else>{{ translations.file }}</span>
					</div>
				</div>

				<!-- <div v-if="type == 'email' && data.type">
					<div :class="'type-color-' + data.type">
						{{ translations.emailType[data.type] }}
					</div>
				</div> -->

				<div v-if="type == 'email' && data.category && data.category.length > 0">
					<div>
						{{ data.category }}
					</div>
				</div>
			</div>
		</div>

		<hr>

		<div class="actions">
			<a
				@click="redirectToEditItem"
				class="em-primary-button em-font-size-14"
			>
					{{ translations.edit }}
			</a>
			<list-action-menu
				:type="type"
				:itemId="data.id"
				:isPublished="actionMenuIsPublished"
				:showTootlip="hasActionMenu"
				@validateFilters="validateFilters"
				@updateLoading="updateLoading"
				@showModalPreview="showModalPreview"
			>
			</list-action-menu>
		</div>
	</div>
</template>

<script>
import moment from "moment";
import axios from "axios";
import ListActionMenu from '../ListActionMenu.vue';
import { global } from "../../../store/global";
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
				publishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
				unpublishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
				active: this.translate("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH_FORM"),
				inactive: this.translate("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH_FORM"),
				isFinished: this.translate("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
				edit: this.translate("COM_EMUNDUS_ONBOARD_MODIFY"),
				files: this.translate("COM_EMUNDUS_ONBOARD_FILES"),
      	file: this.translate("COM_EMUNDUS_ONBOARD_FILE"),
				emailType: {
					1: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_TYPE_SYSTEM"),
					2: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_TYPE_MODEL"),
				},
				campaignAssociated: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGN_ASSOCIATED"),
        campaignsAssociated: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED"),
				from: this.translate("COM_EMUNDUS_ONBOARD_FROM"),
				to: this.translate("COM_EMUNDUS_ONBOARD_TO"),
			},
			campaign: {
				associatedCampaigns: null
			}
		};
	},
	mounted() {
		this.lang = global.getters.actualLanguage;
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
			const link = this.getEditUrlByType();

			axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
        params: {
          link: link,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        window.location.href = window.location.pathname + response.data.data;
      });
		},
		getEditUrlByType() {
			let url;

			switch(this.type) {
				case 'campaign':
					url = 'index.php?option=com_emundus_onboard&view=campaign&layout=addnextcampaign&cid=' + this.data.id + '&index=0';
				break;
				case 'form':
				case 'formulaire':
					url = 'index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=' + this.data.id + '&index=0&cid=';
				break;
				case 'grilleEval':
					url =  "index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=&index=0&cid=" + "" + "&evaluation=" + this.data.id
				break;
				case 'email':
					url = 'index.php?option=com_emundus_onboard&view=email&layout=add&eid=' + this.data.id;
				break;
			}

			return url;
		},

		// Specific methods for campaigns
		getAssociatedCampaigns() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=form&task=getassociatedcampaign",
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
	},
	computed: {
		isPublished() {
			if (this.type == "campaign") {
				return (
      	  this.data.published == 1 &&
      	  moment(this.data.start_date) <= moment() &&
      	  (moment(this.data.end_date) >= moment() ||
      	    this.data.end_date == null ||
      	    this.data.end_date == "0000-00-00 00:00:00")
      	);
			} else if (this.type == "email") {
				return this.data.published == 1 ? true : false;
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
	}
}
</script>

<style lang="scss" scoped>

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
  box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07),
    0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);
	box-sizing: border-box;
	border-radius: 4px;

	hr {
		width: 100%;
		z-index: 1;
	}

	.title {
    margin-bottom: 16px;

		h3 {
			font-size: 18px;
			font-weight: 800;
			line-height: 23px;
			color: #080C12;
			margin-bottom: 8px;
		}

		.dates {
			font-size: 12px;
		}
	}

	p {
		max-height: 20px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 240px;
	}

	.informations {
		font-size: 12px;

		.associated-campaigns {
			margin: 0 0 24px 0;
    	width: 100%;
    	height: 30px;
    	overflow: hidden;
    	text-overflow: ellipsis;
    	display: flex;
    	flex-direction: row;
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
		display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    margin: 8px 0;
		font-size: 10px;
		line-height: 13px;

		>div {
			margin: 0 8px 8px 0;
			padding: 4px 8px;
			border-radius: 4px;
			color: #080C12;
			height: fit-content;
			background: #F2F2F3;
			box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07),
    		0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);

			&.published {
				background: #DFF5E9;
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
		display: flex;
		justify-content: space-between;
		align-items: center;
		width: 100%;

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
			border: 1px solid #20835F;
			cursor: pointer;

			&:hover {
				background-color: white;
				color: #20835F;
			}
		}
	}
}

</style>
