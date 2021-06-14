<template>
  <div class="messages__vue">
    <span :id="'messages'">
      <modal
          :name="'messages'"
          transition="nice-modal-fade"
          :adaptive="true"
          height="auto"
          width="70%"
          :scrollable="true"
          :delay="100"
          :clickToClose="true"
          @closed="beforeClose"
          @opened="getCampaignsByUser"
      >

        <div class="drag-window">
          <div class="col-md-3 messages__campaigns-list">
            <div v-for="campaign in campaigns" @click="campaignSelected = campaign.fnum" :class="campaign.fnum == campaignSelected ? 'messages__active-campaign' : ''" class="messages__block">
              <div class="messages__campaign-block">
                <img class="messages__campaigns_folder-icon" src="/images/emundus/messenger/folder.svg" />
                <span class="messages__campaigns_title">{{campaign.label}}</span>
              </div>
            </div>
          </div>

          <div class="messages__list col-md-9">
            <div class="message__header">
              <label class="text-center" style="width: 100%">{{translations.messages}}</label>
              <i class="fas fa-times pointer" @click="$modal.hide('messages')"></i>
            </div>
            <div class="messages__list-block" id="messages__list">
<!--              <infinite-loading direction="top" @infinite="infiniteHandler"></infinite-loading>-->
              <div v-for="date in dates">
                <div class="messages__date-section">
                  <hr>
                  <p>{{ moment(date.dates).format("DD/MM/YYYY") }}</p>
                  <hr>
                </div>
                <div v-for="message in messages" v-if="date.messages.includes(message.message_id)" class="messages__message-item" :class="user == message.user_id_from ? 'messages__current_user' : 'messages__other_user'">
                  <div class="messages__message-item-block" @click="showDate != message.message_id ? showDate = message.message_id : showDate = 0" :class="user == message.user_id_from ? 'messages__text-align-right' : 'messages__text-align-left'">
                    <p><em class="messages__message-item-from">{{message.name}}</em></p>
                    <span class="messages__message-item-span" :class="user == message.user_id_from ? 'messages__message-item-span_current-user' : 'messages__message-item-span_other-user'">{{message.message}}</span>
                    <p><em class="messages__message-item-from" v-if="showDate == message.message_id">{{ moment(message.date_time).format("DD/MM/YYYY HH:mm") }}</em></p>
                  </div>
                </div>
              </div>
            </div>
            <div class="messages__bottom-input">
              <input type="text" class="messages__input_text" v-model="message" @keyup.enter.exact.prevent="sendMessage($event)"/>
              <img class="messages__send-icon" src="/images/emundus/messenger/send.svg" @click="sendMessage" />
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
import InfiniteLoading from 'vue-infinite-loading';
import moment from 'moment';

import "../assets/css/bootstrap.css";
import "../assets/css/messenger.scss";

const qs = require("qs");

export default {
  name: "Messages",
  props: {
    user: Number,
    fnum: String,
    notifications: Object,
  },
  components: {
    InfiniteLoading
  },
  data() {
    return {
      dates: [],
      messages: [],
      campaigns: [],
      campaignSelected: 0,
      message: '',
      loading: false,
      interval: 0,
      showDate: 0,
      translations:{
        messages: Joomla.JText._("COM_EMUNDUS_MESSENGER_TITLE"),
      }
    };
  },

  methods: {
    moment(date) {
      return moment(date);
    },

    beforeClose() {
      clearInterval(this.interval);
    },

    getCampaignsByUser(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_messenger&controller=messages&task=getcampaignsbyuser",
      }).then(response => {
        this.campaigns = response.data.data;
        if(this.fnum != ''){
          this.campaignSelected = this.fnum;
        } else {
          this.campaignSelected = this.campaigns[0].fnum;
        }
        this.getMessagesByFnum(true);
        this.interval = setInterval(() => {
          this.getMessagesByFnum(false);
        },20000);
      });
    },

    getMessagesByFnum(loader = true){
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
        this.scrollToBottom();
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

    /*infiniteHandler($state){
      setTimeout(() => {
            axios({
              method: "get",
              url: "index.php?option=com_emundus_messenger&controller=messages&task=getmessagesbyfnum",
              params: {
                fnum: this.campaignSelected,
                offset: this.messages.length,
              },
              paramsSerializer: params => {
                return qs.stringify(params);
              }
            }).then(response => {
              this.messages.unshift(response.data.data);
              this.markAsRead();
              $state.loaded();
            });
      },1000);
    },*/

    scrollToBottom() {
      setTimeout(() => {
        const container = document.getElementById("messages__list");
        container.scrollTop = container.scrollHeight;
      },500);
    }
  },

  watch: {
    campaignSelected: function(){
      this.getMessagesByFnum(true);
    }
  }
}
</script>
