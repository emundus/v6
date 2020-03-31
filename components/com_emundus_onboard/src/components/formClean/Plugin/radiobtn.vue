<template>
  <div id="radiobtnF">
    <p>Je suis le radiobtn {{element.id}}</p>
    <div class="flex">
      Hover help:
      <input type="text" class="inputF" v-model="element.params.rollover" />
    </div>
  <div class="suboptions">
      Sub options :
      <span>
        <button @click.prevent="add" class="plusmoins toright">+</button>
      </span>
      <div v-for="(sub_values, i) in arraySubValues" :key="i" class="dpflex">
        <input type="text" v-model="arraySubValues[i]" @change="needtoemit()" class="inputF" />
        <button @click.prevent="leave(i)" class="plusmoins">-</button>
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
      arraySubValues: []
    };
  },
  methods: {
    add: function() {
      let size = Object.keys(this.arraySubValues).length;
      this.$set(this.arraySubValues, size, "");
      this.needtoemit();
    },
    leave: function(index) {
      this.$delete(this.arraySubValues, index);
      this.needtoemit();
    },
    initialised: function() {
      Axios({
        method: "post",
        url:
          "index.php?option=com_emundus_onboard&controller=formbuilder&task=getJTEXT",
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
}
.rowmodal {
  margin-bottom: 45px;
}

.plusmoins {
  border-radius: 12px 0 12px 0;
  background: #1b1f3c;
  border: none;
  color: #fff;
  font: bold 12px Verdana;
  padding: 6px 12px 6px 12px;
  margin-right: 15px;
  cursor: pointer;
  text-transform: capitalize;
  transition-duration: 0.4s;
}

.plusmoins:hover {
  background-color: white;
  color: black;
  border: 1px solid #1b1f3c;
}
.inputF {
  width: 80% !important ;
  margin: 0 2%;
}
</style>