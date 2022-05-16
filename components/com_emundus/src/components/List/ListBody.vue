<template>
<div id="list-body">
	<ModalEmailPreview
		v-if="type == 'email'"
    :model="email.emailToPreview"
    :models="list"
  />

	<div id="actions">
		<div class="filters">

		</div>
		<div class="view-type">
			<span
				v-for="viewTypeOption in viewTypeOptions"
				:key="viewTypeOption.value"
				class="material-icons"
				:class="{'active': viewTypeOption.value === viewType}"
				@click="changeViewType(viewTypeOption)"
			>
				{{ viewTypeOption.icon }}
			</span>
		</div>
	</div>
	<list-table
		key="list-table"
		class="list-view"
		:class="{
			'not-displayed': viewType !== 'table',
		}"
		:type="type"
		:actions="actions"
		:params="params"
		@validateFilters="validateFilters"
		@updateLoading="updateLoading"
		@showModalPreview="showModalPreview"
	></list-table>
	<list-blocs
		key="list-blocs"
		class="list-view"
		:class="{
			'not-displayed': viewType !== 'blocs',
		}"
		:type="type"
		:actions="actions"
		:params="params"
		@validateFilters="validateFilters"
		@updateLoading="updateLoading"
		@showModalPreview="showModalPreview"
	></list-blocs>
</div>
</template>

<script>
import ListTable from './Table/ListTable.vue';
import ListBlocs from './Bloc/ListBlocs.vue';
import ModalEmailPreview from "@/components/AdvancedModals/ModalEmailPreview";
;

export default {
	components: {
		ListTable,
		ListBlocs,
		ModalEmailPreview
	},
	props: {
		type: {
			type: String,
			required: true
		},
		actions: {
			type: Object,
			required: true
		},
		params: {
			type: Object,
			default: {}
		}
	},
	data() {
		return {
			viewType: 'blocs',
			viewTypeOptions: [
				{
					value: 'table',
					icon: 'dehaze'
				},
				{
					value: 'blocs',
					icon: 'grid_view'
				}
			],
			email: {
				emailToPreview: "-1",
			}
		};
	},
  created() {
    this.viewType = localStorage.getItem('tchooz_view_type/' + document.location.hostname)
    if(this.viewType === null || typeof this.viewType === 'undefined' || (this.viewType !== 'blocs' && this.viewType !== 'table')){
      this.viewType = 'blocs';
      localStorage.setItem('tchooz_view_type/' + document.location.hostname,'blocs');
    }
  },

  methods: {
		changeViewType(viewType) {
			this.viewType = viewType.value;
      localStorage.setItem('tchooz_view_type/' + document.location.hostname,viewType.value);
		},
		validateFilters() {
			this.$emit('validateFilters');
		},
		updateLoading(value) {
			this.$emit('updateLoading',value);
		},
		showModalPreview(itemId) {
			this.email.emailToPreview = itemId;
      setTimeout(() => {
        this.$modal.show('modalEmailPreview_' + itemId);
      },200)
		}
	},
	computed: {
		list() {
			return this.$store.getters['lists/list'];
		}
	}
}
</script>

<style lang="scss" scoped>
#list-body {
	#actions {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin: 20px 0;

		.view-type {
			display: flex;
			align-items: center;
			justify-content: center;

			span {
				margin-left: 10px;
    		padding: 4px 4px 3px 4px;
				color: #E3E5E8;
    		border: 1px solid #E3E5E8;
    		border-radius: 4px;
				cursor: pointer;
				transition: all 0.3s;


				&.active {
					color: #298721;
					border-color: #298721;
				}
			}
		}
	}

	.list-view {
		transition: .3s;
	}

	.not-displayed {
		opacity: 0;
		pointer-events: none;
		z-index: -1;
		height: 0;
	}

	.scale-enter-active,
	.scale-leave-active {
	  transition: all 0.5s ease;
	}

	.scale-enter-from,
	.scale-leave-to {
	  opacity: 0;
	  transform: scale(0.9);
	}
}

.material-icons{
  font-size: 24px !important;
}
</style>
