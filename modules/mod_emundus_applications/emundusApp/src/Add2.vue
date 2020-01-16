<template>
  <div id="add2">
    <AddButton />
  </div>
</template>

<script>
import AddButton from "./components/AddButton";

export default {
  name: "Add2",
  components: {
    AddButton
  }
};
</script>

<style>
#add2 {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  display: flex;
}
</style>
