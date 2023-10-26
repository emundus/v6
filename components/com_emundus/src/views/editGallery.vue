<template>
  <div id="edit-gallery">
    <div class="flex items-center em-pointer w-min" @click="redirectJRoute('index.php?option=com_emundus&view=gallery')">
      <span class="material-icons-outlined">arrow_back</span>
      <p class="ml-2">{{ translate('BACK') }}</p>
    </div>

    <div>
      <h1 class="mt-3">{{ translate('COM_EMUNDUS_ONBOARD_EDIT_GALLERY') }}</h1>
      <a class="mt-2 em-tertiary-button flex em-w-auto p-0 items-center justify-start gap-2 hover:no-underline" target="_blank" :href="'/index.php?option=com_fabrik&view=list&listid='+gallery.list_id">
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
          v-if="selectedMenu === 0 && gallery"
          :gallery="gallery"
          @updateAttribute="updateAttribute"
      ></display>
      <gallery-details
          v-if="selectedMenu === 1 && gallery"
      ></gallery-details>
      <settings
          v-if="selectedMenu === 2 && gallery"
          :gallery="gallery"
          @updateAttribute="updateAttribute"
      ></settings>
    </transition>
  </div>
</template>

<script>
import Swal from "sweetalert2";

import Display from "@/components/Gallery/display.vue";
import Settings from "@/components/Gallery/settings.vue";
import galleryDetails from "@/components/Gallery/details.vue";

/** SERVICES **/

const qs = require("qs");

export default {
  name: "editGallery",

  components: {galleryDetails, Settings, Display},

  directives: {},

  props: {},

  data: () => ({
    selectedMenu: 0,
    menus: [
      'COM_EMUNDUS_GALLERY_DISPLAY',
      'COM_EMUNDUS_GALLERY_DETAILS',
      'COM_EMUNDUS_GALLERY_SETTINGS'
    ],

    gallery: null,
  }),

  created() {
    let gid = this.$store.getters['global/datas'].gallery.value;
    fetch('index.php?option=com_emundus&controller=gallery&task=getgallery&id='+gid)
        .then(response => response.json())
        .then(data => {
          this.gallery = data.data;
        })
        .catch((error) => {
          console.error('Error:', error);
        });
  },
  methods: {
    redirectJRoute(link) {
      window.location.href = link
    },

    updateAttribute(attribute,value) {
      console.log(attribute);
      console.log(value);
      fetch('index.php?option=com_emundus&controller=gallery&task=updateattribute&gallery_id='+this.gallery.id+'&attribute='+attribute+'&value='+value)
          .then(response => response.json())
          .then(data => {
            console.log(data);
          });
    },
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
