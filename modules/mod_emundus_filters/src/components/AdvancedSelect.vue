<template>
  <div id="advanced-select" class="advanced-search em-w-100">
    <input type="text" v-model="search" class="em-w-100"
           :placeholder="translate('MOD_EMUNDUS_FILTERS_GLOBAL_SEARCH_PLACEHOLDER')" @focusin="opened = true">

    <ul :class=" {
			'em-border-radius-8 em-border-neutral-400 em-w-100 em-box-shadow em-white-bg em-mt-4': true,
			'hidden': opened === false,
		}">
      <div v-for="group in groupedFilters" :key="group.id">
        <li class="em-mt-8 em-mb-8 em-pl-8"><strong>{{ group.label }}</strong></li>
        <li v-for="option in group.options" :key="option.id" class="em-mb-8 em-pl-16 em-pointer"
            @click="onClick(option.id)"> {{ option.label }}
        </li>
      </div>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'AdvancedSelect.vue',
  props: {
    moduleId: {
      type: Number,
      required: true
    },
    filters: {
      type: Array,
      required: true
    },
  },
  data() {
    return {
      groupedOptions: [],
      search: '',
      selected: -1,
      opened: false
    };
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    onClick(id) {
      this.$emit('filter-selected', id);
      this.opened = false;
      this.search = '';
    },
    handleClickOutside(event) {
      if (!this.$el.contains(event.target)) {
        this.opened = false;
        this.search = '';
      }
    },
  },
  computed: {
    groupedFilters() {
      const groups = [];
      const alreadyAdded = [];

      this.displayedFilters.forEach((filter) => {
        if (!alreadyAdded.includes(filter.group_id)) {
          groups.push({
            id: filter.group_id,
            label: filter.group_label,
            options: []
          });
          alreadyAdded.push(filter.group_id);
        }

        const currentFilterGroup = groups.find((group) => group.id === filter.group_id);
        currentFilterGroup.options.push(filter);
      });

      return groups;
    },
    displayedFilters() {
      return this.filters.filter((filter) => {
        return filter.label.toLowerCase().includes(this.search.toLowerCase()) || filter.group_label.toLowerCase().includes(this.search.toLowerCase());
      });
    }
  }
}
</script>

<style scoped>
#advanced-select ul {
  list-style-type: none;
  max-height: 300px;
  overflow-y: auto;
}

#advanced-select ul li.em-pointer:hover {
  background-color: #E4E4E4;
}
</style>