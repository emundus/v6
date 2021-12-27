<template>
	<div class="list-bloc-item">
		<div class="title">
			<h3>{{ title }}</h3>
		</div>

		<div class="dates">
		</div>

		<div class="informations">
			<p v-if="data.short_description" class="description">
				{{ data.short_description }}
			</p>
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
				
				<div v-if="isFinished">
					{{ translations.isFinished }}
				</div>

				<div v-if="data.nb_files">
					<div>
						{{ data.nb_files }}
						<span v-if="data.nb_files > 1">{{ translations.files }}</span>
						<span v-else>{{ translations.file }}</span>
					</div>
				</div>
			</div>
		</div>

		<div class="actions">
			<a 
				@click="redirectToEditItem" 
				class="edit bouton-ajouter pointer add-button-div"> 
				<span>
					{{ translations.edit }}
				</span> 
			</a>
		</div>
	</div>
</template>

<script>
import moment from "moment";
import axios from "axios";
const qs = require("qs");

export default {
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
			translations: {
				publishedTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
				unpublishedTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
				active: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH_FORM"),
				inactive: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH_FORM"),
				isFinished: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
				edit: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODIFY"),
				files: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILES"),
      	file: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILE")
			}
		};
	},
	mounted() {
		this.getTitle();
	},
	methods: {
		getTitle() {
			if (this.data.label && !this.data.label.fr) {
				this.title = this.data.label;
			} else if (this.data.label && this.data.label.fr) {
				this.title = this.data.label.fr;
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
					url = 'index.php?option=com_emundus_onboard&view=form&layout=edit&id=' + this.data.id;
				break;
				case 'emails':
					url = 'index.php?option=com_emundus_onboard&view=emails&layout=edit&id=' + this.data.id;
				break;
			}

			return url;
		}
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
			}

			return null;
    },
		isActive() {
			if (this.type == "form" || this.type == "formulaire" || this.type == "grilleEval") {
				return this.data.published == 1;
			}

			return null;
		},
		isFinished() {
			if (this.type == "campaign") {
				return moment(this.data.end_date) < moment();
			}

			return false;
		},
	}
}
</script>

<style lang="scss" scoped>
.list-bloc-item {
	width: 272px;
	height: 199px;
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	justify-content: space-between;
	padding: 16px;
	margin: 0 27px 17px 0;
	background: #FFFFFF;
	border: 1px solid #E3E5E8;
	box-sizing: border-box;
	border-radius: 4px;

	.title h3 {
		font-size: 18px;
		font-weight: 800;
		line-height: 23px;
		color: #080C12;
		margin-bottom: 8px;

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
}
</style>