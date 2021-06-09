<template>
  <div class="messages__vue">
    <div class="col-md-3" v-if="fnum == ''">
      <div v-for="campaign in campaigns" @click="campaignSelected = campaign.fnum" :class="campaign.fnum == campaignSelected ? 'messages__active-campaign' : ''" class="messages__block">
        <div class="messages__campaign-block">
          <span>{{campaign.label}}</span>
        </div>
        <hr :class="campaign.fnum == campaignSelected ? 'messages__active-hr' : ''">
      </div>
    </div>
    <!--  <select v-model="campaignSelected">
        <option v-for="campaign in campaigns" :value="campaign.id">{{campaign.label}}</option>
      </select>-->
    <div class="messages__list" :class="fnum != '' ? 'col-md-10 col-md-offset-1' : 'col-md-8'">
      <label class="text-center" style="width: 100%">{{translations.messages}}</label>
      <div class="messages__list-block">
        <div v-for="message in messages" class="messages__message-item" :class="user == message.user_id_from ? 'messages__current_user' : ''">
          <div class="messages__message-item-block">
            <p><em class="messages__message-item-from">{{message.name}}</em></p>
            <span class="messages__message-item-span">{{message.message}}</span>
          </div>
        </div>
      </div>
      <div class="messages__bottom-input">
        <input type="text" v-model="message" @keyup.enter.exact.prevent="sendMessage($event)"/>
        <i class="far fa-paper-plane messages__send-icon" @click="sendMessage"></i>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

import "../assets/css/bootstrap.css";
import "../assets/css/messenger.css";

const qs = require("qs");

export default {
  name: "Messages",
  props: {
    fnum: String,
    user: Number,
  },
  components: {},
  data() {
    return {
      messages: [],
      campaigns: [],
      campaignSelected: 0,
      message: '',
      translations:{
        messages: Joomla.JText._("COM_EMUNDUS_MESSENGER_TITLE"),
      }
    };
  },

  methods: {
    getCampaignsByUser(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_messenger&controller=messages&task=getcampaignsbyuser",
      }).then(response => {
        this.campaigns = response.data.data;
        this.campaignSelected = this.campaigns[0].fnum;
      });
    },

    getMessagesByFnum(){
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
      });
    },

    sendMessage(){
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
    },

    scrollToBottom() {
      setTimeout(() => {
        const container = document.getElementsByClassName("messages__list-block")[0];
        container.scrollTop = container.scrollHeight;
      },500);
    }
  },

  created(){
    if(this.fnum != ''){
      this.campaignSelected = this.fnum;
    } else {
      this.getCampaignsByUser();
    }
  },

  watch: {
    campaignSelected: function(){
      this.getMessagesByFnum();
    }
  }
}
</script>
