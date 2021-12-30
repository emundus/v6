<template>
	<tr class="list-row">
		<td v-for="td in tds" :key="td.value">
			<span 
				v-if="td.value !== 'actions'" 
				:class="classFromTd(td)" 
			>
				{{ dataValueFromTd(td) }}
			</span>
			<span v-if="td.value == 'actions'"> 
				<list-action-menu
					id="list-row action-menu"
					:type="type"
					:itemId="data.id"
					:isPublished="isPublished"
					:showTootlip="hasActionMenu"
					@validateFilters="validateFilters"
					@updateLoading="updateLoading"
					@showModalPreview="showModalPreview"
				></list-action-menu>
			</span>
		</td>
	</tr>
</template>

<script>
import ListActionMenu from '../ListActionMenu.vue';
import moment from "moment";
import rows from '../../../data/tableRows'

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
			translations: {
				finished: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
				published: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
				unpublished: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
			}
		}
	},
	mounted() {
		this.tds = typeof rows[this.type] !== undefined ? rows[this.type] : [];	
	},
	methods: {
		dataValueFromTd(td) {
			if (this.type === 'campaign') {
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
					default: 
						return this.data[td.value] ? this.data[td.value] : '-';
				}
			} else {
				return this.data[td.value] ? this.data[td.value] : '-';
			}
		},
		classFromTd(td) {
			let classes;
			switch(td.value) {
				case 'status': 
					if(this.isFinished) {
						classes = "tag finished";
					} else if(this.isPublished) {
						classes = "tag published";
					} else {
						classes = "tag unpublished";
					}
				break;
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
		}
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
}
</style>