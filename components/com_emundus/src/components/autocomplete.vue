<template>
  <div class="autocomplete">
    <input
      type="text"
      :id="id"
      v-model="search"
      @input="onChange"
      @keydown.down="onArrowDown"
      @keydown.up="onArrowUp"
      @keydown.enter="onEnter"
      :placeholder="year !== '' ? year : name"
      :class="year !== '' ? '' : 'placeholder'"
      class="em-w-100"
    />
    <ul v-show="isOpen" class="autocomplete-results">
      <li
        v-for="(result, i) in results"
        :key="i"
        @click="setResult(result)"
        class="autocomplete-result"
        :class="{ 'is-active': i === arrowCounter }"
      >
        {{ result }}
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: "autocomplete",

  props: {
    items: {
      type: Array,
      required: false,
      default: () => []
    },
    name: String,
    year: String,
    id: String,
  },

  data() {
    return {
      search: "",
      results: [],
      isOpen: false,
      sLoading: false,
      arrowCounter: -1
    };
  },

  created() {
    const sleep = milliseconds => {
      return new Promise(resolve => setTimeout(resolve, milliseconds));
    };
    sleep(2000).then(() => {
      this.search = this.year;
    });
  },

  methods: {
    onSearching(event) {
      this.$emit("searched", this.search);
    },

    onChange() {
      this.isOpen = true;
      this.filterResults();
      this.onSearching();
    },
    filterResults() {
      this.results = this.items.filter(
        item => item.toLowerCase().indexOf(this.search.toLowerCase()) > -1
      );
    },
    setResult(result) {
      this.search = result;
      this.isOpen = false;
      this.onSearching();
    },
    onArrowDown() {
      if (this.arrowCounter < this.results.length) {
        this.arrowCounter = this.arrowCounter + 1;
      }
    },
    onArrowUp() {
      if (this.arrowCounter > 0) {
        this.arrowCounter = this.arrowCounter - 1;
      }
    },
    onEnter() {
      this.search = this.results[this.arrowCounter] ? this.results[this.arrowCounter] : this.search;
      this.isOpen = false;
      this.arrowCounter = -1;
      this.onSearching();
    },
    handleClickOutside(evt) {
      if (!this.$el.contains(evt.target)) {
        this.isOpen = false;
        this.arrowCounter = -1;
      }
    }
  },
  mounted() {
    document.addEventListener("click", this.handleClickOutside);
  },
  destroyed() {
    document.removeEventListener("click", this.handleClickOutside);
  }
};
</script>

<style scoped>
.autocomplete {
  position: relative;
  width: 100%;
  display: block;
  font-size: 14px;
  line-height: 1.428571429;
  color: #333333;
  background-color: #ffffff;
}

.autocomplete input {
	height: 48px;
	margin-bottom: 0px;
}

.autocomplete-results {
  padding: 0;
  margin: 0;
  border: 1px solid #eeeeee;
  overflow: auto;
  background-color: white;
  position: relative;
  z-index: 10;
}

.autocomplete-result {
  list-style: none;
  text-align: left;
  padding: 16px 6px;
  cursor: pointer;
}

.autocomplete-result.is-active,
.autocomplete-result:hover {
  background-color: #EDEDED;
  color: white;
}
</style>
