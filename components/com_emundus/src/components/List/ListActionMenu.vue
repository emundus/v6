<template>
	<div class="list-actions-menu">
        <span v-if="type == 'campaign' && allowPinnedCampaign == 1"
              :title="translate('COM_EMUNDUS_CAMPAIGNS_PIN')"
              @click="pinCampaign"
              :class="pinned == 1 ? 'material-icons' : 'material-icons-outlined em-text-neutral-500'"
              class="em-pointer">push_pin</span>
    <v-popover v-if="showTootlip === true" class="em-pointer" :popoverArrowClass="'custom-popover-arrow'">
      <span class="tooltip-target b3 material-icons-outlined">more_vert</span>
      <template slot="popover">
        <actions
            :data="{type: type}"
            :selected="itemId"
            :published="isPublished"
            :nb_files="nb_files"
            @validateFilters="validateFilters()"
            @updateLoading="updateLoading"
        ></actions>
      </template>
    </v-popover>
    <button
			v-if="type == 'email'"
			type="button"
      class="em-transparent-button"
			:title="translations.visualize"
			@click="showModalPreview"
		>
      <span class="material-icons-outlined">visibility</span>
    </button>
	</div>
</template>

<script>
import actions from "../ListComponents/action_menu.vue";
import campaignService from "../../services/campaign";

export default {
	components: { actions },
	props: {
		type: {
			type: String,
			required: true
		},
		itemId: {
			type: String,
			required: true
		},
		isPublished: {
			type: Boolean,
			required: true
		},
		showTootlip : {
			type: Boolean,
			default: true
		},
    nb_files : {
      type: Number,
      default: 0
    },
    pinned : {
      type: Number,
      default: 0
    }
	},
	data() {
		return {
			translations: {
				visualize: this.translate("COM_EMUNDUS_ONBOARD_VISUALIZE"),
			}
		}
	},
	methods: {
		showModalPreview() {
			this.$emit("showModalPreview");
		},
		validateFilters() {
      this.$emit('validateFilters');
    },
		updateLoading(value) {
      this.$emit('updateLoading',value);
    },
    pinCampaign() {
      campaignService.pinCampaign(this.itemId).then((result) => {
        if(result.data.status == 1){
          this.$store.dispatch('campaign/setPinned', this.itemId);
          Swal.fire({
            title: this.translate('COM_EMUNDUS_ONBOARD_CAMPAIGNS_CAMPAIGN_PINNED'),
            text: this.translate('COM_EMUNDUS_ONBOARD_CAMPAIGNS_CAMPAIGN_PINNED_TEXT'),
            type: "success",
            showCancelButton: false,
            showConfirmButton: false,
            customClass: {
              title: 'em-swal-title',
            },
            timer: 2000,
          });
        }
      });
    }
	},

  computed: {
    pinned: function(){
      return this.$store.getters['campaign/pinned'] === this.itemId;
    },
    allowPinnedCampaign: function(){
      return this.$store.getters['campaign/allowPinnedCampaign'];
    }
  }
}
</script>

<style lang="scss" scoped>
.list-actions-menu {
	display: flex;
	align-items: center;
	justify-content: flex-end;

	.cta-block:hover {
		color: #298721;
	}

	&#list-row#action-menu {
		margin: 0;
		transform: rotate(90deg);
	}
}
.material-icons, .material-icons-outlined{
  font-size: 24px !important;
}
</style>
