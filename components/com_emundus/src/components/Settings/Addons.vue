<template>
  <div>
    <div class="flex flex-row flex-wrap">
      <div v-for="addon in addons" class="flex flex-col flex-wrap mr-3"
           :key="addon.id">
        <div class="cursor-pointer flex flex-col bg-white justify-center items-center rounded border border-neutral-300 p-6" name="tilebutton">
            <div class="mb-10 rounded flex justify-center items-center" :style="{ 'background-color': addon.color, 'width': '16em', 'height':'10em' }">
              <span class="material-icons-outlined !text-white"
                 :style="{'transform': 'scale(7)'}">{{ addon.icon }}</span>
            </div>
          <span class="font-medium">{{ translate(addon.label) }}</span>
        </div>
      </div>
    </div>
  </div>

</template>

<script>
export default {
  name: "Content",
  components: {},
  props: {
    json_source: {
      type: String,
      required: true,
    },
  },

  mixins: [],

  data() {
    return {
      addons: [
        {
          "label": "COM_EMUNDUS_GLOBAL_PARMAS_SECTIONS_SHARE_FILE",
          "published": false,
          "type": "Tile",
          "color": "#F04437",
          "icon": "share",
          "link": "",
          "helptext": ""
        },
        {
          "label": "COM_EMUNDUS_GLOBAL_PARMAS_SECTIONS_MAIL_GENERATION/PUBLISHING",
          "published": false,
          "type": "Tile",
          "color": "#D292EF",
          "icon": "mail",
          "link": "",
          "helptext": ""
        }
      ],
    }
  },
  created() {},
  mounted() {},
  methods: {},
  watch: {},
}
</script>

<style scoped>

</style>