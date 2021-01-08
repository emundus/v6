<template class="email-item">
  <div class="main-column-block">
    <div class="column-block w-100">
      <div class="block-dash" :class="isPublished ? '' : 'unpublishedBlock'">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8 pl-30px">
            <div class="list-item-header">
              <div class="block-label">
                <a v-if="data.type == 2"
                   class="item-select w-inline-block"
                   v-on:click="selectItem(data.id)"
                   :class="{ active: isActive }"
                ></a>
                <h1 class="nom-campagne-block" :style="data.type != 2 ? 'margin-left: 35px' : ''">{{ data.subject }}</h1>
              </div>
              <div :class="isPublished ? 'publishedTag' : 'unpublishedTag'">
                {{ isPublished ? publishedTag : unpublishedTag }}
              </div>
            </div>
            <a href="#" class="button-programme" style="margin-left: 35px">{{ type[langue][data.type - 1] }}</a>
            <p class="description-block"><span v-html="data.message"></span></p>
            <div class="stats-block" style="justify-content: flex-end">
              <a class="cta-block pointer"
                 @click="redirectJRoute('index.php?option=com_emundus_onboard&view=email&layout=add&eid=' + data.id)"
                 :title="Modify">
                <em class="fas fa-pen"></em>
              </a>
            </div>
          </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
import { list } from "../../store";
import axios from "axios";

const qs = require("qs");

export default {
  name: "emailItem",
  props: {
    data: Object,
    selectItem: Function,
    actualLanguage: String
  },
  data() {
    return {
      langue: 0,

      selectedData: [],
      publishedTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
      unpublishedTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
      passeeTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
      Modify: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODIFY"),
      Visualize: Joomla.JText._("COM_EMUNDUS_ONBOARD_VISUALIZE"),

      type: [
        ['Système', 'Modèle'],
        ['System', 'Model']
      ]
    };
  },

  methods: {
    redirectJRoute(link) {
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
    }
  },

  computed: {
    isPublished() {
      return this.data.published == 1;
    },

    isActive() {
      return list.getters.isSelected(this.data.id);
    }
  },

  mounted() {
    if (this.actualLanguage == "en") {
      this.langue = 1;
    }
  }
};
</script>

<style scoped>
.w-row{
  margin-bottom: 0;
}
</style>
