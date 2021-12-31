<template>
	<tr class="list-row">
		<td v-for="td in tds" :key="td.value">
			<span v-if="td.value == 'actions'"> 
				<list-action-menu
					id="list-row action-menu"
					:type="type"
					:itemId="data.id"
					:isPublished="actionMenuIsPublished"
					:showTootlip="hasActionMenu"
					@validateFilters="validateFilters"
					@updateLoading="updateLoading"
					@showModalPreview="showModalPreview"
				></list-action-menu>
			</span>
			<span 
				v-else-if="td.value == 'message'"
				:class="classFromTd(td)"
				v-html="formattedDataFromTd(td)"
			>
			</span>
			<span
				@click="redirectToEditItem(td)"
				:class="classFromTd(td)" 
			>
				{{ formattedDataFromTd(td) }}
			</span>
		</td>
	</tr>
</template>

<script>
import ListActionMenu from '../ListActionMenu.vue';
import rows from '../../../data/tableRows';
import moment from "moment";
import axios from "axios";
import { global } from "../../../store/global";
const qs = require('qs');

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
	},
	data() {
		return {
			tds: [],
			lang: 'fr',
			translations: {
				finished: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
				published: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
				unpublished: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
				active: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH_FORM"),
				inactive: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH_FORM"),
				emailType: {
					1: "Système",
					2: "Modèle",
				},
			}
		}
	},
	mounted() {
		this.tds = typeof rows[this.type] !== undefined ? rows[this.type] : [];
		this.lang = global.getters.actualLanguage;
	},
	methods: {
		formattedDataFromTd(td) {

			if (this.type === 'campaign') {
				return this.formattedCampaignData(td);
			} else if (this.type === 'email') {
				return this.formattedEmailData(td);
			} else if (this.type === 'form' || this.type === 'formulaire' || this.type === 'grilleEval') {
				return this.formattedFormData(td);
			}

			return this.data[td.value] ? this.data[td.value] : '-';
		},
		formattedCampaignData(td) {
			switch(td.value) {
				case 'status':
					if(this.isFinished) {
						return this.translations.finished;
					} else if(this.isPublished) {
						return this.translations.published;
					} else {
						return this.translations.unpublished;
					}
				case 'start_date':
				case 'end_date':
					return moment(this.data[td.value]).format('DD/MM/YYYY');
				case 'actions':
					return '';
				default: 
					return this.data[td.value] ? this.data[td.value] : '-';
			}
		},
		formattedEmailData(td) {
			switch(td.value) {
				case 'type':
					return this.translations.emailType[this.data[td.value]];
				case 'published': 
					if (this.isPublished) {
						return this.translations.published;
					} else {
						return this.translations.unpublished;
					}			
				case 'actions':
					return '';	
				default:
					return this.data[td.value] ? this.data[td.value] : '-';
			}
		},
		formattedFormData(td) {
			if (td.value === "published") {
				if (this.isActive) {
					return this.translations.active;
				} else {
					return this.translations.inactive;
				}
			}

			if (td.value === 'label' && this.data.label && typeof this.data.label === 'object') {
				return this.data.label[this.lang] ? this.data.label[this.lang] : this.data.label.fr;
			}

			if (td.value === 'actions') {
				return '';
			}

			return this.data[td.value] ? this.data[td.value] : '-';
		},
		classFromTd(td) {
			let classes;
			switch(td.value) {
				case 'status': 
				case 'published':
					if (this.isFinished) {
						classes = "tag finished";
					} else if(this.isPublished || this.isActive) {
						classes = "tag published";
					} else {
						classes = "tag unpublished";
					}
				break;

				case 'type': 
					classes = "tag " + this.data[td.value];
				break;
				case 'actions':
					return '';
				default:
					classes = "list-td-" + td.value;
			}

			return classes;
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
		redirectToEditItem(td) {
			if (td.redirect !== true) {
				return;
			}
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

			return false;
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
tr td {
	border-left: 0;
  border-right: 0;
	font-size: 12px;
	padding: 0.85rem 0.5rem;

	span {
		&.tag {
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

		&.list-td-label,
		&.list-td-subject {
			cursor: pointer;
			transition: all .3s;

			&:hover {
				color: #20835F;
			}
		}
	}
}
</style>