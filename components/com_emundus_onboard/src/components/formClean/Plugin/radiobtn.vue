<template>
  <div id="radiobtnF">
    <div class="row rowmodal">
      <div class="form-group">
        <label>{{helptext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.rollover" />
      </div>
      <div class="d-flex mb-1">
        <label class="require col-md-3">{{suboptions}} :</label>
      </div>
      <div class="col-md-10">
        <draggable
                v-model="arraySubValues"
                @end="needtoemit()"
                handle=".handle"
                style="padding-bottom: 2em">
          <div v-for="(sub_values, i) in arraySubValues" :key="i" class="d-flex mb-1">
            <span class="icon-handle">
              <em class="fas fa-grip-vertical handle"></em>
            </span>
            <input type="text" v-model="arraySubValues[i]" @change="needtoemit()" class="form__input field-general w-input mb-0" style="height: 35px" :id="'suboption_' + i" @keyup.enter="add"/>
            <button @click.prevent="leave(i)" type="button" class="remove-option">-</button>
          </div>
        </draggable>
        <button @click.prevent="add" type="button" class="bouton-sauvergarder-et-continuer-3 button-add-option" style="margin-bottom: 2em">{{AddOption}}</button>
      </div>
      <div class="form-group mb-2" >
        <label>{{ SelectDefault }}</label>
        <select id="select_type" class="dropdown-toggle" v-model="choiceOfDefaultValue"
                @change="onChangeChoiceOfDefaultValue($event)">
          <option :value=-1></option>
          <option v-for="(default_value, index) in arraySubValues" :key="index" :value="index">
            {{default_value}}
          </option>
        </select>
      </div>
  </div>
  </div>
</template>

<script>
import _ from "lodash";
import axios from "axios";
import draggable from "vuedraggable";
const qs = require("qs");

export default {
  name: "radiobtnF",
  components: {
    draggable
  },
  props: {element: Object},
  data() {
    return {
      arraySubValues: [],
      checked_initial_value: null,
      choiceOfDefaultValue: -1,
      helptext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT"),
      suboptions: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_OPTIONS"),
      AddOption: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADD_OPTIONS"),
      SelectDefault: Joomla.JText._("COM_EMUNDUS_ONBOARD_DEFAULT_SELECT"),
    };
  },
  methods: {
    add: _.debounce(function () {
      let size = Object.keys(this.arraySubValues).length;
      this.$set(this.arraySubValues, size, "");
      this.needtoemit();
      let id = 'suboption_' + size.toString();
      setTimeout(() => {
        document.getElementById(id).focus();
      }, 100);
    }, 150),
    leave: function (index) {
      this.$delete(this.arraySubValues, index);
      this.needtoemit();
    },
    initialised: function () {
      if (this.element.params.sub_options.sub_initial_selection.length == 0 && this.element.params.sub_options.sub_initial_selection[0] == "") {
        this.choiceOfDefaultValue = -1;
      } else {
        this.choiceOfDefaultValue = 0;
      }
      if (typeof this.element.params.sub_options !== 'undefined') {
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getJTEXTA",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            toJTEXT: this.element.params.sub_options.sub_labels
          }),
        }).then(response => {
          Object.values(response.data).forEach(rep => {
            this.arraySubValues.push(rep);
          });
          this.needtoemit();
        }).catch(e => {
          console.log(e);
        });
      } else {
        this.element.params.sub_options = {
          'sub_values': [],
          'sub_labels': [],
        };
        this.arraySubValues = this.element.params.sub_options.sub_labels;
      }
    },
    needtoemit: _.debounce(function () {
      this.$emit("subOptions", this.arraySubValues);
    }),
    onChangeChoiceOfDefaultValue(event) {
      if (event.target.value != -1) {
        this.setDefaultSubOption(event.target.value);
        this.choiceOfDefaultValue = 0;
        this.checked_initial_value = this.arraySubValues[0];
      } else {
        this.checked_initial_value = '';
      }
    },
    // this function is used to set the choice of the default option
    // option of dropdown move the option from his index to the first index of the subOption Arrays
    setDefaultSubOption(oldIndex) {
      const newIndex = 0;
      if (newIndex >= this.arraySubValues.length) {
        let k = newIndex - this.arraySubValues.length + 1;
        // eslint-disable-next-line no-plusplus
        while (k--) {
          this.arraySubValues.push(undefined);
        }
      }
      this.arraySubValues.splice(newIndex, 0, this.arraySubValues.splice(oldIndex, 1)[0]);

      this.needtoemit();
    },
  },
  created: function () {
    this.initialised();
  },
  watch:{
    checked_initial_value: function (value) {
      this.element.params.sub_options.sub_initial_selection[0] = value;
    },
  },

};
</script>
<style scoped>
  .flex {
    display: flex;
    align-items: center;
    margin-bottom: 1em;
    height: 30px;
  }
  .rowmodal {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
  }
  #radiobtnF{
    padding: 10px 0;
  }
.icon-handle{
  color: #cecece;
  cursor: grab;
  margin-right: 10px;
}
</style>
