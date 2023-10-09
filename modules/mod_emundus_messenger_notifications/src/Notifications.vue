<template>
  <div id="app">
    <Messages
        :user="user"
        :fnum="fnum"
        :notifications="notifications"
        @removeNotifications="removeNotifications"
    />
    <div class="em-messages-container">
      <span class="material-icons-outlined em-messages-modal" style="cursor: pointer;" @click="openModal">question_answer</span>
      <p v-if="counter > 0" class="notifications__counter">{{counter}}</p>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Messages from "../../../components/com_emundus/src/components/Messages/Messages";

const qs = require("qs");
axios.defaults.baseURL = '/';

export default {
  name: 'Notifications',
  props:{
    user: Number,
    fnum: String
  },
  components: {
    Messages
  },
  data() {
    return {
      counter: 0,
      notifications: {}
    };
  },
  methods: {
    openModal(){
      this.$modal.show('messages');
    },

    getNotifications(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=messenger&task=getnotifications",
        params: {
          user: this.user,
        },
        paramsSerializer: params => {
           return qs.stringify(params);
        }
      }).then(response => {
        if(response.data.data != null && response.data.status) {
          this.counter = response.data.data.notifications;
          this.notifications = response.data.data;
        }
      });
    },

    removeNotifications(count){
      this.counter -= count;
    }
  },

  created(){
    this.getNotifications();
    setInterval(() => {
      this.getNotifications();
    },20000);
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}

.messages__vue{
  min-height: unset !important;
}
</style>
