<template>
  <div class="row">

    <div class="col-md-12">

      <div class="tabs">

        <div class="tab" v-for="(database,index) in databases">
          <input type="radio" :id="'database'+database.label" @click="getDatas(database.database_name,index)" name="rd">
          <label class="tab-label" :for="'database'+database.label" @click="getDatas(database.database_name,index)">{{database.label}}</label>
          <div class="tab-content">
            <div class="tab-content-details">
            <button @click="addColum()">Ajouter un colonne</button>
            <table class="table table-bordered table-striped table-responsive">
              <thead>
              <tr>
                <th scope="col" v-for="(data,i) in datas.columns" :id="'column_' + data">{{data}}</th>
                <th scope="col" v-for="(data,i) in newColumns" :id="'column_new_th_' + i">

                  <span>{{data}}</span>
                  <input v-model="newColumns[i]"  @keyup.enter="saveColumn(newColumns[i],i)" class="form__input field-general w-input link" type="text" :id="'column_new_'+ i"  v-show="addingNewColumn" placeholder="Saisir le nom de la colonne"/>
                 <!-- <a @click="saveColumn(newColumns[i],i)" v-show="addingNewColumn" class="d-flex actions-update-label link">
                    <em class="fas fa-check" data-toggle="tooltip" data-placement="top"></em>
                  </a>-->
                </th>
                <!--<th scope="col">First</th>
                <th scope="col">Last</th>
                <th scope="col">Handle</th>-->
              </tr>
              </thead>
              <tbody>
              <tr v-for="(data, i) in datas.datas" >
                <td  v-for="(value,j) in data" style="cursor: pointer;"  title="Clicker pour modifier" v-if="value != null">
                  <span @click="enableDataValueInput(value,i,j)"  v-show="indexHighlight != i+'_'+j+'_'+value" style="cursor: pointer;" title="Clicker pour modifier">{{value}}</span>
                  <input v-model="data_value" class="form__input field-general w-input link" type="text" @keyup.enter="updateDataValue(value,i,j)" :id="'data_value_' +value+'_'+ i" v-show="clickUpdatingLabel && indexHighlight == i+'_'+j+'_'+value"/>

                  <a @click="updateDataValue(value,i,j)" v-show="clickUpdatingLabel && indexHighlight == i+'_'+j+'_'+value" class="d-flex actions-update-label link">
                    <em class="fas fa-check" data-toggle="tooltip" data-placement="top"></em>
                  </a>
                </td>
                <td  v-for="(value,j) in data" style="cursor: pointer;"  title="Clicker pour modifier" v-if="value == null">
                  <span @click="enableDataValueInput(value,i,j)"  v-show="indexHighlight != i+'_'+j+'_'+value" style="cursor: pointer;" title="Clicker pour modifier">Cliquer pour ajouter une valeur</span>
                  <input v-model="data_value" class="form__input field-general w-input link" type="text" @keyup.enter="updateDataValue(value,i,j)" :id="'data_value_' +value+'_'+ i" v-show="clickUpdatingLabel && indexHighlight == i+'_'+j+'_'+value"/>

                  <a @click="updateDataValue(value,i,j)" v-show="clickUpdatingLabel && indexHighlight == i+'_'+j+'_'+value" class="d-flex actions-update-label link">
                    <em class="fas fa-check" data-toggle="tooltip" data-placement="top"></em>
                  </a>
                </td>
                <td v-for="(data,i) in newColumns"></td>
               <!--<td>Otto</td>
                <td>@mdo</td>-->
              </tr>
              <!--<tr>

                <td>Jacob</td>
                <td>Thornton</td>
                <td>@fat</td>
              </tr>-->
              </tbody>
            </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="loading-form" v-if="loading">
      <Ring-Loader :color="'#12db42'" />
    </div>
  </div>
</template>

<script>
import actions from "./action_menu";
import moment from "moment";
import axios from "axios";
import qs from "qs";
import {list} from "../../store";

export default {
  name: "referentielItem",
  components: {actions},
  props: {
    data: Object,
    selectItem: Function,
    actions: Object,
    databases:Object,
  },
  data() {
    return {
      selectedData: [],
      newColumns:[],
      datas: {
        columns: [],
        datas: []
      },
      indexOpen: -1,
      indexHighlight: "",
      loading: false,
      clickUpdatingLabel:false,
      new_columnname_value:"",
      addingNewColumn: false,
      data_value:"",
      current_referentiel_db_name:"",
      publishedTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
      unpublishedTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
      passeeTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
      Modify: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODIFY"),
      Visualize: Joomla.JText._("COM_EMUNDUS_ONBOARD_VISUALIZE"),
      From: Joomla.JText._("COM_EMUNDUS_ONBOARD_FROM"),
      To: Joomla.JText._("COM_EMUNDUS_ONBOARD_TO"),
      Since: Joomla.JText._("COM_EMUNDUS_ONBOARD_SINCE"),
      AdvancedSettings: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADVANCED_SETTINGS"),
      Program: Joomla.JText._("COM_EMUNDUS_ONBOARD_DOSSIERS_PROGRAM"),
      Files: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILES"),
      File: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILE")
    };
  },

  methods: {
    updateLoading(value) {
      this.$emit('updateLoading', value);
    },
    getDatas(db_name,index){
      if(index == this.indexOpen){
        console.log(index);
        this.indexOpen = -1;
        console.log(this.indexOpen);
      } else {
        this.loading = true;
        console.log(index);
        this.indexOpen = index;
        console.log(this.indexOpen);
        this.datas = {
          columns: [],
          datas: []
        };
        axios({
          method: "get",
          url: "index.php?option=com_emundus_onboard&controller=settings&task=getdatasfromtable",
          params: {
            db: db_name,
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
          this.loading = false;
          this.datas.datas = response.data.data;
          this.datas.columns = Object.keys(this.datas.datas[0]);
          console.log(this.datas.columns[0]);
          this.current_referentiel_db_name=db_name;
          console.log(this.datas.columns);
        });
      }
    },
    updateDataValue(value,i,j){
      console.log(j)
      let index= this.datas.columns.indexOf(j);
      console.log(index);
      console.log(((this.datas.datas)[i]));
      console.log(this.datas.datas[i]);
      console.log(((this.datas.datas)[i])[j]);
      console.log(this.datas.datas[i][j]);

      this.datas.datas[i][j]=this.data_value;
      this.indexHighlight="";
      this.clickUpdatingLabel = false;
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=settings&task=updateDataColumnValue",
        params: {
          db: this.current_referentiel_db_name,
          column: j,
          value: this.data_value,
          primary_key_column: this.datas.columns[0],
          primary_key_column_value:this.datas.datas[i][this.datas.columns[0]],
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(resp=>{
        console.log(resp);
        console.log("hello succesfull done");
      })
      console.log(value);
    },
    enableDataValueInput(value,i,j){
      console.log(value);
      console.log(j);

      this.clickUpdatingLabel = true;
      this.indexHighlight=i+'_'+j+'_'+value;

      this.data_value=value;
      console.log(this.indexHighlight);
      setTimeout(() => {
        document.getElementById('data_value_'+value+'_'+ i).focus();
        console.log("focus now");
      }, 100);
    },
    addColum(){
      this.addingNewColumn=true;
      //this.new_columnname_value="";
      this.newColumns.push("");
      let last_index=this.newColumns.length-1;
      setTimeout(() => {
        document.getElementById('column_new_'+last_index).focus();
        console.log("focus now");
      }, 100);
    },

    saveColumn(column_name, i) {
      // eslint-disable-next-line camelcase
      console.log(column_name);
      let new_column_name="";
      if(column_name==""){
        new_column_name="new_column_"+i;

      } else{
        new_column_name = column_name.replace(/ /g, '_');
      }
      if(this.datas.columns.indexOf(new_column_name)==-1){
        this.datas.columns.push(new_column_name);

        this.datas.datas.forEach( el => {
          el[new_column_name] = null;
        });
      }else{
        console.log("already exist");
      }

      //suppresion du table temporaire des new columns
      this.newColumns.splice(i, 1);


    },
    validateFilters() {
      this.$emit('validateFilters');
    },

    moment(date) {
      return moment(date);
    },

    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
        params: {
          link: link,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        window.location.href = window.location.pathname + response.data.data;
      });
    }
  },

  computed: {
    isPublished() {
      return (
          this.data.published == 1 &&
          moment(this.data.start_date) <= moment() &&
          (moment(this.data.end_date) >= moment() ||
              this.data.end_date == null ||
              this.data.end_date == "0000-00-00 00:00:00")
      );
    },

    isFinish() {
      return moment(this.data.end_date) <= moment();
    },

    isActive() {
      return list.getters.isSelected(this.data.id);
    },
  }
}
</script>

<style lang="scss" scoped>
$midnight: white;
$clouds: white;
// General

h1 {
  margin: 0;
  line-height: 2;
  text-align: center;
}

h2 {
  margin: 0 0 0.5em;
  font-weight: normal;
}

input[type="radio"] {
  position: absolute;
  opacity: 0;
  z-index: -1;
}
.link{
  display:inline-block;
}
input[type="text"]{
  display:inline;
  widtth: 100px;
}
a {
  display: inline;
}
// Layout
.row {
  display: flex;

  .col {
    flex: 1;

    &:last-child {
      margin-left: 1em;
    }
  }
}

/* Accordion styles */
.tabs {
  border-bottom: 1px solid back;
  overflow: hidden;
}
.tab-content-details{
  height: auto;
}
.tab {
  width: 100%;
  color: white;
  overflow: hidden;
  border-bottom: 1px solid black;

  &-label {
    display: flex;
    /*justify-content: flex-start;*/
    padding: 1em;
    background: white;
    font-weight: bold;
    cursor: pointer;
    /* Icon */
    &:hover {
      background: white;
    }

    &::before {
      content: "\276F";
      width: 1em;
      height: 1em;
      text-align: center;
      transition: all 0.35s;
    }
  }

  &-content {
    max-height: 0;
    padding: 0 1em;
    color: black;
    background: white;
    transition: all 0.35s;
    border-top: 1px solid black;
    border-left: 1px solid black;
    border-right: 1px solid black;
  }


  &-close {
    display: flex;
    justify-content: flex-end;
    padding: 1em;
    font-size: 0.75em;
    background: $midnight;
    cursor: pointer;

    &:hover {
      background: $midnight;
    }
  }
}

/* :checked*/
input:checked {
  + .tab-label {
    background: $midnight;

    &::before {
      transform: rotate(90deg);
    }
  }

  ~ .tab-content {
    max-height: 100vh;
    padding: 1em;
  }
}

</style>
