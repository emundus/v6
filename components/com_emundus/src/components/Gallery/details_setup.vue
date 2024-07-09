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
      </div>

      <div>
        <div class="details-tabs mt-10 flex items-center mb-8">
          <div v-for="(tab,index) in gallery.tabs">
            <p :class="{ 'active': index == active_tab}" @click="active_tab = index">{{ tab.title }}</p>
          </div>
          <div>
            <p class="flex" :title="translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_ADD')" @click="addTab">
              <span class="material-icons-outlined mr-2">add</span>
              {{ translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_ADD') }}
            </p>
          </div>
        </div>

        <div>
          <span class="text-red-500 cursor-pointer" :title="translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_DELETE')" @click="deleteTab">{{ translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_DELETE') }}</span>
        </div>

        <div class="mt-4 mb-4">
          <label for="tab_title" class="w-max">{{ translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_NAME') }}</label>
          <input type="text" maxlength="255" style="width: 50%" id="tab_title" v-model="form.tabs[active_tab].title" @focusout="udpateTabTitle"/>
        </div>

        <div class="em-grid-2">
          <div>
            <h3>{{ translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_AVAILABLE_CONTENT') }}</h3>
            <div class="p-4 elements-block mt-2">
              <fieldset v-for="element in elements" class="mb-8">
                <h4 class="mb-6">{{ element.label }}</h4>
                <div class="pl-3">
                  <div v-for="elt in element.elements"
                       class="flex justify-between pb-2 mb-3 border-b border-neutral-400">
                    <h5>{{ elt.label }}</h5>
                    <span class="material-icons-outlined cursor-pointer" :title="translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_ADD_FIELD')" @click="addField(elt)">east</span>
                  </div>
                </div>
              </fieldset>
            </div>
          </div>
          <div>
            <h3>{{ translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_DISPLAY_CONTENT') }}</h3>
            <div class="px-4 py-8 elements-block mt-2 min-h-[100px]">
              <draggable
                  v-model="form.tabs[active_tab].fields"
                  group="fields-list"
                  class="draggables-list"
                  @end="onDragEnd"
                  handle=".handle"
              >
                <transition-group>
                  <div v-for="(field, index) in form.tabs[active_tab].fields" :key="'field_'+field.id"
                       class="flex justify-between items-center pb-2 mb-3 border-b border-neutral-400">
                    <div class="flex items-center">
                      <span class="material-icons-outlined handle em-grab mr-2">drag_indicator</span>
                      <h5>{{ field.label }}</h5>
                    </div>
                    <span class="material-icons-outlined cursor-pointer" :title="translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_REMOVE_FIELD')" @click="removeField(index,field)">west</span>
                  </div>
                </transition-group>
              </draggable>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";
import draggable from "vuedraggable";

export default {
  name: "details_setup",

  components: {draggable, Multiselect},

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
      tabs: [],
    },

    drag: false,

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

    this.gallery.tabs.forEach((tab) => {
      if (tab.fields && tab.fields.length > 0) {
        const fields = tab.fields.split(',');
        tab.fields = [];
        fields.forEach((field) => {
          this.elements.forEach((element) => {
            element.elements.forEach((elt) => {
              if (elt.fullname == field) {
                tab.fields.push(elt);
              }
            })
          })
        })
      } else {
        tab.fields = [];
      }

      this.form.tabs.push(tab);
    });

    this.attachments_update++;
    this.$emit('updateLoader');
  },
  methods: {
    addTab() {
      let new_gallery = {
        id: 0,
        title: 'Nouvel onglet',
        fields: '',
      };

      let formData = new FormData();
      formData.append('gallery_id', this.gallery.id);
      formData.append('title', 'Nouvel onglet');

      fetch('/index.php?option=com_emundus&controller=gallery&task=addtab', {
        method: 'POST',
        body: formData,
      })
          .then(response => response.json())
          .then(data => {
            if (data.status) {
              new_gallery.id = data.data;
              this.form.tabs.push(new_gallery);
              this.gallery.tabs.push(new_gallery);
            }
          });
    },

    deleteTab() {
      Swal.fire({
        title: this.translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_DELETE_TAB_TITLE')+' '+this.gallery.tabs[this.active_tab].title+' ?',
        text: this.translate('COM_EMUNDUS_GALLERY_DETAILS_TABS_DELETE_TAB_TEXT'),
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonText: this.translate("COM_EMUNDUS_ACTIONS_DELETE"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
      }).then((result) => {
        if (result.value) {
          let formData = new FormData();
          formData.append('tab_id', this.gallery.tabs[this.active_tab].id);

          fetch('/index.php?option=com_emundus&controller=gallery&task=deletetab', {
            method: 'POST',
            body: formData,
          })
              .then(response => response.json())
              .then(data => {
                this.gallery.tabs.splice(this.active_tab, 1);
                this.active_tab = 0;
              });
        }
      })
    },

    udpateTabTitle() {
      let formData = new FormData();
      formData.append('tab_id', this.gallery.tabs[this.active_tab].id);
      formData.append('title', this.form.tabs[this.active_tab].title);
      fetch('/index.php?option=com_emundus&controller=gallery&task=updatetabtitle', {
        method: 'POST',
        body: formData,
      })
          .then(response => response.json())
          .then(data => {
            console.log(data);
          });
    },

    addField(field) {
      this.form.tabs[this.active_tab].fields.push(field);

      let formData = new FormData();
      formData.append('tab_id', this.gallery.tabs[this.active_tab].id);
      formData.append('field', field.fullname);

      fetch('/index.php?option=com_emundus&controller=gallery&task=addfield', {
        method: 'POST',
        body: formData,
      })
          .then(response => response.json())
          .then(data => {
            console.log(data);
          });
    },

    removeField(index, field) {
      this.form.tabs[this.active_tab].fields.splice(index, 1);

      let formData = new FormData();
      formData.append('tab_id', this.gallery.tabs[this.active_tab].id);
      formData.append('field', field.fullname);

      fetch('/index.php?option=com_emundus&controller=gallery&task=removefield', {
        method: 'POST',
        body: formData,
      })
          .then(response => response.json())
          .then(data => {
            console.log(data);
          });
    },

    onDragEnd(e) {
      let formData = new FormData();
      formData.append('tab_id', this.gallery.tabs[this.active_tab].id);
      formData.append('fields', this.form.tabs[this.active_tab].fields.map((field) => field.fullname).join(','));

      fetch('/index.php?option=com_emundus&controller=gallery&task=updatefieldsorder', {
        method: 'POST',
        body: formData,
      })
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
.details-preview {
  transform: scale(0.7);
  transform-origin: top left;
  max-width: 60vw;
  position: relative;
  top: -50px;
  height: 230px;
}

.fabrikImageBackground {
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

.tags ul li {
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
  border-radius: calc(var(--em-form-br-block) / 2);
  background: #F0F0F0;
}

.elements-block {
  border-radius: var(--em-coordinator-br);
  background: var(--neutral-400);
}
</style>