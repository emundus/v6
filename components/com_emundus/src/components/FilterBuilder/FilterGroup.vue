<template>
  <div class="group">
    <div class="wrapper">
      <div class="rows">
        <div class="relation">
          <label>Condition</label>
          <select name="and_or">
            <option value="and">ET</option>
            <option value="or">OU</option>
          </select>
        </div>
        <FilterRow
          :group="id"
          class="filter"
          v-for="(filter, index) in filters"
          :key="index"
          @removeFilter="removeFilter(index)"
        ></FilterRow>
      </div>
      <div class="actions">
        <div class="btn-primary-vue" @click="addFilter">
          <span class="material-icons"> add </span>
          Ajouter un filtre
        </div>
      </div>
    </div>
    <span class="material-icons delete" @click="$emit('removeGroup')">
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
    },
  },
  components: {
    FilterRow,
  },
  data() {
    return {
      filters: [],
    };
  },
  methods: {
    addFilter() {
      this.filters.push(1);
    },
    removeFilter(index) {
      this.filters.splice(index, 1);
    },
  },
};
</script>

<style lang="scss" scoped>
.group {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  margin: 20px 0 20px 40px;

  .wrapper {
    display: flex;
    flex-direction: column;
    padding: 16px;
    border: 1px solid var(--border-color);
  }
}
</style>