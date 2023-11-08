<template>
  <div class="em-settings-menu">
    <notifications
        group="foo-velocity"
        animation-type="velocity"
        :speed="500"
        position="bottom left"
        :classes="'vue-notification-custom'"
    />
    <ModalAddDatas
        @updateDatabases="getDatabases"
        :actualLanguage="actualLanguage"
        :manyLanguages="manyLanguages"
    />
    <ModalImportDatas
        @updateDatabases="getDatabases"
    />
    <div class="em-flex-row">
      <a @click="$modal.show('modalAddDatas')" class="bouton-ajouter-green bouton-ajouter pointer em-mr-4"
         style="width: max-content">
        <div class="add-button-div">
          <em class="fas fa-plus em-mr-4"></em>
          {{ CreateDatas }}
        </div>
      </a>
      <button type="button" class="bouton-sauvergarder-et-continuer" @click="$modal.show('modalImportDatas')">
        {{ ImportDatas }}
      </button>
    </div>
    <div class="mt-1">
      <div v-for="(database, index) in databases" :key="database.database_name" class="db-table">
        <div :class="[index == indexOpen ? 'down-arrow' : 'right-arrow']" class="db-item"
             @click="getDatas(database.database_name,index)">
          <h3>{{ database.label }}</h3>
          <p>{{ database.description }}</p>
        </div>
        <transition :name="'slide-down'" type="transition">
          <div v-if="index == indexOpen" class="mt-1">
            <table class="db-description">
              <tr class="db-columns">
                <th v-for="(data, i) in datas.columns" :key="i" :id="'column_' + data">{{ data }}</th>
              </tr>
              <tr v-for="(data, i) in datas.datas" :key="i" class="db-values">
                <th v-for="(value, key) in data" :key="key"> {{ value }}</th>
              </tr>
            </table>
          </div>
        </transition>
      </div>
    </div>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";
import ModalAddDatas from "@/components/AdvancedModals/ModalAddDatas";
import ModalImportDatas from "@/components/AdvancedModals/ModalImportDatas";

const qs = require("qs");

export default {
  name: "EditDatas",

  components: {
    ModalAddDatas,
    ModalImportDatas,
  },

  props: {
    actualLanguage: String,
    manyLanguages: Number,
  },

  data() {
    return {
      databases: [],
      datas: {
        columns: [],
        datas: []
      },
      indexOpen: -1,
      loading: false,
      CreateDatas: this.translate("COM_EMUNDUS_ONBOARD_CREATE_DATAS"),
      ImportDatas: this.translate("COM_EMUNDUS_ONBOARD_IMPORT_DATAS"),
      UpdateDatas: this.translate("COM_EMUNDUS_ONBOARD_UPDATE_DATAS"),
    };
  },

  methods: {
    getDatabases() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=formbuilder&task=getdatabasesjoin",
      }).then(response => {
        this.databases = response.data.data;
      });
    },
    getDatas(db_name, index) {
      if (index == this.indexOpen) {
        this.indexOpen = -1;
      } else {
        this.loading = true;
        this.indexOpen = index;
        this.datas = {
          columns: [],
          datas: []
        };
        axios({
          method: "get",
          url: "index.php?option=com_emundus&controller=settings&task=getdatasfromtable",
          params: {
            db: db_name,
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
          this.loading = false;
          this.datas.datas = response.data.data;
          this.datas.columns = Object.keys(this.datas.datas[0]);
        });
      }
    },
    /**
     * ** Methods for notify
     */
    tip() {
      this.show(
          "foo-velocity",
          this.translate("COM_EMUNDUS_ONBOARD_BUILDER_UPDATE"),
          this.translate("COM_EMUNDUS_ONBOARD_COLOR_SUCCESS"),
      );
    },
    show(group, text = "", title = "Information") {
      this.$notify({
        group,
        title: `${title}`,
        text: text,
        duration: 3000
      });
    },
    clean(group) {
      this.$notify({group, clean: true});
    },
  },

  created() {
    this.getDatabases();
  },

  watch: {}
};
</script>
<style>
.db-item {
  background-size: 20px;
  cursor: pointer;
  height: 35px;
}
</style>
