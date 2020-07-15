<template>
  <div id="radiobtnF">
    <div class="row rowmodal">
      <div class="form-group">
        <label>{{helptext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.rollover" />
      </div>
      <div class="col-md-8 flex">
        <label class="require col-md-3">{{suboptions}} :</label>
        <button @click.prevent="add" class="add-option">+</button>
      </div>
      <div class="col-md-10">
        <div v-for="(sub_values, i) in arraySubValues" :key="i" class="dpflex">
          <input type="text" v-model="arraySubValues[i]" @change="needtoemit()" class="form__input field-general w-input" :id="'suboption_' + i" @keyup.enter="add"/>
          <button @click.prevent="leave(i)" class="remove-option">-</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import Axios from "axios";
const qs = require("qs");

export default {
  name: "radiobtnF",
  props: { element: Object },
  data() {
    return {
      arraySubValues: [],
      helptext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT"),
      suboptions: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_OPTIONS"),
    };
  },
  methods: {
    add: _.debounce(function() {
      let size = Object.keys(this.arraySubValues).length;
      this.$set(this.arraySubValues, size, "");
      this.needtoemit();
      let id = 'suboption_' + size.toString();
      setTimeout(() => {
        document.getElementById(id).focus();
      }, 100);
    },150),
    leave: function(index) {
      this.$delete(this.arraySubValues, index);
      this.needtoemit();
    },
    initialised: function() {
      if(typeof this.element.params.sub_options !== 'undefined') {
      Axios({
        method: "post",
        url:
          "index.php?option=com_emundus_onboard&controller=formbuilder&task=getJTEXTA",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          toJTEXT: this.element.params.sub_options.sub_labels
        })
      })
        .then(r => {
          this.arraySubValues = r.data;
        })
        .catch(e => {
          console.log(e);
        });
      } else {
        this.element.params.sub_options = {
          'sub_values': [],
          'sub_labels': [],
        }
        this.arraySubValues = this.element.params.sub_options.sub_labels;
      }
    },
    needtoemit: _.debounce(function() {
      this.$emit("subOptions", this.arraySubValues);
    })
  },
  created: function() {
    this.initialised();
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
  .rowmodal {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
  }
  #radiobtnF{
    padding: 10px;
  }
</style>
