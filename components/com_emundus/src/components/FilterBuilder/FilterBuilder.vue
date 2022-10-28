<template>
  <div id="filter-builder">
    <h2>Filtres</h2>
    <div class="filters">
      <div class="rows">
        <div class="relation">
          <select name="and_or">
            <option value="and">ET</option>
            <option value="or">OU</option>
          </select>
        </div>
        <div class="elements">
          <div
            class="element"
            v-for="element in orderedElements"
            :key="element.type == 'filter' ? element.id : element.group"
          >
            <FilterRow
              v-if="element.type == 'filter'"
              class="filter-row"
              @removeFilter="removeFilter($event)"
              :group="0"
              :id="element.id"
            ></FilterRow>
            <FilterGroup
              v-if="element.type == 'group'"
              :id="element.group"
              @removeGroup="removeGroup($event)"
            ></FilterGroup>
          </div>
        </div>
      </div>
      <div class="actions">
        <div class="btn-primary-vue" @click="addGroup">
          <span class="material-icons-outlined"> add </span>
          Ajouter un groupe
        </div>
        <div class="btn-primary-vue" @click="addFilter">
          <span class="material-icons-outlined"> add </span>
          Ajouter un filtre
        </div>
      </div>
    </div>
    <div class="actions">
      <div class="btn-primary-vue" @click="addFilter">
        Annuler
      </div>
      <div class="btn-primary-vue" @click="applyFilters">
        Appliquer les filtres
      </div>
    </div>
  </div>
</template>

<script>
import filterService from "../../services/filter";
import FilterRow from "./FilterRow.vue";
import FilterGroup from "./FilterGroup.vue";

export default {
  name: "FilterBuilder",
  components: {
    FilterRow,
    FilterGroup,
  },
  props: {
    type: {
      type: String,
      default: "list",
    },
    id: {
      type: String,
      default: "70",
    },
  },
  data() {
    return {
      orderedElements: [],
    };
  },
  mounted() {
    this.getFilters();
  },
  methods: {
    async getFilters() {
      const response = await filterService.getFilters(this.type, this.id);

      if (response.status == true) {
        this.$store.dispatch("setFilters", response.filters);
      }
    },
    async applyFilters() {
      // build query and send result ? or just send the filters ?
      const response = await filterService.mountQuery(this.id, this.$store.state.queryFilters);

      if (response.status == true) {
        this.$emit("applyFilters", response.query);
      }
    },
    addFilter() {
      this.orderedElements.push({
        type: "filter",
        group: 0,
        id: Math.floor(Math.random() * Date.now()),
      });
    },
    addGroup() {
      this.orderedElements.push({
        type: "group",
        group: Math.floor(Math.random() * Date.now()),
      });
    },

    /**
     * Tips for v-for => avoid using index as key
     * When you remove an item of your array, you shift every index from the removal point up by one, which means only one index disappears from the array: the last one.
     */
    removeFilter(id) {
      // find filter position
      const index = this.orderedElements.findIndex((element) => {
        return element.id == id;
      });
      // remove filter
      this.orderedElements.splice(index, 1);
    },
    removeGroup(id) {
      // find filter position
      const index = this.orderedElements.findIndex((element) => {
        return element.group == id;
      });
      // remove filter
      this.orderedElements.splice(index, 1);
    },
  }
};
</script>

<style lang="scss" scoped>
#filter-builder {
  width: fit-content;
  background-color: white;
  padding: 16px;
  border-radius: 4px;
  margin: 20px;

  .filter-row {
    margin-left: 40px;
  }

  .actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-top: 20px;

    .btn-primary-vue {
      margin-left: 10px;
    }
  }
}
</style>
