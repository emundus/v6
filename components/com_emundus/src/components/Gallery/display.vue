<template>
  <div id="gallery-display">
    <div>
      <h2>{{ translate('COM_EMUNDUS_GALLERY_VIGNETTES') }}</h2>
      <p>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_INTRO') }}</p>
    </div>

    <div class="mt-2">
      <div class="flex mt-4 gap-8">
        <div class="w-2/4">
          <div class="mb-4 mt-2">
            <label>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TITLE') }}</label>
            <select class="w-full"></select>
          </div>

          <div class="mb-4">
            <label>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_SUBTITLE') }}</label>
            <select class="w-full"></select>
          </div>

          <div class="mb-4">
            <label>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TAGS') }}</label>
            <select class="w-full"></select>
          </div>

          <div class="mb-4">
            <label>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_RESUME') }}</label>
            <select class="w-full"></select>
          </div>

          <div class="mb-4">
            <label>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_IMAGE') }}</label>
            <select class="w-full"></select>
          </div>
        </div>

        <div class="em-repeat-card-no-padding em-pb-24 relative card-preview">
          <div class="fabrikImageBackground" style="background-image: url('/media/com_emundus/images/gallery/default_card.png')"></div>
          <div class="p-4">
            <h2 class="line-clamp-2 h-14">
              {{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TITLE') }}
            </h2>
            <div class="mb-3">
              <p class="em-caption" style="min-height: 15px">
                {{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_SUBTITLE') }}
              </p>
            </div>
            <div class="mb-3 tags" style="min-height: 30px">
              <ul>
                <li>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TAG') }} 1</li>
                <li>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TAG') }} 2</li>
              </ul>
            </div>
            <p class="mb-3 line-clamp-4 h-20">
              Lorem ipsum dolor sit amet, consectetur adi elit, sed do eiusmod tempor incididunt ut labLorem ipsum dolor sitermina erts
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";

export default {
  name: "display",

  components: {},

  directives: {},

  props: {
    gallery: Object,
  },

  data: () => ({
    elements: [],
  }),

  created() {
    // get elements from gallery campaign_id attribute
    let campaign_id = this.$props.gallery.campaign_id;
    fetch('index.php?option=com_emundus&controller=gallery&task=getelements&campaign_id='+campaign_id)
        .then(response => response.json())
        .then(data => {
          this.elements = data;
          console.log(this.elements);
        });
  },
  methods: {},

  watch: {}
};
</script>

<style scoped>
.card-preview {
  width: 400px;
  transform: scale(0.7);
  transform-origin: top left;
}
.fabrikImageBackground{
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  height: 192px;
}

.tags ul {
  list-style-type: none;
  display: flex;
  gap: 8px;
  align-items: center;
}

.tags ul li{
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 14px;
  background: #F0F0F0;
}
</style>