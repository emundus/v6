<template>
  <div id="export-ranking" class="p-4">
    <h4>Export ranking - WORK IN PROGRESS</h4>

    <div class="p-2">
      <h5>{{ translate('COM_EMUNDUS_RANKING_EXPORT_PACKAGES') }}</h5>
      <div id="select-packages-wrapper" class="p-4">
        <div class="flex flex-row items-center">
          <input type="checkbox" v-model="selectAllPackages" @change="toggleAllPackages" name="selectAll" id="selectAll">
          <label for="selectAll">{{ translate('COM_EMUNDUS_SELECT_ALL') }}</label>
        </div>
        <div v-for="rankingPackage in userPackages" :key="rankingPackage.id" class="flex flex-row items-center">
          <input type="checkbox" v-model="selectedPackages" :value="rankingPackage.id" name="selectedPackages" :id="'package-' + rankingPackage.id">
          <label :for="'package-' + rankingPackage.id">{{ rankingPackage.label }}</label>
        </div>
      </div>

      <h5>{{ translate('COM_EMUNDUS_RANKING_EXPORT_HIERARCHIES') }}</h5>
      <div class="p-4">
        <div v-for="hierarchy in hierarchies" :key="hierarchy.id" class="flex flex-row items-center">
          <input type="checkbox" v-model="selectedHierarchies" :value="hierarchy.id" name="selectedHierarchies" :id="'hierarchy-' + hierarchy.id">
          <label :for="'hierarchy-' + hierarchy.id">{{ hierarchy.label }}</label>
        </div>
      </div>

      <h5>{{ translate('COM_EMUNDUS_RANKING_EXPORT_COLUMNS') }}</h5>
      <div class="p-4">
        <div v-for="column in columns" :key="column.id" class="flex flex-row items-center">
          <input type="checkbox" v-model="selectedColumns" :value="column.id" name="selectedColumns" :id="'column-' + column.id">
          <label :for="'column-' + column.id">{{ column.label }}</label>
        </div>
      </div>
    </div>

    <div class="w-full flex justify-end">
      <button class="em-primary-button w-fit" @click="exportRanking">Export</button>
      <a v-if="downloadLink" :href="downloadLink" download>Download</a>
    </div>
  </div>
</template>

<script>
import rankingService from "@/services/ranking";

export default {
  name: "export-ranking",
  props: {
    user: {
      type: Number,
      required: true
    },
    packages: {
      type: Array,
      default: []
    }
  },
  data() {
    return {
      userPackages: [],
      selectAllPackages: false,
      selectedPackages: [],
      hierarchies: [],
      selectedHierarchies: [],
      columns: [
        {id: 'name', label: 'Name'},
        {id: 'status', label: 'Status'},
        {id: 'ranker', label: 'Ranker'},
      ],
      selectedColumns: ['fnum', 'name', 'status', 'rank', 'ranker'],
      downloadLink: null
    }
  },
  created() {
    if (this.packages.length === 0) {
      this.getPackages();
    } else {
      this.userPackages = this.packages;
    }
    this.getHierarchiesUserCanSee();
  },
  methods: {
    getPackages() {
      rankingService.getPackages().then(response => {
        this.userPackages = response.data;
      }).catch(error => {
        console.log(error);
      });
    },
    getHierarchiesUserCanSee() {
      rankingService.getHierarchiesUserCanSee().then(response => {
        this.hierarchies = response.data;
      }).catch(error => {
        console.log(error);
      });
    },
    toggleAllPackages() {
      if (this.selectAllPackages) {
        this.selectedPackages = this.userPackages.map(p => p.id);
      } else {
        this.selectedPackages = [];
      }
    },
    exportRanking() {
      if (this.selectedPackages.length === 0) {
        alert('Please select at least one package');
        return;
      }

      rankingService.exportRanking(this.selectedPackages, this.selectedHierarchies, this.selectedColumns).then(response => {
        if (response.data) {
          // Download the file
          console.log(response.data);

          // force download response.data.data
          this.downloadLink = response.data.data;
        }
      }).catch(error => {
        console.log(error);
      });
    }
  }
}
</script>

<style scoped>
label {
  margin: 0;
}
</style>