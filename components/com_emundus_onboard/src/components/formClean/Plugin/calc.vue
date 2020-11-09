<template>
  <div id="calcF">
    <div class="row rowmodal">
      <div class="col-md-8 flex">
        <label class="require col-md-3">{{calculation}} :</label>
      </div>
      <div class="col-md-10">
        <div v-for="(calc_element,key) in calc_elements">
          <div class="flex">
            <select class="dropdown-toggle" @change="addElement($event,calc_element)" v-model="calc_element.id">
              <option disabled v-if="calc_elements.length < 2" :value="null">{{SelectElementToStart}}</option>
              <option v-for="(raw_element,index) in raw_elements" :disabled="raw_element.disabled" :value="raw_element.id">{{raw_element.label[actualLanguage]}}</option>
            </select>
            <div @click="removeElementFromCalc(key)" class="remove-elt" v-if="key != 0">
              <em class="fas fa-trash-alt"></em>
            </div>
          </div>
          <div class="flex" v-if="calc_element.element != null">
            <span @click="updateOperator(calc_element,'+')" class="operator" :class="calc_element.operator == '+' ? 'operator-selected' : ''" :id="'plus_' + calc_element.id">
              <em class="fas fa-plus"></em>
            </span>
            <span @click="updateOperator(calc_element,'-')" class="operator" :class="calc_element.operator == '-' ? 'operator-selected' : ''" :id="'minus_' + calc_element.id">
              <em class="fas fa-minus"></em>
            </span>
            <span @click="updateOperator(calc_element,'*')" class="operator" :class="calc_element.operator == '*' ? 'operator-selected' : ''" :id="'multiple_' + calc_element.id">
              <em class="fas fa-times"></em>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import axios from "axios";
import Swal from "sweetalert2";
const qs = require("qs");

export default {
  name: "calcF",
  components: {},
  props: { element: Object, elements: Array, actualLanguage: String },
  data() {
    return {
      calc_elements: [],
      raw_elements: [],
      operators_regex: /(\*|\+|\-)/,
      operators: ['-','+','*','/'],
      calculation: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_CALC"),
      SelectElementToStart: Joomla.JText._("COM_EMUNDUS_ONBOARD_CALC_SELECT_ELEMENT"),
    };
  },
  methods: {
    findObjectByKey(array, key, value) {
      for(let index = 0; index < array.length; index++){
        if(array[index][key] == value){
          return array[index];
        }
      }
    },

    addElement: _.debounce(function(value,calc_elt) {
      let id = parseInt(value.srcElement.value);
      let elementToAdd = this.findObjectByKey(this.raw_elements, 'id', id);

      this.findObjectByKey(this.raw_elements, 'id', id).disabled = true;

      // Check if we update an element
      if(calc_elt.old != null) {
        this.findObjectByKey(this.raw_elements, 'id', calc_elt.old).disabled = false;
      }
      calc_elt.old = id;
      //

      // Get db element
      var el = document.createElement( 'html' );
      el.innerHTML = elementToAdd.label_value;
      calc_elt.element =  el.getElementsByTagName('label')[0].getAttribute('for');
      //

      this.needtoemit();
    },150),

    removeElementFromCalc: _.debounce(function(key) {
      this.calc_elements.splice(key, 1);
      this.updateOperator(this.calc_elements[key-1],null);
    },150),

    updateOperator(calc_element,operator){
      if(operator != null && calc_element.operator == null) {
        this.calc_elements.push({
          'id': null,
          'element': null,
          'operator': null
        });
        calc_element.operator = operator;
      } else {
        calc_element.operator = operator;
        this.needtoemit();
      }
    },

    needtoemit: _.debounce(function() {
      this.$emit("addToCalc", this.calc_elements);
    }),
  },

  created: function() {
    this.raw_elements = this.elements;

    if(this.element.params.calc_calculation != '') {
      let splitByOperators = this.element.params.calc_calculation.split(this.operators_regex);
      let criteria_id = null;

      splitByOperators.forEach((value) => {
        if (value != '-' && value != '+' && value != '*' && value != '/') {
          // Get only the criteria without raw
          let criteria = value.split(/({|})/).filter(element => element.match(/\W*(jos_)\W*/))[0].split('_raw')[0];

          // Get the element id
          this.raw_elements.forEach((element) => {
            var el = document.createElement('html');
            el.innerHTML = element.label_value;
            if (criteria == el.getElementsByTagName('label')[0].getAttribute('for')) {
              criteria_id = element.id;
            }
          });

          // Push to our calculation array
          this.calc_elements.push({
            'old': null,
            'id': criteria_id,
            'element': criteria,
            'operator': null
          });

          // Disable the element from the dropdown list
          this.findObjectByKey(this.raw_elements, 'id', criteria_id).disabled = true;
        } else {
          // Add the operator to previous element
          this.findObjectByKey(this.calc_elements, 'id', criteria_id).operator = value;
        }
      });
    }

    // If calculation is empty create a first element
    if(this.calc_elements.length == 0){
      this.calc_elements.push({
        'old': null,
        'id': null,
        'element': null,
        'operator': null
      });
    }
  }
};
</script>
<style scoped>
.flex {
  display: flex;
  align-items: center;
  margin-bottom: 1em;
  height: 30px;
  margin-top: 1em;
}
.operator{
  margin: 15px;
  background: #cecece;
  padding: 5px;
  border-radius: 4px;
  color: black;
  cursor: pointer;
  width: 30px;
  text-align: center;
}
.remove-elt{
  margin: 15px;
  background: #de6339;
  padding: 5px 7px;
  border-radius: 4px;
  color: white;
  cursor: pointer;
  position: absolute;
  right: -4em;
}
.operator:hover{
  background: #de6339;
  color: white;
}
.operator:hover .fa-times{
  color: white;
}
.operator-selected{
  background: #de6339 !important;
  color: white !important;
}
.operator-selected .fa-times{
  color: white !important;
}
.rowmodal {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}
#calcF{
  padding: 10px;
}
</style>
