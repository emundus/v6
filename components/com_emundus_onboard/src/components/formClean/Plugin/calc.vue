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
            <div @click="updateOperator(calc_element,'+')" class="operator" :id="'plus_' + calc_element.id">
              <em class="fas fa-plus"></em>
            </div>
            <div @click="updateOperator(calc_element,'-')" class="operator" :id="'minus_' + calc_element.id">
              <em class="fas fa-minus"></em>
            </div>
            <div @click="updateOperator(calc_element,'*')" class="operator" :id="'multiple_' + calc_element.id">
              <em class="fas fa-times"></em>
            </div>
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

      //Check if we update an element
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
      }

      calc_element.operator = operator;
      switch (operator) {
        case '+':
          document.getElementById('minus_' + calc_element.id).classList.remove('operator-selected')
          document.getElementById('multiple_' + calc_element.id).classList.remove('operator-selected')
          document.getElementById('plus_' + calc_element.id).classList.add('operator-selected')
          break;
        case '*':
          document.getElementById('minus_' + calc_element.id).classList.remove('operator-selected')
          document.getElementById('plus_' + calc_element.id).classList.remove('operator-selected')
          document.getElementById('multiple_' + calc_element.id).classList.add('operator-selected')
          break;
        case '-':
          document.getElementById('plus_' + calc_element.id).classList.remove('operator-selected')
          document.getElementById('multiple_' + calc_element.id).classList.remove('operator-selected')
          document.getElementById('minus_' + calc_element.id).classList.add('operator-selected')
          break;
        default:
          document.getElementById('plus_' + calc_element.id).classList.remove('operator-selected')
          document.getElementById('multiple_' + calc_element.id).classList.remove('operator-selected')
          document.getElementById('minus_' + calc_element.id).classList.remove('operator-selected')
      }
    },

    leave: function(index) {},
    initialised: function() {
      this.raw_elements = this.elements;
      if(this.calc_elements.length == 0){
        this.calc_elements.push({
          'old': null,
          'id': null,
          'element': null,
          'operator': null
        });
      }
    },
    needtoemit: _.debounce(function() {}),
  },
  created: function() {
    this.initialised();
  },
  watch: {}
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
