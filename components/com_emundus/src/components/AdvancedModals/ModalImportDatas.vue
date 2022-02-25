<template>
    <!-- modalC -->
    <span :id="'modalImportDatas'">
    <modal
            :name="'modalImportDatas'"
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
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalImportDatas')">
              <em class="fas fa-times"></em>
            </button>
        </div>
        <div class="update-field-header">
            <h2 class="update-title-header">
                {{translations.ImportDatasTable}}
            </h2>
        </div>
    </div>

     <div class="modalC-content">
        <div v-if="!importCSV">
            <div class="form-group">
              <label>{{translations.Name}} :</label>
              <input v-model="form.label" type="text" maxlength="40" class="form__input field-general w-input" style="margin: 0" :class="{ 'is-invalid': errors.label}"/>
              <p v-if="errors.label" class="error">
                <span class="error">{{translations.NameRequired}}</span>
              </p>
            </div>
            <div class="form-group">
              <label>{{translations.Description}} :</label>
              <textarea v-model="form.desc" maxlength="150" class="form__input field-general w-input" style="margin: 0"/>
            </div>
            <div class="col-md-8 flex">
              <label class="require col-md-3">{{translations.Columns}} :</label>
              <button @click.prevent="add" class="add-option">+</button>
            </div>
            <div class="col-md-12">
              <div v-for="(sub_values, i) in form.db_columns" :key="i" class="dpflex">
                <input type="text" v-model="form.db_columns[i]" class="form__input field-general w-input db-values" :id="'columns_' + i" @keyup.enter="add"/>
                <button @click.prevent="leave(i)" class="remove-option">-</button>
              </div>
              <p v-if="errors.db_columns" class="error">
                <span class="error">{{translations.LeastOneColumnRequired}}</span>
              </p>
            </div>
          </div>
          <vue-csv-import v-model="csv" :map-fields="form.db_columns" :key="csv_comp" tableSelectClass="dropdown-toggle" tableClass="custom-table-csv" v-if="importCSV" ref="csvimport">
              <template slot="hasHeaders" slot-scope="{headers, toggle}">
                  <label style="display: none">
                    <input type="checkbox" id="hasHeaders" :value="headers" @change="toggle"> Headers?
                  </label>
              </template>

              <template slot="error">
                  {{translations.FileTypeInvalid}}
              </template>

              <template slot="thead">
                  <p style="margin-top: 2em;width: max-content;">{{ translations.CSVAssociate }}</p>
                  <tr>
                      <th id="my-columns">{{ translations.MyColumns }}</th>
                      <th id="csv-columns">{{ translations.CSVColumns }}</th>
                  </tr>
              </template>

              <template slot="next" slot-scope="{load}">
                  <button class="bouton-sauvergarder-et-continuer" style="float: left" @click.prevent="load">{{ translations.Load }}</button>
              </template>

              <template slot="submit" slot-scope="{submit}">
                  <button @click.prevent="submit">send!</button>
              </template>
        </vue-csv-import>
      </div>
      <div class="em-flex-row em-flex-space-between mb-1">
        <button type="button" class="bouton-sauvergarder-et-continuer w-retour" @click.prevent="goBack">
            {{ translations.Retour }}
        </button>
        <button type="button" class="bouton-sauvergarder-et-continuer"
           @click.prevent="saveDatas()">
          {{ translations.Continuer }}
        </button>
      </div>
    </modal>
  </span>
</template>

<script>
    import axios from "axios";
    import { VueCsvImport } from 'vue-csv-import';
    import _ from "lodash";
    const qs = require("qs");

    export default {
        name: "modalImportDatas",
        props: { },
        components: {
            VueCsvImport
        },
        data() {
            return {
                form: {
                    label: '',
                    desc: '',
                    db_columns: [],
                },
                errors: {
                    label: false,
                    db_columns: false
                },
                csv: null,
                csv_comp: 0,
                importCSV: false,
                translations: {
                    Name: "COM_EMUNDUS_ONBOARD_LASTNAME",
                    NameRequired: "COM_EMUNDUS_ONBOARD_LASTNAME_REQUIRED",
                    Columns: "COM_EMUNDUS_ONBOARD_COLUMNS",
                    Description: "COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION",
                    ImportDatasTable: "COM_EMUNDUS_ONBOARD_IMPORT_DATAS",
                    Retour: "COM_EMUNDUS_ONBOARD_ADD_RETOUR",
                    Continuer: "COM_EMUNDUS_ONBOARD_NEXT",
                    Load: "COM_EMUNDUS_ONBOARD_LOAD_FILE",
                    MyColumns: "COM_EMUNDUS_ONBOARD_MY_COLUMNS",
                    CSVColumns: "COM_EMUNDUS_ONBOARD_CSV_COLUMNS",
                    CSVAssociate: "COM_EMUNDUS_ONBOARD_CSV_ASSOCIATION",
                    LeastOneColumnRequired: "COM_EMUNDUS_ONBOARD_LEAST_ONE_COLUMN_REQUIRED",
                    FileTypeInvalid: "COM_EMUNDUS_ONBOARD_INVALID_FILE_TYPE",
                }
            };
        },
        methods: {
            goBack() {
                if(!this.importCSV){
                    this.$modal.hide('modalImportDatas');
                } else {
                    this.importCSV = false;
                }
            },

            // Triggers to add and delete values
            add: _.debounce(function() {
                let size = Object.keys(this.form.db_columns).length;
                this.$set(this.form.db_columns, size, '');
                let id = 'columns_' + size.toString();
                setTimeout(() => {
                    document.getElementById(id).focus();
                }, 100);
            },150),
            leave: function(index) {
                this.$delete(this.form.db_columns, index);
            },
            //

            // Ajax methods
            saveDatas() {
                this.errors = {
                    label: false,
                    db_columns: false,
                };
                if(this.form.label == ''){
                    this.errors.label = true;
                    return false;
                }

                if(this.form.db_columns.length == 0){
                    this.errors.db_columns = true;
                    return false;
                }

                if(!this.importCSV){
                    this.importCSV = true;
                    this.csv_comp += 1;
                } else {
                    axios({
                        method: "post",
                        url: "index.php?option=com_emundus&controller=settings&task=saveimporteddatas",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        data: qs.stringify({
                            form: this.form,
                            datas: this.csv,
                        })
                    }).then(() => {
                        this.$emit("updateDatabases");
                        this.$modal.hide('modalImportDatas');
                    });
                }
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

    .db-values{
        height: 35px;
        margin-bottom: 0;
        width: auto;
    }
</style>
