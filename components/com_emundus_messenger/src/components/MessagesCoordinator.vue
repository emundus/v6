<template>
  <div class="messages__coordinator_vue">
    <div class="messages__list col-md-12">
      <label class="text-center" style="width: 100%">{{translations.messages}}</label>
      <div class="messages__list-block" id="messages__list">
        <div v-for="date in dates">
          <div class="messages__date-section">
            <hr>
            <p>{{ moment(date.dates).format("DD/MM/YYYY") }}</p>
            <hr>
          </div>
          <div v-for="message in messages" v-if="date.messages.includes(message.message_id)" class="messages__message-item" :class="user == message.user_id_from ? 'messages__current_user' : 'messages__other_user'">
            <div class="messages__message-item-block" @click="showDate != message.message_id ? showDate = message.message_id : showDate = 0" :class="user == message.user_id_from ? 'messages__text-align-right' : 'messages__text-align-left'">
              <p><em class="messages__message-item-from">{{message.name}}</em></p>
              <span class="messages__message-item-span" :class="user == message.user_id_from ? 'messages__message-item-span_current-user' : 'messages__message-item-span_other-user'" v-html="message.message"></span>
              <p><em class="messages__message-item-from" v-if="showDate == message.message_id">{{ moment(message.date_time).format("DD/MM/YYYY HH:mm") }}</em></p>
            </div>
          </div>
        </div>
      </div>
      <transition :name="'slide-up'" type="transition">
        <AttachDocument :user="user" :fnum="fnum" :applicant="false" v-if="attachOpen" @pushAttachmentMessage="pushAttachmentMessage"/>
      </transition>
      <div class="messages__bottom-input">
        <input type="text" class="messages__input_text" v-model="message" @keyup.enter.exact.prevent="sendMessage($event)"/>
        <div class="messages__actions_bar">
          <img class="messages__send-icon" src="/images/emundus/messenger/send.svg" @click="sendMessage" />
          <img class="messages__send-icon" src="/images/emundus/messenger/attached.svg" @click="attachDocument"/>
        </div>
      </div>
    </div>
    <div class="loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";
import moment from 'moment';

import "../assets/css/bootstrap.css";
import "../assets/css/messenger.scss";

import AttachDocument from "../modals/AttachDocument";

const qs = require("qs");

export default {
  name: "MessagesCoordinator",
  props: {
    fnum: String,
    user: Number,
  },
  components: {
    AttachDocument
  },
  data() {
    return {
      dates: [],
      messages: [],
      campaignSelected: 0,
      message: '',
      loading: false,
      showDate: 0,
      counter: 0,
      attachOpen: false,
      translations:{
        messages: Joomla.JText._("COM_EMUNDUS_MESSENGER_TITLE"),
      }
    };
  },

  methods: {
    moment(date) {
      return moment(date);
    },

    getMessagesByFnum(loader = true, scroll = true){
      this.loading = loader;
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
        this.messages = response.data.data.messages;
        this.dates = response.data.data.dates;
        this.markAsRead();
        if(typeof document.getElementsByClassName('notifications-counter')[0] != 'undefined') {
          document.getElementsByClassName('notifications-counter')[0].remove();
        }
        if(scroll) {
          this.scrollToBottom();
        }
        this.loading = false;
      });
    },

    markAsRead(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_messenger&controller=messages&task=markasread",
        params: {
          fnum: this.campaignSelected,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.$emit('removeNotifications',response.data.data);
      });
    },

    sendMessage(e){
      if(typeof e != 'undefined') {
        e.stopImmediatePropagation();
      }
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
          var message_date = response.data.date_time.split(' ')[0];
          this.dates.forEach((elt,index) => {
            if(elt.dates == message_date){
              this.dates[index].messages.push(response.data.message_id);
            }
          });
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
    },

    attachDocument(){
      this.attachOpen = !this.attachOpen;
    },

    pushAttachmentMessage(message){
      var message_date = message.date_time.split(' ')[0];
      this.dates.forEach((elt,index) => {
        if(elt.dates == message_date){
          this.dates[index].messages.push(message.message_id);
        }
      });
      this.messages.push(message);
      this.scrollToBottom();
      this.attachDocument();
    }
  },

  created(){
    if(typeof this.fnum != 'undefined'){
      this.campaignSelected = this.fnum;
      setInterval(() => {
        this.getMessagesByFnum(false);
      },20000);
    }
  },

  watch: {
    campaignSelected: function(){
      this.getMessagesByFnum(true);
    }
  }
}
</script>

<style>
.messages__vue_attach_document{
  width: 100%;
  position: absolute;
  background: #f8f8f8;
  bottom: 70px;
}
</style>
