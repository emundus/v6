<template>
  <div id="add">
    <AddButton />
  </div>
</template>

<script>
import AddButton from "./components/AddButton";

export default {
  name: "Add",
  components: {
    AddButton
  }
};
</script>

<style>
#add {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  display: flex;
}
</style>
