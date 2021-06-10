<template>
  <div class="messages__coordinator_vue">
    <div class="messages__list col-md-12">
      <label class="text-center" style="width: 100%">{{translations.messages}}</label>
      <div class="messages__list-block" id="messages__list">
        <div v-for="message in messages" class="messages__message-item" :class="user == message.user_id_from ? 'messages__current_user' : 'messages__other_user'">
          <div class="messages__message-item-block" :class="user == message.user_id_from ? 'messages__text-align-right' : 'messages__text-align-left'">
            <p><em class="messages__message-item-from">{{message.name}}</em></p>
            <span class="messages__message-item-span" :class="user == message.user_id_from ? 'messages__message-item-span_current-user' : 'messages__message-item-span_other-user'">{{message.message}}</span>
          </div>
        </div>
      </div>
      <div class="messages__bottom-input">
        <input type="text" class="messages__input_text" v-model="message" @keyup.enter.exact.prevent="sendMessage($event)"/>
        <img class="messages__send-icon" src="/images/emundus/messenger/send.svg" @click="sendMessage" />
      </div>
    </div>
    <div class="loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";

import "../assets/css/bootstrap.css";
import "../assets/css/messenger.scss";

const qs = require("qs");

export default {
  name: "MessagesCoordinator",
  props: {
    fnum: String,
    user: Number,
  },
  components: {},
  data() {
    return {
      messages: [],
      campaignSelected: 0,
      message: '',
      loading: false,
      translations:{
        messages: Joomla.JText._("COM_EMUNDUS_MESSENGER_TITLE"),
      }
    };
  },

  methods: {
    getMessagesByFnum(){
      this.loading = true;
      axios({
        method: "get",
        url: "index.php?option=com_emundus_messenger&controller=messages&task=getmessagesbyfnum",
        params: {
          fnum: this.campaignSelected,
        },
        paramsSerializer: params => {
           return qs.stringify(params);
        }
      }).then(response => {
        this.messages = response.data.data;
        this.scrollToBottom();
        this.loading = false;
      });
    },

    sendMessage(){
      if(this.message !== '') {
        axios({
          method: "post",
          url:
              "index.php?option=com_emundus_messenger&controller=messages&task=sendmessage",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            message: this.message,
            fnum: this.campaignSelected
          })
        }).then(response => {
          this.message = '';
          this.messages.push(response.data);
          this.scrollToBottom();
        });
      }
    },

    scrollToBottom() {
      setTimeout(() => {
        const container = document.getElementsByClassName("messages__list-block")[0];
        container.scrollTop = container.scrollHeight;
      },500);
    }
  },

  created(){
    if(typeof this.fnum != 'undefined'){
      this.campaignSelected = this.fnum;
    }
  },

  watch: {
    campaignSelected: function(){
      this.getMessagesByFnum();
    }
  }
}
</script>
