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

      <div v-for="(tag, index) in tags" class="em-mb-24" :id="'tag_' + tag.id" :key="'tag_' + tag.id" @mouseover="enableGrab(index)" @mouseleave="disableGrab()">
        <div class="em-flex-row em-flex-row-start em-w-100">
          <div class="status-field">
            <div style="width: 100%">
              <p class="em-p-8-12 em-editable-content" contenteditable="true" :id="'tag_label_' + tag.id" @focusout="updateTag(tag)" @keyup.enter="manageKeyup(tag)" @keydown="checkMaxlength">{{tag.label}}</p>
            </div>
            <input type="hidden" :class="tag.class + '-500'">
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
            <a type="button" :title="translate('COM_EMUNDUS_ONBOARD_DELETE_TAGS')" @click="removeTag(tag,index)" class="em-flex-row em-ml-8 em-pointer">
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
      actualLanguage : '',
      swatches: [
        '#5E6580', '#D444F1', '#7959F8', '#0BA4EB', '#2E90FA', '#2970FE', '#15B79E', '#238C69', '#20835F', '#EAA907',
        '#F79009', '#EF681F', '#FF4305', '#DB333E', '#EE46BC', '#F53D68'
      ],
    };
  },

  created() {
    this.getTags();
    this.actualLanguage = this.$store.getters['global/shortLang'];
  },

  methods: {
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
      this.$emit('updateSaving',true);

      const formData = new FormData();
      formData.append('tag', tag.id);
      formData.append('label', document.getElementById(('tag_label_' + tag.id)).textContent);
      formData.append('color', tag.class);

      await client().post('index.php?option=com_emundus&controller=settings&task=updatetags',
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
      ).then(() => {
        this.$emit('updateSaving',false);
        this.$emit('updateLastSaving',this.formattedDate('','LT'));
      });
    },

    pushTag() {
      this.$emit('updateSaving',true);

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

        this.$emit('updateSaving',false);
        this.$emit('updateLastSaving',this.formattedDate('','LT'));
      });
    },

    removeTag(tag, index) {
      this.$emit('updateSaving',true);

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
        this.tags.splice(index,1);

        this.$emit('updateSaving',false);
        this.$emit('updateLastSaving',this.formattedDate('','LT'));
      });
    },

    manageKeyup(tag){
      document.getElementById(('tag_label_' + tag.id)).textContent = document.getElementById(('tag_label_' + tag.id)).textContent.trim();
      document.activeElement.blur();
    },

    getHexColors(element) {
      let tags_class = document.querySelector('.' + element.class + '-500');
      let style = getComputedStyle(tags_class);
      let rgbs = style.backgroundColor.split('(')[1].split(')')[0].split(',');
      element.class = this.rgbToHex(parseInt(rgbs[0]),parseInt(rgbs[1]),parseInt(rgbs[2]));
    },

    rgbToHex(r, g, b) {
      return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
    },

    checkMaxlength(event) {
      if(event.target.textContent.length === 50 && event.keyCode != 8) {
        event.preventDefault();
      }
    },

    enableGrab(index){
      if(this.tags.length !== 1){
        this.indexGrab = index;
        this.grab = true;
      }
    },
    disableGrab(){
      this.indexGrab = 0;
      this.grab = false;
    },
  },

};
</script>
<style scoped>
.status-field{
  border-radius: 5px;
  width: 100%;
  margin-right: 1em;
  display: flex;
}

.status-item{
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 95%;
  width: 100%;
}
</style>
