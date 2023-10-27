<template>
  <div id="edit-gallery">
    <div class="flex items-center em-pointer w-min" @click="redirectJRoute('index.php?option=com_emundus&view=gallery')">
      <span class="material-icons-outlined">arrow_back</span>
      <p class="ml-2">{{ translate('BACK') }}</p>
    </div>

    <div>
      <h1 class="mt-3">{{ translate('COM_EMUNDUS_ONBOARD_EDIT_GALLERY') }}</h1>
      <a v-if="gallery" class="mt-2 em-tertiary-button flex w-min p-0 items-center justify-start gap-2 hover:no-underline" target="_blank" :href="'/index.php?option=com_fabrik&view=list&listid='+gallery.list_id">
        <span class="material-icons-outlined">visibility</span>
        {{ translate('COM_EMUNDUS_ONBOARD_EDIT_PREVIEW') }}
      </a>
    </div>

    <!--- Menu --->
    <div class="flex items-center mt-4" >
      <ul class="nav nav-tabs topnav">

        <li v-for="(menu, index) in menus" :key="'category-' + index">
          <a  @click="selectedMenu = index;"
              class="em-neutral-700-color em-pointer"
              :class="[(selectedMenu === index ? 'w--current' : '')]">
            {{ translate(menu) }}
          </a>
        </li>
      </ul>
    </div>

    <transition>
      <display
          v-if="selectedMenu === 0 && gallery && elements && simple_fields && choices_fields && image_attachments && description_fields"
          :gallery="gallery"
          :elements="elements"
          :simple_fields="simple_fields"
          :choices_fields="choices_fields"
          :description_fields="description_fields"
          :image_attachments="image_attachments"
          @updateAttribute="updateAttribute"
          @updateLoader="updateLoading"
      ></display>
      <gallery-details
          v-if="selectedMenu === 1 && gallery && elements && simple_fields && choices_fields && image_attachments && description_fields"
          :gallery="gallery"
          :elements="elements"
          :simple_fields="simple_fields"
          :choices_fields="choices_fields"
          :description_fields="description_fields"
          :image_attachments="image_attachments"
          @updateAttribute="updateAttribute"
          @updateLoader="updateLoading"
      ></gallery-details>
      <settings
          v-if="selectedMenu === 2 && gallery"
          :gallery="gallery"
          @updateAttribute="updateAttribute"
          @updateLoader="updateLoading"
      ></settings>
    </transition>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import Swal from "sweetalert2";

import Display from "@/components/Gallery/display.vue";
import Settings from "@/components/Gallery/settings.vue";
import galleryDetails from "@/components/Gallery/details_setup.vue";

/** SERVICES **/

const qs = require("qs");

export default {
  name: "editGallery",

  components: {galleryDetails, Settings, Display},

  directives: {},

  props: {},

  data: () => ({
    loading: false,

    selectedMenu: 0,
    menus: [
      'COM_EMUNDUS_GALLERY_DISPLAY',
      'COM_EMUNDUS_GALLERY_DETAILS',
      'COM_EMUNDUS_GALLERY_SETTINGS'
    ],

    gallery: null,
    elements: null,
    simple_fields: null,
    choices_fields: null,
    description_fields: null,
    image_attachments: [
      {allowed_types: '',id:0,value:'Aucune image'}
    ],
  }),

  async created() {
    this.loading = true;
    let gid = this.$store.getters['global/datas'].gallery.value;

    fetch('index.php?option=com_emundus&controller=gallery&task=getgallery&id='+gid)
        .then(response => response.json())
        .then(data => {
          this.gallery = data.data;

          this.getElements();
          this.getAttachments();
        })
        .catch((error) => {
          console.error('Error:', error);
        });
  },
  methods: {
    redirectJRoute(link) {
      window.location.href = link
    },

    async getElements() {
      fetch('index.php?option=com_emundus&controller=gallery&task=getelements&campaign_id='+this.gallery.campaign_id+'&list_id='+this.gallery.list_id)
          .then(response => response.json())
          .then(data => {
            this.elements = data.data.elements;
            this.simple_fields = Object.values(data.data.simple_fields);
            this.choices_fields = data.data.choices_fields;
            this.description_fields = data.data.description_fields;
          });
    },

    async getAttachments() {
      fetch('index.php?option=com_emundus&controller=gallery&task=getattachments&campaign_id='+this.gallery.campaign_id)
          .then(response => response.json())
          .then(data => {
            Array.prototype.push.apply(this.image_attachments,Object.values(data.data));
          });
    },

    updateAttribute(attribute,value) {
      fetch('index.php?option=com_emundus&controller=gallery&task=updateattribute&gallery_id='+this.gallery.id+'&attribute='+attribute+'&value='+value)
          .then(response => response.json())
          .then(data => {
            console.log(data);
          });
    },

    updateLoading(state) {
      this.loading = Boolean(state);
    }
  },

  watch: {}
};
</script>

<style scoped>
.w--current{
  border: solid 1px #eeeeee;
  background: #eeeeee;
}

.w--current:hover{
  color: var(--em-coordinator-primary-color);
}
</style>
