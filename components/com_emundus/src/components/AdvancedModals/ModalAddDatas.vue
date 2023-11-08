<template>
  <!-- modalC -->
  <span :id="'modalAddDatas'">
    <modal
        :name="'modalAddDatas'"
        height="auto"
        transition="nice-modal-fade"
        :min-width="200"
        :min-height="200"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
    >
      <div class="fixed-header-modal">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalAddDatas')">
              <em class="fas fa-times"></em>
            </button>
          </div>
          <div class="update-field-header">
            <h2 class="update-title-header">
               {{ CreateDatasTable }}
            </h2>
          </div>
        </div>
      <div class="modalC-content">
        <div class="form-group">
          <label>{{ Name }} :</label>
          <input v-model="form.label" type="text" maxlength="40" class="form__input field-general w-input"
                 style="margin: 0" :class="{ 'is-invalid': errors.label}"/>
        </div>
        <div class="form-group">
          <label>{{ Description }} :</label>
          <textarea v-model="form.desc" maxlength="150" class="form__input field-general w-input" style="margin: 0"/>
        </div>
        <div class="col-md-8 flex">
          <label class="require col-md-3">{{ Values }} :</label>
          <button @click.prevent="add" class="add-option">+</button>
        </div>
        <div class="col-md-12">
          <div v-for="(sub_values, i) in form.db_values" :key="i" class="dpflex">
            <div class="input-can-translate">
              <input type="text" v-model="form.db_values[i][actualLanguage]"
                     class="form__input field-general w-input db-values" :id="'values_fr_' + i" @keyup.enter="add"/>
              <button class="translate-icon" :class="{'translate-icon-selected': form.db_values[i].translate}"
                      v-if="manyLanguages !== '0'" type="button" :title="Translate"
                      @click="form.db_values[i].translate = !form.db_values[i].translate"></button>
            </div>
            <translation :label="form.db_values[i]" :actualLanguage="actualLanguage"
                         v-if="form.db_values[i].translate"></translation>
            <button @click.prevent="leave(i)" class="remove-option">-</button>
          </div>
        </div>
      </div>
      <div class="em-flex-row em-flex-space-between mb-1">
        <button
            type="button"
            class="bouton-sauvergarder-et-continuer w-retour"
            @click.prevent="$modal.hide('modalAddDatas')"
        >
          {{ Retour }}
        </button>
        <button type="button" class="bouton-sauvergarder-et-continuer"
                @click.prevent="saveDatas()">
          {{ Continuer }}
        </button>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
import _ from "lodash";

const qs = require("qs");

import Translation from "@/components/translation";

export default {
  name: "modalAddDatas",
  props: {
    actualLanguage: String,
    manyLanguages: Number,
  },
  components: {
    Translation
  },
  data() {
    return {
      form: {
        label: '',
        desc: '',
        db_values: [],
      },
      errors: {
        label: false,
      },
      Name: this.translate("COM_EMUNDUS_ONBOARD_LASTNAME"),
      Translate: this.translate("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
      Values: this.translate("COM_EMUNDUS_ONBOARD_VALUES"),
      Description: this.translate("COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION"),
      CreateDatasTable: this.translate("COM_EMUNDUS_ONBOARD_CREATE_DATAS"),
      Retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
    };
  },
  methods: {
    // Triggers to add and delete values
    add: _.debounce(function () {
      let size = Object.keys(this.form.db_values).length;
      this.$set(this.form.db_values, size, {fr: '', en: '', translate: false});
      let id = 'values_fr_' + size.toString();
      setTimeout(() => {
        document.getElementById(id).focus();
      }, 100);
    }, 150),
    leave: function (index) {
      this.$delete(this.form.db_values, index);
    },
    //

    // Ajax methods
    saveDatas() {
      this.errors = {
        label: false,
      };
      if (this.form.label == '') {
        this.errors.label = true;
        return false;
      }
      this.form.db_values.forEach((value) => {
        if (!value.translate) {
          value.en = value.fr;
        }
      });
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=settings&task=savedatas",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          form: this.form,
        })
      }).then(() => {
        this.$emit("updateDatabases");
        this.$modal.hide('modalAddDatas');
      });
    },
    //

  }
};
</script>

<style scoped>
.flex {
  display: flex;
  align-items: center;
  margin-bottom: 1em;
  height: 30px;
}

.db-values {
  height: 35px;
  margin-bottom: 0;
  width: auto;
}
</style>
