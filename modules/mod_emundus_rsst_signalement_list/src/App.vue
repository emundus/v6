<template>
  <div id="app">
    <List :listId="fabrikListId" :ListActionColumn="fabrikListActionColumn"
          :listParticularConditionalColumn="fabrikListParticularConditionalColumn"
          :listParticularConditionalColumnValues="fabrikListParticularConditionalColumnValues"
          :listColumnShowingAsBadge="fabrikListColumnShowingAsBadge"
          :listColumnToNotShowingWhenFilteredBy="fabrikListColumnToNotShowingWhenFilteredBy"
    />
  </div>
</template>

<script>
import moment from 'moment';
import List from './components/List.vue';

export default {
  name: 'App',
  components: {
    List
  },
  props: {
    fabrikListId: {
      type: String,
      required: true
    },
    fabrikListActionColumn: {
      type: String,
      required: false
    },
    fabrikListParticularConditionalColumn: {
      type: String,
      required: false
    },
    fabrikListParticularConditionalColumnValues: {
      type: String,
      required: false
    },
    fabrikListColumnShowingAsBadge: {
      type: String,
      required: false
    },
    fabrikListColumnToNotShowingWhenFilteredBy: {
      type: String,
      required: false
    }
  },
  created() {
    moment.locale('fr');
  }
}
</script>

<style>
@import url("../../../components/com_emundus/src/assets/css/main.scss");

#app {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  margin-top: 60px;
}

#g-container-main .g-container {
  width: 90%;
}

</style>
