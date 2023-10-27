<template>
  <div id="gallery-settings">
    <div>
      <h2>Paramètres</h2>
    </div>

    <div class="mt-2">
      <div class="mb-4 mt-2">
        <label>{{ translate('COM_EMUNDUS_GALLERY_SETTINGS_TITLE') }}</label>
        <input v-model="form.label" type="text" maxlength="255" class="w-full" />
      </div>

      <div class="mb-4">
        <label>{{ translate('COM_EMUNDUS_GALLERY_SETTINGS_INTRO') }}</label>
        <textarea v-model="form.introduction" class="w-full" rows="5"></textarea>
      </div>

      <div class="mb-4">
        <label>{{ translate('COM_EMUNDUS_GALLERY_SETTINGS_STATUS') }}</label>
        <select v-model="form.status">
          <option v-for="status in status" :value="status.step">{{ status.value }}</option>
        </select>
      </div>

      <div class="mb-4">
        <label for="enable_vote">{{ translate('COM_EMUNDUS_GALLERY_SETTINGS_ENABLE_VOTE') }}</label>
        <div class="em-toggle">
          <input type="checkbox"
                 true-value="1"
                 false-value="0"
                 class="em-toggle-check"
                 id="enable_vote"
                 name="is_voting"
                 v-model="form.is_voting"
          />
          <strong class="b em-toggle-switch"></strong>
          <strong class="b em-toggle-track"></strong>
        </div>
      </div>

      <transition name="slide-down">
        <div class="mb-4" v-if="form.is_voting == 1">
          <label>{{ translate('COM_EMUNDUS_GALLERY_SETTINGS_MAX_VOTE') }}</label>
          <select v-model="form.max">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
          </select>
        </div>
      </transition>

      <transition name="slide-down">
        <div class="mb-4" v-if="form.is_voting == 1">
          <label>{{ translate('COM_EMUNDUS_GALLERY_SETTINGS_VOTING_ACCESS') }}</label>
          <select v-model="form.voting_access">
            <option value="1">{{ translate('COM_EMUNDUS_GALLERY_SETTINGS_VOTING_ACCESS_PUBLIC') }}</option>
            <option value="2">{{ translate('COM_EMUNDUS_GALLERY_SETTINGS_VOTING_ACCESS_REGISTERED') }}</option>
          </select>
        </div>
      </transition>

      <div class="em-grid-2">
        <transition name="slide-down">
          <div class="mb-4" v-if="form.is_voting == 1">
            <label>{{ translate('COM_EMUNDUS_GALLERY_SETTINGS_START_DATE') }}</label>
            <datetime
                v-model="form.start_date"
                id="startDate"
                type="datetime"
                class="w-full"
                format=""
                :placeholder="translate('COM_EMUNDUS_GALLERY_SETTINGS_START_DATE')"
                :input-id="'start_date'"
                :phrases="{ok: translate('COM_EMUNDUS_ONBOARD_OK'), cancel: translate('COM_EMUNDUS_ONBOARD_CANCEL')}"
            ></datetime>
          </div>
        </transition>

        <transition name="slide-down">
          <div class="mb-4" v-if="form.is_voting == 1">
            <label>{{ translate('COM_EMUNDUS_GALLERY_SETTINGS_END_DATE') }}</label>
            <datetime
                v-model="form.end_date"
                id="endDate"
                type="datetime"
                class="w-full"
                format=""
                :placeholder="translate('COM_EMUNDUS_GALLERY_SETTINGS_END_DATE')"
                :input-id="'end_date'"
                :min-datetime="minDate"
                :phrases="{ok: translate('COM_EMUNDUS_ONBOARD_OK'), cancel: translate('COM_EMUNDUS_ONBOARD_CANCEL')}"
            ></datetime>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";
import { Datetime } from "vue-datetime";
import {DateTime as LuxonDateTime} from "luxon";

export default {
  name: "settings",

  components: {
    Datetime
  },

  directives: {},

  props: {
    gallery: Object,
  },

  data: () => ({
    status: [],
    minDate: '',
    form: {
      label: "",
      introduction: "",
      is_voting: 0,
      max: 0,
      voting_access: 0,
      start_date: "",
      end_date: "",
      status: '1',
    }
  }),

  created() {
    this.$emit('updateLoader',true);
    this.getStatus();

    this.form.label = this.gallery.label;
    this.form.introduction = this.gallery.introduction;
    this.form.is_voting = this.gallery.is_voting;
    this.form.max = this.gallery.max;
    this.form.voting_access = this.gallery.voting_access;
    this.form.start_date = LuxonDateTime.fromSQL(this.gallery.start_date).toISO();
    this.form.end_date = LuxonDateTime.fromSQL(this.gallery.end_date).toISO();
    this.form.status = this.gallery.status;
  },
  methods: {
    getStatus() {
      fetch('index.php?option=com_emundus&controller=settings&task=getstatus')
          .then(response => response.json())
          .then(data => {
            this.status = data.data;

            this.$emit('updateLoader');
          });
    },
    updateGalleryList(attribute,value) {
      fetch('index.php?option=com_emundus&controller=gallery&task=updategallerylist&list_id='+this.gallery.list_id+'&attribute='+attribute+'&value='+value)
          .then(response => response.json())
          .then(data => {
            console.log(data);
          });
    },
    updateStatus(value) {
      fetch('index.php?option=com_emundus&controller=gallery&task=editprefilter&list_id='+this.gallery.list_id+'&value='+value)
          .then(response => response.json())
          .then(data => {
            console.log(data);
          });
    }
  },

  watch: {
    'form.label': function(val, oldVal) {
      if (oldVal !== '' && val != oldVal) {
        if (this.timer) {
          clearTimeout(this.timer)
        }

        this.timer = setTimeout(() => {
          this.updateGalleryList('label',val)
        }, 1500);
      }
    },
    'form.introduction': function(val, oldVal) {
      if (oldVal !== '' && val != oldVal) {
        if (this.timer) {
          clearTimeout(this.timer)
        }

        this.timer = setTimeout(() => {
          this.updateGalleryList('introduction',val)
        }, 1500);
      }
    },
    'form.is_voting': function(val, oldVal) {
      if (oldVal !== 0 && val != oldVal) {
        this.$emit('updateAttribute', 'is_voting',val);
      }
    },
    'form.max': function(val, oldVal) {
      if (oldVal !== 0 && val != oldVal) {
        this.$emit('updateAttribute', 'max',val);
      }
    },
    'form.voting_access': function(val, oldVal) {
      if (oldVal !== 0 && val != oldVal) {
        this.$emit('updateAttribute', 'voting_access',val);
      }
    },
    'form.start_date': function (val, oldVal) {
      if (val != oldVal) {
        this.minDate = LuxonDateTime.fromISO(val).plus({days: 1}).toISO();
        if (this.form.end_date == "") {
          this.form.end_date = LuxonDateTime.fromISO(val).plus({days: 1}).toISO();
        }

        this.$emit('updateAttribute', 'start_date',val);
      }
    },
    'form.end_date': function(val, oldVal) {
      if (val != oldVal) {
        this.$emit('updateAttribute', 'end_date',val);
      }
    },
    'form.status': function(val, oldVal) {
      if (val != oldVal) {
        this.updateStatus(val);
      }
    },
  }
};
</script>

<style scoped>
</style>