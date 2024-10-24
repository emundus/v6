<template>
  <div class="messages__vue">
    <span :id="'messages'">
      <modal
          :name="'messages'"
          transition="nice-modal-fade"
          :adaptive="true"
          height="90%"
          width="90%"
          :scrollable="true"
          :delay="100"
          :clickToClose="true"
          :draggable="'.drag-window'"
          @closed="beforeClose"
          @opened="getFilesByUser"
      >
        <div class="em-flex-row em-flex-align-start em-w-100 em-h-100 em-small-flex-column">
          <div class="messages__campaigns-list em-h-100">
            <div v-for="file in files" @click="fileSelected = file.fnum" :key="file.fnum" :class="file.fnum == fileSelected ? 'messages__active-campaign' : ''" class="messages__block">
              <div class="messages__campaign-block em-w-100">
                <div class="em-w-100">
                  <p class="messages__campaigns_title">{{file.label}}</p>
                  <p class="messages__campaigns_fnum messages__campaigns_title">N° {{file.fnum}}</p>
                  <p class="messages__campaigns_fnum messages__campaigns_title">{{file.year}}</p>
                </div>
              </div>
              <div></div>
            </div>
          </div>

          <div class="messages__campaigns-list-select">
            <select v-model="fileSelected">
              <option v-for="file in files" :value="file.fnum" :key="file.fnum">{{ file.label }} - {{ file.fnum }}</option>
            </select>
          </div>

          <div class="messages__list em-w-100 em-h-100 em-flex-column em-flex-space-between">
            <div class="message__header em-w-100">
              <label class="text-center" style="width: 100%">{{translations.messages}}</label>
              <i class="fas fa-times pointer" @click="$modal.hide('messages')"></i>
            </div>
            <div class="messages__list-block em-w-100 em-h-100" id="messages__list">
              <div v-for="date in messageByDates" :key="date.date">
                <div class="messages__date-section">
                  <hr>
                  <p>{{ moment(date.date).format("DD/MM/YYYY") }}</p>
                  <hr>
                </div>
                <div v-for="message in date.messages" :key="message.message_id" class="messages__message-item" :class="user == message.user_id_from ? 'messages__current_user' : 'messages__other_user'">
                  <div class="messages__message-item-block" @click="showDate != message.message_id ? showDate = message.message_id : showDate = 0" :class="user == message.user_id_from ? 'messages__text-align-right' : 'messages__text-align-left'">
                    <p>
                      <span class="messages__message-item-from">
                        <span v-if="anonymous === 0 && user != message.user_id_from">{{message.name}} - </span>
                        <span v-if="user == message.user_id_from">{{message.name}} - </span>
                        {{ message.date_hour }}
                      </span>
                    </p>
                    <span class="messages__message-item-span" :class="user == message.user_id_from ? 'messages__message-item-span_current-user' : 'messages__message-item-span_other-user'" v-html="message.message"></span>
                    <p><span class="messages__message-item-from" v-if="showDate == message.message_id">{{ moment(message.date_time).format("DD/MM/YYYY HH:mm") }}</span></p>
                  </div>
                </div>
              </div>
              <transition :name="'slide-up'" type="transition">
               <AttachDocument :user="user" :fnum="fileSelected" v-if="attachOpen" :applicant="true" @pushAttachmentMessage="pushAttachmentMessage" @close="attachDocument" ref="attachment"/>
              </transition>
            </div>

            <div style="position: sticky;bottom: 15px;padding: 0 15px;margin-right: 15px;" class="em-w-100">
              <div class="messages__bottom-input">
                <textarea type="text"
                          class="messages__input_text em-p-8"
                          rows="1"
                          :disabled="send_progress || attachOpen"
                          spellcheck="true"
                          :placeholder="translations.writeMessage"
                          v-model="message"
                          @keydown.enter.exact.prevent="sendMessage($event)"
                />
              </div>
              <div class="messages__bottom-input-actions" v-if="!send_progress">
                <div class="messages__actions_bar">
                  <span class="material-icons-outlined em-pointer" @click="attachDocument">attach_file</span>
                </div>
                <button type="button" class="messages__send_button btn btn-primary" @click="sendMessage">
                    {{ translations.send }}
                </button>
              </div>
            </div>

          </div>
        </div>
      </modal>
    </span>
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
  name: "Messages",
  props: {
    user: Number,
    fnum: String,
    notifications: Object,
  },
  components: {
    AttachDocument
  },
  data() {
    return {
      dates: [],
      messages: [],
      anonymous: 0,
      files: [],
      fileSelected: 0,
      message: '',
      loading: false,
      interval: 0,
      showDate: 0,
      attachOpen: false,
      send_progress: false,
      currentUserName: '',
      translations:{
        messages: Joomla.JText._("COM_EMUNDUS_MESSENGER_TITLE"),
        send: Joomla.JText._("COM_EMUNDUS_MESSENGER_SEND"),
        writeMessage: Joomla.JText._("COM_EMUNDUS_MESSENGER_WRITE_MESSAGE"),
      }
    };
  },
  mounted() {
    this.getUsername();
  },

  methods: {
    moment(date) {
      return moment(date);
    },

    beforeClose() {
      clearInterval(this.interval);
    },

    getUsername() {
      fetch('index.php?option=com_emundus&controller=users&task=getuserbyid')
        .then((res) => {
          if (res.ok) {
            return res.json();
          }
        }).then((response) => {
          if (response.status) {
            this.currentUserName = response.user[0].firstname + ' ' + response.user[0].lastname;
          }
        });
    },

    getFilesByUser(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=messenger&task=getfilesbyuser",
      }).then(response => {
        this.files = response.data.data;
        if(this.fnum != ''){
          this.fileSelected = this.fnum;
        } else {
          this.fileSelected = this.files[0].fnum;
        }
        this.getMessagesByFnum(true);
        this.interval = setInterval(() => {
          this.getMessagesByFnum(false,false);
        },20000);
      });
    },

    async getMessagesByFnum(loader = true,scroll = true){
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
        this.anonymous = parseInt(response.data.data.anonymous);
        this.markAsRead();
        if(scroll) {
          this.scrollToBottom();
        }
        this.loading = false;
      });
    },

    markAsRead(){
      fetch('index.php?option=com_emundus&controller=messenger&task=markasread&fnum=' + this.fileSelected)
        .then((res) => {
          if (res.ok) {
            return res.json;
          }
        }).then((response) => {
          if (response.status) {
            this.$emit('removeNotifications',response.data);
          }
        });
    },

    async sendMessage(e){
      if (this.message.trim() !== '' && !this.send_progress) {
        this.send_progress = true;
 
        const formData = new FormData();
        formData.append('message', this.message);
        formData.append('fnum', this.fileSelected);

        fetch('index.php?option=com_emundus&controller=messenger&task=sendmessage', {
          method: 'POST',
          body: formData
        }).then((res) => {
          if (res.ok) {
            return res.json();
          }
        }).then((response) => {
          this.send_progress = false;
          
          if (response.status) {
            this.getMessagesByFnum(true, true);
          } else {
            Swal.fire({
              title: Joomla.JText._("COM_EMUNDUS_ONBOARD_ERROR"),
              text: response.msg,
              type: "error",
              showCancelButton: false,
              showConfirmButton: false,
              timer: 3000,
            });
          }
        });

        this.pushToDatesArray({
            message_id: Math.floor(Math.random() * 1000) + 9999,
            user_id_from: this.user,
            user_id_to: null,
            folder_id: 2,
            date_time: this.formatedTimestamp(),
            state: 0, 
            priority: 0, 
            subject: 0,
            message: this.message, 
            email_from: null, 
            email_cc: null, 
            email_to: null, 
            name: this.currentUserName
          });

          this.message = '';
          this.send_progress = false;
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

      if (!pushToDate) {
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
        const container = document.getElementById("messages__list");
        container.scrollTop = container.scrollHeight;
      },500);
    },

    attachDocument(){
      this.attachOpen = !this.attachOpen;
      this.scrollToBottom();
      setTimeout(() => {
        if(this.attachOpen){
          this.$refs.attachment.getTypesByCampaign();
        }
      },500);
    },

    pushAttachmentMessage(message){
      this.pushToDatesArray(message);
      this.scrollToBottom();
      this.attachOpen = !this.attachOpen;
    },

    formatedTimestamp()  {
      const d = new Date()
      const date = d.toISOString().split('T')[0];
      const time = d.toTimeString().split(' ')[0];
      return `${date} ${time}`
    }
  },

  computed: {
    messageByDates() {
      let messages = [];

      this.dates.forEach((elt,index) => {
        let date = elt.dates;
        let messages_array = [];
        elt.messages.forEach((message_id) => {
          this.messages.forEach((message) => {
            if(message.message_id == message_id){
              messages_array.push(message);
            }
          });
        });
        messages.push({date: date, messages: messages_array});
      });

      return messages;
    }
  },

  watch: {
    fileSelected: function() {
      this.getMessagesByFnum(true);
    }
  }
}
</script>

<style lang="scss">
@import url("../../assets/css/messenger.scss");

.messages__vue_attach_document{
  background: #f8f8f8;
}
</style>
