<template>
  <div id="dropdownF">
    <p>Je suis le dropdown {{element.id}}</p>

    <div class="flex">
      Hover help:
      <input type="text" class="inputF" v-model="element.params.rollover" />
    </div>
    <div>
      Sub options :
      <div v-for="(sub_values, i) in arraySubValues" :key="i">
        <input type="text" v-model="arraySubValues[i]" @change="needtoemit()" />
        <button @click.prevent="leave(i)">-</button>
      </div>
      <button @click.prevent="add">+</button>
    </div>
    <!-- <div class="row rowmodal">
      <div class="col-sm-3 flex">
        type de champ :
        <select v-model="element.params.password">

          <option value="0">Texte</option>
          <option value="2">Phone</option>
          <option value="3">Email</option>
          <option value="6">Nombre</option>
        </select>
      </div>
      <div class="col-sm-3 flex">
        placeholder :
        <input type="text" class="inputF" v-model="element.params.placeholder" />
      </div>
      <div class="col-sm-3 flex">
        Size:
        <span>a définir</span>
      </div>
      <div class="col-sm-3 flex">
        Hover help:
        <input type="text" class="inputF" v-model="element.params.rollover" />
      </div>
    </div>-->
  </div>
</template>

<script>
import _ from "lodash";
import Axios from "axios";
const qs = require("qs");

export default {
  name: "dropdownF",
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
</style>