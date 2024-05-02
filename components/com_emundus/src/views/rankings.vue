<template>
  <div id="rankings-by-package">
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
  </div>
</template>

<script>
import rankingService from '@/services/ranking';
import Classement from "@/views/classement.vue";

export default {
  name: 'rankings',
  components: {Classement},
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
    }
  },
  computed: {
    selectedPackageItem() {
      return this.packages.find(item => item.id === this.selectedPackage);
    },
  }
}
</script>

<style scoped>
#rankings-by-package {
  .ranking-navigation-item {
    min-width: 200px;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
</style>