<template class="email-item">
  <div class="main-column-block" >
    <ModalEmailPreview
        :model="this.email_to_preview"
        :models="this.models"
    />

    <div class="column-block w-100" >
      <div class="block-dash" :class="isPublished ? '' : 'unpublishedBlock'">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8 pl-30px">
            <div class="list-item-header">
              <div class="block-label">
                <h2 class="nom-campagne-block">{{ data.subject }}</h2>
              </div>
            </div>
            <div class="em-flex-row gap">
              <div :class="isPublished ? 'publishedTag' : 'unpublishedTag'">
                {{ isPublished ? publishedTag : unpublishedTag }}
              </div>
              <div class="nb-dossier" :class="'type-color-' + data.type">
                <div>{{ type[langue][data.type - 1] }}</div>
              </div>
              <div class="nb-dossier" v-if="data.category !== '' && data.category !== null">
                {{ data.category }}
              </div>
            </div>
            <div>
              <hr class="divider-card">
              <div class="stats-block">
                <a @click="redirectJRoute('index.php?option=com_emundus&view=email&layout=add&eid=' + data.id)"
                   class="bouton-ajouter pointer add-button-div"
                   :title="Modify">
                  <em class="fas fa-pen"></em>
                  <span>{{Modify}}</span>
                </a>
                <div class="em-flex-row">
                  <button class="cta-block" style="height: unset" type="button" :title="Visualize" @click="$modal.show('modalEmailPreview_' + data.id)">
                    <em class="fas fa-eye"></em>
                  </button>
                  <v-popover :popoverArrowClass="'custom-popover-arraow'" v-if="data.lbl.startsWith('custom_') || data.lbl.startsWith('email_')">
                    <button class="tooltip-target b3 card-button"></button>

                    <template slot="popover">
                      <actions
                          :data="actions"
                          :selected="this.data.id"
                          :published="isPublished"
                          @validateFilters="validateFilters()"
                          @updateLoading="updateLoading"
                      ></actions>
                    </template>
                  </v-popover>
                </div>
              </div>
            </div>
          </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
;
import axios from "axios";
import ModalEmailPreview from "@/components/AdvancedModals/ModalEmailPreview";
import actions from "./action_menu";

const qs = require("qs");

export default {
  name: "emailItem",
  components: {ModalEmailPreview,actions},
  props: {
    data: Object,
    selectItem: Function,
    actualLanguage: String,
    models: Array,
    actions: Object,
  },
  data() {
    return {
      langue: 0,
      email_to_preview: -1,

      selectedData: [],
      publishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
      unpublishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
      passeeTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
      Modify: this.translate("COM_EMUNDUS_ONBOARD_MODIFY"),
      Visualize: this.translate("COM_EMUNDUS_ONBOARD_VISUALIZE"),

      type: [
        ['Système', 'Modèle'],
        ['System', 'Model']
      ]
    };
  },

  methods: {
    updateLoading(value){
      this.$emit('updateLoading',value);
    },

    validateFilters(){
      this.$emit('validateFilters');
    },

    redirectJRoute(link) {
      window.location.href = link;
    },
  },

  computed: {
    isPublished() {
      return this.data.published == 1;
    },

    isActive() {
      return this.$store.getters['lists/isSelected'](this.data.id);
    }
  },

  mounted() {
    if (this.actualLanguage === "en") {
      this.langue = 1;
    }
    this.email_to_preview = this.data.id;
  }
};
</script>

<style scoped>
.w-row{
  margin-bottom: 0;
}
</style>
