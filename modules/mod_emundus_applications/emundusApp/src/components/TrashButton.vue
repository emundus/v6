<template>
  <div id="trashButton">
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
    />
    <button class="trashAdd" @click="deletefile(fnum); listdossiers()">
      <div class="far fa-trash-alt"></div>
    </button>
  </div>
</template>

<script>
import axios from "../../node_modules/axios";

export default {
  name: "TrashButton",
  data: function() {
    return {
      dossiers: ""
    };
  },
  mounted() {
    this.listdossiers();
  },
  props: { fnum: String },
  methods: {
    deletefile: function(fnum) {
      axios.post(
        "index.php?option=com_emundus&task=deletefile&fnum=" + this.fnum
      );
    },
    listdossiers: function() {
      axios
        .get(
          "index.php?option=com_ajax&module=emundus_applications&format=json&method=getdossiers"
        )
        .then(response => this.$emit("dossiers", response.data))
        .catch(function(error) {
          console.log(error);
        });
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
#trashButton {
  display: flex;
}

button.trashAdd {
  color: white;
  background-color: #1b1f3c;
  height: 40px;
  width: 40px;
  font-size: 17px;
  border: none;
  display: block;
  margin-left: 5px;
}

div.fa {
  margin-right: 5px;
}

button.trashAdd:hover {
  color: #1b1f3c;
  background-color: white;
  border: 1px solid #1b1f3c;
  cursor: pointer;
}
</style>
