<template>
  <div id="edit-gallery">
    <div class="flex items-center em-pointer" @click="redirectJRoute('index.php?option=com_emundus&view=gallery')">
      <span class="material-icons-outlined">arrow_back</span>
      <p class="ml-2">{{ translate('BACK') }}</p>
    </div>

    <div>
      <h1 class="mt-3">Modifier la catalogue </h1>
      <button class="mt-2 em-tertiary-button flex em-w-auto p-0 items-center gap-2" type="button">
        <span class="material-icons-outlined">visibility</span>
        Prévisualiser
      </button>
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
          v-if="selectedMenu === 0"
      ></display>
      <gallery-details
          v-if="selectedMenu === 1"
      ></gallery-details>
      <settings
          v-if="selectedMenu === 2"
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

  directives: { focus: {
      inserted: function (el) {
        el.focus()
      }
    }
  },

  props: {
    gallery: Number,
  },

  data: () => ({
    selectedMenu: 0,
    menus: [
      'COM_EMUNDUS_GALLERY_DISPLAY',
      'COM_EMUNDUS_GALLERY_DETAILS',
      'COM_EMUNDUS_GALLERY_SETTINGS'
    ],
  }),

  created() {},
  methods: {
    redirectJRoute(link) {
      window.location.href = link
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
