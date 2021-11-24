<template>
  <div class="group">
    <div class="wrapper">
      <div class="rows">
        <div class="relation">
          <button class="and-or" @click="toggleAndOr">
            {{ andOr == "AND" ? 'ET' : 'OU' }}
          </button>
        </div>
        <FilterRow
          :group="id"
          :id="filter"
          class="filter"
          v-for="filter in filters"
          :key="filter"
          @removeFilter="removeFilter(filter)"
        ></FilterRow>
      </div>
      <div class="actions add">
        <span class="material-icons add" @click="addFilter"> 
          add_circle_outlined
         </span>
      </div>
    </div>
    <span class="material-icons delete" @click="removeGroup">
      clear
    </span>
  </div>
</template>

<script>
import FilterRow from "./FilterRow.vue";

export default {
  name: "FilterGroup",
  props: {
    id: {
      type: Number,
      required: true,
    }
  },
  components: {
    FilterRow,
  },
  data() {
    return {
      andOr: "AND",
      filters: [],
    };
  },
  mounted() {
    this.filters = this.$store.state.filterBuilder.queryFilters.groups[this.id] ? Object.entries(this.$store.state.filterBuilder.queryFilters.groups[this.id].filters).map(filter => Number(filter[0])) : [];
    if (this.filters.length < 1) {
      this.addFilter();
    }
  },
  methods: {
    addFilter() {
      this.filters.push(Math.floor(Math.random() * Date.now()));
    },
    removeFilter(filterId) {
      this.filters = this.filters.filter(filter => filter !== filterId);
      
      if (this.filters.length < 1) {
        this.removeGroup();
      }
    },
    removeGroup() {
      this.$store.dispatch("filterBuilder/removeGroup", this.id);

      this.$emit("removeGroup", this.id);
    },
    toggleAndOr() {
      this.andOr = this.andOr === "AND" ? "OR" : "AND";
    }
  },
  watch: {
    andOr() {
      this.$store.dispatch("filterBuilder/updateAndOr", {
        group: this.id,
        and_or: this.andOr,
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.group {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  margin: 20px 0 20px 0px;

  .relation {
    margin-bottom: 10px;

    .and-or {
      transition: all .3s;
      background-color: var(--primary-color);
      color: white;
      padding: 5px;
      border-radius: 4px;
    }
  }

  .wrapper {
    display: flex;
    flex-direction: column;
    padding: 8px 16px;
    border-radius: 4px;
    background-color: var(--grey-bg-color);
  }

  .actions.add {
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
    cursor: pointer;
    margin-top: 8px;

    .material-icons {
      margin-right: 10px;
      color: var(--primary-color);
    }
  }
}
</style>