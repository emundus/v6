<template>
  <div id="gallery-display">
    <div>
      <h2>Vignettes</h2>
      <p>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_INTRO') }}</p>
    </div>

    <div class="mt-2">
      <div class="flex mt-4 gap-8">
        <div class="w-2/4">
          <div class="mb-4 mt-2">
            <label>Titre</label>
            <select class="w-full"></select>
          </div>

          <div class="mb-4">
            <label>Sous-titre</label>
            <select class="w-full"></select>
          </div>

          <div class="mb-4">
            <label>Étiquettes</label>
            <select class="w-full"></select>
          </div>

          <div class="mb-4">
            <label>Description</label>
            <select class="w-full"></select>
          </div>

          <div class="mb-4">
            <label>Image</label>
            <select class="w-full"></select>
          </div>
        </div>

        <div class="em-repeat-card-no-padding em-pb-24 relative card-preview">
          <div class="fabrikImageBackground" style="background-image: url('/media/com_emundus/images/gallery/default_card.png')"></div>
          <div class="p-4">
            <h2 class="line-clamp-2 h-14">
              Titre de la vignette
            </h2>
            <div class="mb-3">
              <p class="em-caption" style="min-height: 15px">
                Sous-titre
              </p>
            </div>
            <div class="mb-3 tags" style="min-height: 30px">
              <ul>
                <li>Étiquette 1</li>
                <li>Étiquette 2</li>
              </ul>
            </div>
            <p class="mb-3 line-clamp-4 h-20">
              Lorem ipsum
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

  directives: { focus: {
      inserted: function (el) {
        el.focus()
      }
    }
  },

  props: {
    gallery: Number,
  },

  data: () => ({}),

  created() {},
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