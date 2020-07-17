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
            @closed="beforeClose"
            @before-open="beforeOpen"
    >
      <div class="modalC-content">
        <div class="update-field-header">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalAddDatas')">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{CreateDatasTable}}
          </h2>
        </div>
        <div class="form-group">
          <label>{{Name}} :</label>
          <input v-model="form.label" type="text" maxlength="40" class="form__input field-general w-input" style="margin: 0" :class="{ 'is-invalid': errors.label}"/>
        </div>
        <div class="form-group">
          <label>{{Description}} :</label>
          <textarea v-model="form.desc" maxlength="150" class="form__input field-general w-input" style="margin: 0"/>
        </div>
        <div class="col-md-8 flex">
          <label class="require col-md-3">{{Values}} :</label>
          <button @click.prevent="add" class="add-option">+</button>
        </div>
        <div class="col-md-10">
          <div v-for="(sub_values, i) in form.db_values" :key="i" class="dpflex">
            <input type="text" v-model="form.db_values[i]" class="form__input field-general w-input" style="height: 35px" @keyup.enter="add"/>
            <button @click.prevent="leave(i)" class="remove-option">-</button>
          </div>
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <a class="bouton-sauvergarder-et-continuer-3"
           @click.prevent="saveDatas()">
          {{ Continuer }}
        </a>
        <a class="bouton-sauvergarder-et-continuer-3 w-retour"
           @click.prevent="$modal.hide('modalAddDatas')">
          {{Retour}}
        </a>
      </div>
    </modal>
  </span>
</template>

<script>
  import axios from "axios";
  import Swal from "sweetalert2";
  const qs = require("qs");

  export default {
    name: "modalUpdateLogo",
    props: { },
    components: {
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
        Name: Joomla.JText._("COM_EMUNDUS_ONBOARD_LASTNAME"),
        Values: Joomla.JText._("COM_EMUNDUS_ONBOARD_VALUES"),
        Description: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION"),
        CreateDatasTable: Joomla.JText._("COM_EMUNDUS_ONBOARD_CREATE_DATAS"),
        Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
        Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      };
    },
    methods: {
      beforeClose(event) {
      },
      beforeOpen(event) {
      },
    }
  };
</script>

<style scoped>
  .modalC-content {
    height: 100%;
    box-sizing: border-box;
    padding: 10px;
    font-size: 15px;
    overflow: auto;
  }
  .topright {
    font-size: 25px;
    float: right;
  }
  .btnCloseModal {
    background-color: inherit;
  }
  .update-field-header{
    margin-bottom: 1em;
  }

  .update-title-header{
    margin-top: 0;
    display: flex;
    align-items: center;
  }

  .flex {
    display: flex;
    align-items: center;
    margin-bottom: 1em;
    height: 30px;
  }
</style>
