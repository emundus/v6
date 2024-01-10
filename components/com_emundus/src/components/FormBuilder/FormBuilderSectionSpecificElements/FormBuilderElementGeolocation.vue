<template>
  <div id="form-builder-geolocation">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <div v-if="loading" class="em-loader"></div>

    <div v-else :id="'map_container_'+element.id" class="fabrikSubElementContainer fabrikEmundusGeolocalisation">
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../../services/formbuilder';
import Editor from "../../editor";

export default {
  props: {
    element: {
      type: Object,
      required: true
    },
    type: {
      type: String,
      required: true
    }
  },
  components: {
    Editor
  },
  data() {
    return {
      loading: false,

      mapContainer: null,
    };
  },
  mounted () {
    if (typeof L !== 'undefined' && L !== null) {
      this.mapContainer = L.map('map_container_'+this.$props.element.id).setView(
          ['48.85341', '2.3488'], 13
      );

      L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
      }).addTo(this.mapContainer);

      this.mapContainer.dragging.disable();
      this.mapContainer.touchZoom.disable();
      this.mapContainer.doubleClickZoom.disable();
      this.mapContainer.scrollWheelZoom.disable();
      this.mapContainer.boxZoom.disable();
      this.mapContainer.keyboard.disable();
      if (this.mapContainer.tap) this.mapContainer.tap.disable();
    }
  },
  methods: {
  },
  watch: {}
}
</script>

<style lang="scss">
#form-builder-geolocation {
  .fabrikEmundusGeolocalisation {
    height: 150px;
  }
}
</style>
