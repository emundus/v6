<template>
  <div id="actions-list-table">
    <div class="em-flex-row-start em-flex-space-between em-w-auto em-mb-32">
      <filter-item></filter-item>
      <filter-item></filter-item>
      <filter-item></filter-item>
      <filter-item></filter-item>
      <filter-item></filter-item>
      <filter-item></filter-item>
    </div>


    <table :aria-describedby="'Table of actions lists '">
      <thead class="list-table-head">
      <tr>
        <th> <input type="checkbox" class="em-switch input"></th>
        <th>Numéro de signalement</th>
        <th>Action</th>
        <th>Date d'ajout de l'action</th>
        <th>État de la publication</th>
        <th colspan="2">Etat de la réalisation</th>
      </tr>
      </thead>
      <tbody>
          <Row/>
      </tbody>
    </table>


  </div>
</template>

<script>
import Row from './Row.vue';
import Filter from './Filter.vue'

export default {
  name: "List",
  components: {
    Row,
    'filter-item': Filter,
  }
}
</script>

<style scoped>

</style>
