<template>
  <div id="rankings-by-package">
    <div class="w-full flex justify-end">
      <button v-if="canExport" class="em-primary-button w-fit" @click="openExportView">Export</button>
    </div>

    <nav id="ranking-navigation" class="mt-4 mb-4">
      <ul class="flex flex-row list-none overflow-auto pt-4">
        <li v-for="rankingPackage in packages" :key="rankingPackage.id"
            class="ranking-navigation-item cursor-pointer shadow rounded-t-lg px-2.5 py-3 text-center"
            :class="{
              'em-bg-main-500 em-text-neutral-300': selectedPackage === rankingPackage.id,
              'em-white-bg': selectedPackage !== rankingPackage.id
            }"
            @click="selectedPackage = rankingPackage.id"
            :title="rankingPackage.label"
        >
          <span>{{ rankingPackage.label }}</span>
        </li>
      </ul>
    </nav>
    <div v-if="selectedPackage !== null">
      <h3>{{ selectedPackageItem.label }}</h3>
      <classement
          :key="'classement-' + selectedPackage"
          :user="user"
          :hierarchy_id="hierarchy_id"
          :fileTabsStr="fileTabsStr"
          :specificTabs="specificTabs"
          :packageId="selectedPackage"
      >
      </classement>
    </div>

    <modal id="export-modal" name="export-modal">
      <export-ranking
          :user="user"
          :packages="packages"
      >
      </export-ranking>
    </modal>
  </div>
</template>

<script>
import rankingService from '@/services/ranking';
import Classement from "@/views/classement.vue";
import ExportRanking from "@/views/ExportRanking.vue";

export default {
  name: 'rankings',
  components: {Classement, ExportRanking},
  props: {
    user: {
      type: Number,
      required: true
    },
    hierarchy_id: {
      type: Number,
      required: true
    },
    fileTabsStr: {
      type: String,
      default: ''
    },
    specificTabs: {
      type: String,
      default: ''
    },
    canExport: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      packages: [],
      selectedPackage: null
    }
  },
  created() {
    this.getPackages();
  },
  methods: {
    getPackages() {
      rankingService.getPackages().then(response => {
        this.packages = response.data;

        this.selectedPackage = this.packages[0].id;
      }).catch(error => {
        console.log(error);
      });
    },
    openExportView() {
      this.$modal.show('export-modal');
    }
  },
  computed: {
    selectedPackageItem() {
      return this.packages.find(item => item.id === this.selectedPackage);
    },
  }
}
</script>

<style>
#rankings-by-package {
  .ranking-navigation-item {
    min-width: 200px;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  #export-modal .v--modal-box.v--modal {
    height: 80vh !important;
    width: 80vw !important;
    top: 10vh !important;
    left: 10vw !important;
  }
}
</style>