<template>
  <div id="addButton">
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
    />
    <button class="buttonAdd" @click="addDossier()">
      <div class="fa fa-plus"></div>
      {{ msg }}
    </button>
  </div>
</template>

<script>
export default {
  name: "AddButton",
  data() {
    return {
      msg: "Déposer un nouveau dossier"
    };
  },
  methods: {
    addDossier: function() {
      location.replace("component/fabrik/form/102");
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
button.buttonAdd {
  color: white;
  background-color: #1b1f3c;
  height: 40px;
  font-size: 14px;
  border: none;
  width: 230px;
  display: block;
}

div.fa {
  margin-right: 5px;
}

button.buttonAdd:hover {
  color: #1b1f3c;
  background-color: white;
  border: 1px solid #1b1f3c;
  cursor: pointer;
}
</style>
