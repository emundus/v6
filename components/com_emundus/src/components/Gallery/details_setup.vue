<template>
  <div id="gallery-details">
    <div>
      <h2>{{ translate('COM_EMUNDUS_GALLERY_DETAILS_TITLE') }}</h2>
    </div>

    <div class="mt-2">
      <div class="flex mt-4 gap-8">
        <div class="w-2/4">
          <div class="mt-2 mb-4">
            <label>{{ translate('COM_EMUNDUS_GALLERY_DETAILS_FIELDS_BANNER') }}</label>
            <multiselect
                :key="attachments_update"
                v-if="image_attachments"
                v-model="form.banner"
                label="value"
                track-by="id"
                :options="image_attachments"
                :multiple="false"
                :searchable="true"
                :taggable="false"
                select-label=""
                selected-label=""
                deselect-label=""
                :close-on-select="true"
                :clear-on-select="false"
            ></multiselect>
          </div>

          <div class="mb-4">
            <label>{{ translate('COM_EMUNDUS_GALLERY_DETAILS_FIELDS_LOGO') }}</label>
            <multiselect
                :key="attachments_update"
                v-if="image_attachments"
                v-model="form.logo"
                label="value"
                track-by="id"
                :options="image_attachments"
                :multiple="false"
                :searchable="true"
                :taggable="false"
                select-label=""
                selected-label=""
                deselect-label=""
                :close-on-select="true"
                :clear-on-select="false"
            ></multiselect>
          </div>
        </div>

        <div class="details-preview">
          <div v-if="form.banner && form.banner.id != 0" class="fabrikImageBackground" style="background-image: url('/media/com_emundus/images/gallery/default_card.png')"></div>
          <div class="p-8" style="max-width: 50%">
            <h2 class="line-clamp-2 h-14">
              {{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TITLE') }}
            </h2>
            <div class="mb-3">
              Lorem ipsum dolor sit amet, consectetur adi elit, sed do eiusmod tempor incididunt ut labLorem ipsum dolor sitermina erts
            </div>
            <div class="mb-3 tags" style="min-height: 30px">
              <ul>
                <li>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TAG') }} 1</li>
                <li>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TAG') }} 2</li>
              </ul>
            </div>

            <div class="details-tabs mt-10 flex items-center mb-8">
              <div v-for="(tab,index) in gallery.tabs">
                <p :class="{ 'active': index == 0}">{{tab.title}}</p>
                <div class="mb-5 mt-3">
                  Lorem ipsum dolor sit amet, consectetur adi elit, sed do eiusmod tempor incididunt ut labLorem ipsum dolor sitermina erts
                </div>
              </div>
            </div>
          </div>

          <div class="voting-pop em-repeat-card" style="padding: unset">
            <div v-if="form.logo && form.logo.id != 0" class="fabrikImageBackground" style="background-image: url('/media/com_emundus/images/gallery/default_card.png')"></div>

            <div class="p-4 voting-details-block">
              <h2 class="line-clamp-2 h-14">
                {{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TITLE') }}
              </h2>
              <div class="mb-3">
                <p class="em-caption flex items-center" style="min-height: 15px">
                  <span class="material-icons-outlined mr-2">{{ gallery.subtitle_icon }}</span>
                  {{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_SUBTITLE') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div>
        <div class="details-tabs mt-10 flex items-center mb-8">
          <div v-for="(tab,index) in gallery.tabs">
            <p :class="{ 'active': index == active_tab}" contenteditable="true" onkeypress="return (this.innerText.length <= 20)" @keydown.enter.prevent @input="udpateTabTitle($event,tab)">{{tab.title}}</p>
          </div>
          <div>
            <p class="flex">
              <span class="material-icons-outlined mr-2">add</span>
              Ajouter un onglet
            </p>
          </div>
        </div>

        <div class="em-grid-2">
          <div>
            <h3>Contenu disponible</h3>
            <div></div>
          </div>
          <div>
            <h3>Contenu affiché</h3>
            <div></div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";

export default {
  name: "details_setup",

  components: {Multiselect},

  directives: {},

  props: {
    gallery: Object,
    elements: [],
    simple_fields: [],
    choices_fields: [],
    description_fields: [],
    image_attachments: [],
  },

  data: () => ({
    form: {
      banner: null,
      logo: null,
    },

    attachments_update: 0,
    active_tab: 0
  }),

  created() {
    this.$emit('updateLoader', true);

    this.image_attachments.forEach((attachment) => {
      if (attachment.id == this.gallery.logo) {
        this.form.logo = attachment;
      }

      if (attachment.id == this.gallery.banner) {
        this.form.banner = attachment;
      }
    });

    this.attachments_update++;
    this.$emit('updateLoader');
  },
  methods: {
    udpateTabTitle(e, tab) {
      if (this.timer) {
        clearTimeout(this.timer)
      }

      this.timer = setTimeout(() => {
        tab.title = e.target.innerText;

        let formData = new FormData();
        formData.append('tab_id', tab.id);
        formData.append('title', tab.title);
        fetch('index.php?option=com_emundus&controller=gallery&task=updatetabtitle', {
          method: 'POST',
          body: formData,
        })
            .then(response => response.json())
            .then(data => {
              console.log(data);
            });
      }, 1500);
    }
  },

  watch: {}
};
</script>

<style scoped>
.details-preview {
  transform: scale(0.7);
  transform-origin: top left;
  max-width: 60vw;
  position: relative;
  top: -50px;
  height: 230px;
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

.voting-details-group {
  max-width: 60vw;
}

.details-tabs {
  border-radius: 4px;
}

.details-tabs p {
  padding: 14px 28px;
  color: var(--em-primary-color);
  border-top: 1px solid #2E404F;
  border-bottom: 1px solid #2E404F;
  border-left: 1px solid #2E404F;
  cursor: pointer;
  width: fit-content;
}

.details-tabs p.active {
  background: #2E404F;
  color: white;
  font-weight: 600;
}

.details-tabs p:nth-child(1) {
  border-radius: 4px 0 0 4px;
}

.details-tabs p:last-of-type {
  border-radius: 0 4px 4px 0;
  border-right: 1px solid #2E404F;
}

.voting-pop {
  position: absolute;
  top: 25%;
  right: 2vw;
  width: 300px;
}

.voting-pop .voting-details-block {
  border-radius: calc(var(--em-form-br-block)/2);
  background: #F0F0F0;
}
</style>