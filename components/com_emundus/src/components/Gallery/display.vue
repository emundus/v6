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
            <multiselect
                v-if="simple_fields"
                v-model="form.title"
                label="label"
                track-by="fullname"
                :options="simple_fields"
                group-values="elements"
                group-label="label"
                :group-select="false"
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
            <label>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_SUBTITLE') }}</label>
            <multiselect
                v-if="simple_fields"
                v-model="form.subtitle"
                label="label"
                track-by="fullname"
                :options="simple_fields"
                group-values="elements"
                group-label="label"
                :group-select="false"
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
            <label>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TAGS') }}</label>
            <multiselect
                v-if="choices_fields"
                v-model="form.tags"
                label="label"
                track-by="fullname"
                :options="choices_fields"
                group-values="elements"
                group-label="label"
                :group-select="false"
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
            <label>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_RESUME') }}</label>
            <multiselect
                v-if="description_fields"
                v-model="form.resume"
                label="label"
                track-by="fullname"
                :options="description_fields"
                group-values="elements"
                group-label="label"
                :group-select="false"
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
import Multiselect from "vue-multiselect";

export default {
  name: "display",

  components: {Multiselect},

  directives: {},

  props: {
    gallery: Object,
  },

  data: () => ({
    elements: [],
    simple_fields: [],
    choices_fields: [],
    description_fields: [],
    form: {
      title: '',
      subtitle: '',
      tags: [],
      resume: '',
      image: '',
    },
  }),

  created() {
    this.getElements();
  },
  methods: {
    getElements() {
      fetch('index.php?option=com_emundus&controller=gallery&task=getelements&campaign_id='+this.gallery.campaign_id+'&list_id='+this.gallery.list_id)
          .then(response => response.json())
          .then(data => {
            this.elements = data.data.elements;
            this.simple_fields = Object.values(data.data.simple_fields);
            this.choices_fields = data.data.choices_fields;
            this.description_fields = data.data.description_fields;

            this.elements.forEach((element) => {
              element.elements.forEach((field) => {
                if(field.fullname === this.gallery.title) {
                  this.form.title = field;
                }

                if(field.fullname === this.gallery.subtitle) {
                  this.form.subtitle = field;
                }

                if(field.fullname === this.gallery.tags) {
                  this.form.tags = field;
                }

                if(field.fullname === this.gallery.resume) {
                  this.form.resume = field;
                }
              });
            });
          });
    },

    updateAttribute(attribute,value) {
      fetch('index.php?option=com_emundus&controller=gallery&task=updateattribute&gallery_id='+this.gallery.id+'&attribute='+attribute+'&value='+value)
          .then(response => response.json())
          .then(data => {
            console.log(data);
          });
    }
  },

  watch: {
    'form.title' : function(val,oldVal) {
      //TODO: Update card title (do not forget joins on list) -> Check if joins already exist and if all joins are used
      if(oldVal !== '') {
        this.updateAttribute('title', val.fullname);
      }
    }
  }
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