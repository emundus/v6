<template>
  <div class="messages__coordinator_vue em-w-100">
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
              <p><span class="messages__message-item-from">{{message.name}} - {{ moment(message.date_time).format("HH:mm") }}</span></p>
              <span class="messages__message-item-span" :class="user == message.user_id_from ? 'messages__message-item-span_current-user' : 'messages__message-item-span_other-user'" v-html="message.message"></span>
              <p><span class="messages__message-item-from" v-if="showDate == message.message_id">{{ moment(message.date_time).format("DD/MM/YYYY HH:mm") }}</span></p>
            </div>
          </div>
        </div>
      </div>
      <transition :name="'slide-up'" type="transition">
        <AttachDocument :user="user" :fnum="fnum" :applicant="false" v-if="attachOpen" @pushAttachmentMessage="pushAttachmentMessage" @close="attachDocument" ref="attachment"/>
      </transition>
      <div class="messages__bottom-input">
        <textarea type="text"
                  class="messages__input_text"
                  :disabled="attachOpen"
                  rows="1"
                  spellcheck="true"
                  v-model="message"
                  :placeholder="translations.writeMessage"
                  @keydown.enter.exact.prevent="sendMessage($event)"
        />
      </div>
      <div class="messages__bottom-input-actions">
        <div class="messages__actions_bar">
          <span class="material-icons-outlined em-pointer"  @click="attachDocument">attach_file</span>
        </div>
        <button type="button" class="messages__send_button btn btn-primary" @click="sendMessage">
          {{ translations.send }}
        </button>
      </div>
    </div>
    <div class="loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";
import moment from 'moment';

import "../../assets/css/messenger.scss";

import AttachDocument from "./modals/AttachDocument";

const qs = require("qs");

export default {
  name: "MessagesCoordinator",
  props: {},
  components: {
    AttachDocument
  },
  data() {
    return {
      fnum: String,
      user: Number,

      dates: [],
      messages: [],
      fileSelected: 0,
      message: '',
      loading: false,
      showDate: 0,
      counter: 0,
      attachOpen: false,
      translations:{
        messages: Joomla.JText._("COM_EMUNDUS_MESSENGER_TITLE"),
        send: Joomla.JText._("COM_EMUNDUS_MESSENGER_SEND"),
        writeMessage: Joomla.JText._("COM_EMUNDUS_MESSENGER_WRITE_MESSAGE"),
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
        url: "index.php?option=com_emundus&controller=messenger&task=getmessagesbyfnum",
        params: {
          fnum: this.fileSelected,
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
        url: "index.php?option=com_emundus&controller=messenger&task=markasread",
        params: {
          fnum: this.fileSelected,
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
      if(this.attachOpen) {
        this.$refs.attachment.sendMessage(this.message);
        this.message = '';
      } else {
        if (this.message.trim() !== '') {
          axios({
            method: "post",
            url:
                "index.php?option=com_emundus&controller=messenger&task=sendmessage",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              message: this.message,
              fnum: this.fileSelected
            })
          }).then(response => {
            if (response.data.status) {
              this.message = '';
              this.pushToDatesArray(response.data.data);
              this.scrollToBottom();
            }
          });
        }
      }
    },

    pushToDatesArray(message) {
      let pushToDate = false;

      let message_date = this.moment().format("YYYY-MM-DD");
      if (message.date_time) {
        message_date = message.date_time.split(' ')[0];
      }
      this.dates.forEach((elt,index) => {
        if(elt.dates == message_date){
          this.dates[index].messages.push(message.message_id);
          pushToDate = true;
        }
      });
      if(!pushToDate) {
        var new_date = {
          dates: this.moment().format("YYYY-MM-DD"),
          messages: []
        }
        new_date.messages.push(message.message_id);
        this.dates.push(new_date);
      }
      this.messages.push(message);
    },

    scrollToBottom() {
      setTimeout(() => {
        const container = document.getElementsByClassName("messages__list-block")[0];
        container.scrollTop = container.scrollHeight;
      },500);
    },

    attachDocument(){
      this.attachOpen = !this.attachOpen;
      setTimeout(() => {
        if(this.attachOpen){
          this.$refs.attachment.getTypesByCampaign();
        }
      },500);
    },

    pushAttachmentMessage(message){
      this.pushToDatesArray(message);
      this.scrollToBottom();
      this.attachDocument();
    }
  },

  created(){
    this.fnum = this.$store.getters['global/datas'].fnum.value;
    this.user = this.$store.getters['global/datas'].user.value;

    if (typeof this.fnum != 'undefined') {
      this.fileSelected = this.fnum;
      this.getMessagesByFnum();
      setInterval(() => {
        this.getMessagesByFnum(false, false);
      }, 20000);
    }
  },

  watch: {
    fileSelected: function(){
      this.getMessagesByFnum(true);
    }
  }
}
</script>

<style>
.messages__vue_attach_document{
  width: 100%;
  position: absolute;
  background: white;
  bottom: 120px;
	z-index: 9999;
}
.messages__list-block{
  padding: 0px 55px;
}

.messages__attach_content {
  padding: 0 70px;
}

.messages__actions_bar .material-icons-outlined {
	font-size: 20px;
}

.messages__bottom-input, .messages__bottom-input-actions {
	padding-left: 16px;
	padding-right: 16px;
}
</style>
