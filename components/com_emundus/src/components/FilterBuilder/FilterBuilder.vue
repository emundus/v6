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
            v-for="(element, index) in orderedElements"
            :key="index"
          >
            <FilterRow
              v-if="element.type == 'filter'"
              class="filter-row"
              @removeFilter="removeElement(index)"
              :group="0"
            ></FilterRow>
            <FilterGroup
              v-if="element.type == 'group'"
              :id="element.group"
              @removeGroup="removeElement(index)"
            ></FilterGroup>
          </div>
        </div>
      </div>
      <div class="actions">
        <div class="btn-primary-vue" @click="addGroup">
          <span class="material-icons"> add </span>
          Ajouter un groupe
        </div>
        <div class="btn-primary-vue" @click="addFilter">
          <span class="material-icons"> add </span>
          Ajouter un filtre
        </div>
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
    addFilter() {
      this.orderedElements.push({
        type: "filter",
        group: 0,
      });
    },
    addGroup() {
      this.orderedElements.push({
        type: "group",
        group: Math.floor(Math.random() * Date.now()),
      });
    },
    removeElement(index) {
      this.orderedElements.splice(index, 1);
    },
  },
};
</script>

<style lang="scss" scoped>
#filter-builder {
  width: fit-content;

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