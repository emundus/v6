<template>
  <div class="em-settings-menu">
    <div class="em-w-80">

      <div class="em-grid-3 em-mb-16">
        <button @click="pushTag" class="em-primary-button em-mb-24" style="width: max-content">
          <div class="add-button-div">
            <em class="fas fa-plus em-mr-4"></em>
            {{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_ADDTAG') }}
          </div>
        </button>
      </div>

      <div v-for="(tag, index) in tags" class="em-mb-24" :id="'tag_' + tag.id" :key="'tag_' + tag.id"
           @mouseover="enableGrab(index)" @mouseleave="disableGrab()">
        <div class="em-flex-row em-flex-row-start em-w-100">
          <div class="status-field">
            <div style="width: 100%">
              <p class="em-p-8-12 em-editable-content" contenteditable="true" :id="'tag_label_' + tag.id"
                 @focusout="updateTag(tag)" @keyup.enter="manageKeyup(tag)" @keydown="checkMaxlength">
                {{ tag.label }}</p>
            </div>
            <input type="hidden" :class="tag.class">
          </div>
          <div class="em-flex-row">
            <v-swatches
                v-model="tag.class"
                @input="updateTag(tag)"
                :swatches="swatches"
                shapes="circles"
                row-length="8"
                show-border
                popover-x="left"
                popover-y="top"
            ></v-swatches>
            <a type="button" :title="translate('COM_EMUNDUS_ONBOARD_DELETE_TAGS')" @click="removeTag(tag,index)"
               class="em-flex-row em-ml-8 em-pointer">
              <span class="material-icons-outlined em-red-500-color">delete_outline</span>
            </a>
          </div>
        </div>
        <hr/>
      </div>
    </div>
  </div>
</template>

<script>
/* COMPONENTS */
import draggable from "vuedraggable";
import axios from "axios";
import VSwatches from 'vue-swatches'
import 'vue-swatches/dist/vue-swatches.css'

/* SERVICES */
import client from "com_emundus/src/services/axiosClient";
import mixin from "com_emundus/src/mixins/mixin";


const qs = require("qs");

export default {
  name: "editTags",

  components: {
    VSwatches,
    draggable
  },

  props: {},

  mixins: [mixin],

  data() {
    return {
      index: "",
      indexGrab: "0",

      grab: 0,
      loading: false,

      tags: [],
      show: false,
      actualLanguage: '',
      swatches: [],
      colors: [],
      variables: null,
    };
  },

  created() {
    let root = document.querySelector(':root');
    this.variables = getComputedStyle(root);

    this.prepareSwatchesColor();
    this.getTags();
    this.actualLanguage = this.$store.getters['global/shortLang'];
  },

  methods: {
    prepareSwatchesColor() {
      this.swatches.push(this.variables.getPropertyValue('--em-red-1'));
      this.colors.push({name: 'red-1', value: this.variables.getPropertyValue('--em-red-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-red-2'));
      this.colors.push({name: 'red-2', value: this.variables.getPropertyValue('--em-red-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-pink-1'));
      this.colors.push({name: 'pink-1', value: this.variables.getPropertyValue('--em-pink-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-pink-2'));
      this.colors.push({name: 'pink-2', value: this.variables.getPropertyValue('--em-pink-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-purple-1'));
      this.colors.push({name: 'purple-1', value: this.variables.getPropertyValue('--em-purple-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-purple-2'));
      this.colors.push({name: 'purple-2', value: this.variables.getPropertyValue('--em-purple-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-light-blue-1'));
      this.colors.push({name: 'light-blue-1', value: this.variables.getPropertyValue('--em-light-blue-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-light-blue-2'));
      this.colors.push({name: 'light-blue-2', value: this.variables.getPropertyValue('--em-light-blue-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-blue-1'));
      this.colors.push({name: 'blue-1', value: this.variables.getPropertyValue('--em-blue-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-blue-2'));
      this.colors.push({name: 'blue-2', value: this.variables.getPropertyValue('--em-blue-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-blue-3'));
      this.colors.push({name: 'blue-3', value: this.variables.getPropertyValue('--em-blue-3')})
      this.swatches.push(this.variables.getPropertyValue('--em-green-1'));
      this.colors.push({name: 'green-1', value: this.variables.getPropertyValue('--em-green-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-green-2'));
      this.colors.push({name: 'green-2', value: this.variables.getPropertyValue('--em-green-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-yellow-1'));
      this.colors.push({name: 'yellow-1', value: this.variables.getPropertyValue('--em-yellow-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-yellow-2'));
      this.colors.push({name: 'yellow-2', value: this.variables.getPropertyValue('--em-yellow-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-orange-1'));
      this.colors.push({name: 'orange-1', value: this.variables.getPropertyValue('--em-orange-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-orange-2'));
      this.colors.push({name: 'orange-2', value: this.variables.getPropertyValue('--em-orange-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-beige'));
      this.colors.push({name: 'beige', value: this.variables.getPropertyValue('--em-beige')})
      this.swatches.push(this.variables.getPropertyValue('--em-brown'));
      this.colors.push({name: 'brown', value: this.variables.getPropertyValue('--em-brown')})
      this.swatches.push(this.variables.getPropertyValue('--em-grey-1'));
      this.colors.push({name: 'grey-1', value: this.variables.getPropertyValue('--em-grey-1')})
      this.swatches.push(this.variables.getPropertyValue('--em-grey-2'));
      this.colors.push({name: 'grey-2', value: this.variables.getPropertyValue('--em-grey-2')})
      this.swatches.push(this.variables.getPropertyValue('--em-black'));
      this.colors.push({name: 'black', value: this.variables.getPropertyValue('--em-black')})
    },

    getTags() {
      axios.get("index.php?option=com_emundus&controller=settings&task=gettags")
          .then(response => {
            this.tags = response.data.data;
            setTimeout(() => {
              this.tags.forEach(element => {
                this.getHexColors(element);
              });
            }, 100);
          });
    },

    async updateTag(tag) {
      this.$emit('updateSaving', true);

      let index = this.colors.findIndex(item => item.value === tag.class);
      const formData = new FormData();
      formData.append('tag', tag.id);
      formData.append('label', document.getElementById(('tag_label_' + tag.id)).textContent);
      formData.append('color', this.colors[index].name);

      await client().post('index.php?option=com_emundus&controller=settings&task=updatetags',
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
      ).then(() => {
        this.$emit('updateSaving', false);
        this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
      });
    },

    pushTag() {
      this.$emit('updateSaving', true);

      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=settings&task=createtag',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then((newtag) => {
        this.tags.push(newtag.data);
        setTimeout(() => {
          this.getHexColors(newtag.data);
        }, 100);

        this.$emit('updateSaving', false);
        this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
      });
    },

    removeTag(tag, index) {
      this.$emit('updateSaving', true);

      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=settings&task=deletetag',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          id: tag.id
        })
      }).then(() => {
        this.tags.splice(index, 1);

        this.$emit('updateSaving', false);
        this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
      });
    },

    manageKeyup(tag) {
      document.getElementById(('tag_label_' + tag.id)).textContent = document.getElementById(('tag_label_' + tag.id)).textContent.trim();
      document.activeElement.blur();
    },

    getHexColors(element) {
      element.translate = false;
      element.class = this.variables.getPropertyValue('--em-' + element.class.replace('label-', ''));
    },

    checkMaxlength(event) {
      if (event.target.textContent.length === 50 && event.keyCode != 8) {
        event.preventDefault();
      }
    },

    enableGrab(index) {
      if (this.tags.length !== 1) {
        this.indexGrab = index;
        this.grab = true;
      }
    },
    disableGrab() {
      this.indexGrab = 0;
      this.grab = false;
    },
  },

};
</script>
<style scoped>
.status-field {
  border-radius: 5px;
  width: 100%;
  margin-right: 1em;
  display: flex;
}

.status-item {
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 95%;
  width: 100%;
}
</style>
