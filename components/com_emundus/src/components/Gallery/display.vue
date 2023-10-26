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
            <label>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_SUBTITLE_ICON') }}</label>
            <multiselect
                v-if="subtitle_icons"
                v-model="form.subtitle_icon"
                label="label"
                track-by="code"
                :options="subtitle_icons"
                :multiple="false"
                :searchable="true"
                :taggable="false"
                select-label=""
                selected-label=""
                deselect-label=""
                :close-on-select="true"
                :clear-on-select="false"
            >
              <template slot="singleLabel" slot-scope="props">
                <div class="flex items-center gap-2">
                  <span class="material-icons-outlined">{{ props.option.code }}</span>
                  <span class="option__title">{{ translate(props.option.label) }}</span>
                </div>
              </template>
              <template slot="option" slot-scope="props">
                <div class="flex items-center gap-2">
                  <span class="material-icons-outlined">{{ props.option.code }}</span>
                  <span class="option__title">{{ translate(props.option.label) }}</span>
                </div>
              </template>
            </multiselect>
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
                :clear-on-select="false">
            </multiselect>
          </div>

          <div class="mb-4">
            <label>{{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_IMAGE') }}</label>
            <multiselect
                :key="attachments_update"
                v-if="image_attachments"
                v-model="form.image"
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

        <div class="em-repeat-card-no-padding em-pb-24 relative card-preview">
          <div v-if="form.image && form.image.id != 0" class="fabrikImageBackground" style="background-image: url('/media/com_emundus/images/gallery/default_card.png')"></div>
          <div class="p-4">
            <h2 class="line-clamp-2 h-14">
              {{ translate('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TITLE') }}
            </h2>
            <div class="mb-3">
              <p class="em-caption flex items-center" style="min-height: 15px">
                <span class="material-icons-outlined mr-2">{{ form.subtitle_icon.code }}</span>
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
    image_attachments: [
      {allowed_types: '',id:0,value:'Aucune image'}
    ],
    subtitle_icons: [
      {
        code: '',
        label: 'COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_SUBTITLE_ICON_NO_ICON',
      },
      {
        code: 'location_on',
        label: '',
      },
      {
        code: 'sell',
        label: '',
      },
      {
        code: 'lightbulb',
        label: '',
      }
    ],
    form: {
      title: '',
      subtitle: '',
      subtitle_icon: '',
      tags: '',
      resume: '',
      image: '',
    },

    attachments_update: 0,
  }),

  created() {
    if(this.gallery.subtitle_icon) {
      this.form.subtitle_icon = this.subtitle_icons.find(icon => {
        return icon.code == this.gallery.subtitle_icon;
      });
    }

    this.getElements();
    this.getAttachments();
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

    getAttachments() {
      fetch('index.php?option=com_emundus&controller=gallery&task=getattachments&campaign_id='+this.gallery.campaign_id)
          .then(response => response.json())
          .then(data => {
            Array.prototype.push.apply(this.image_attachments,Object.values(data.data));

            this.form.image = this.image_attachments.find(attachment => {
              return attachment.id == this.gallery.image;
            });

            this.attachments_update++;
          });
    },

    updateAttribute(attribute,value) {
      fetch('index.php?option=com_emundus&controller=gallery&task=updateattribute&gallery_id='+this.gallery.id+'&attribute='+attribute+'&value='+value)
          .then(response => response.json())
          .then(data => {
            console.log(data);
          });
    },
  },

  watch: {
    'form.title' : function(val,oldVal) {
      if(val != oldVal) {
        this.$emit('updateAttribute', 'title',val.fullname);
      }
    },

    'form.subtitle' : function(val,oldVal) {
      if(val != oldVal) {
        this.$emit('updateAttribute', 'subtitle',val.fullname);
      }
    },

    'form.subtitle_icon' : function(val,oldVal) {
      if(val != oldVal) {
        this.$emit('updateAttribute', 'subtitle_icon',val.code);
      }
    },

    'form.tags' : function(val,oldVal) {
      if(val != oldVal) {
        this.$emit('updateAttribute', 'tags',val.fullname);
      }
    },

    'form.resume' : function(val,oldVal) {
      if(val != oldVal) {
        this.$emit('updateAttribute', 'resume',val.fullname);
      }
    },

    'form.image': function(val,oldVal) {
      if (val != oldVal) {
        this.$emit('updateAttribute', 'image',val.id);
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
  height: fit-content;
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