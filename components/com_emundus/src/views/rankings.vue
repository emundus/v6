<template>
  <div id="rankings-by-package">
    <div v-for="rankingPackage in packages" :key="rankingPackage.id">
      <h2 class="mt-4">{{ rankingPackage.label }}</h2>
      <classement
        :user="user"
        :hierarchy_id="hierarchy_id"
        :fileTabsStr="fileTabsStr"
        :specificTabs="specificTabs"
        :packageId="rankingPackage.id"
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
    }
  },
  created() {
    this.getPackages();
  },
  methods: {
    getPackages() {
      rankingService.getPackages().then(response => {
        this.packages = response.data;
        console.log(response.data);
      }).catch(error => {
        console.log(error);
      });
    }
  }
}
</script>

<style scoped>

</style>