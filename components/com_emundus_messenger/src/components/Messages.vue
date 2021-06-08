<template>
  <div class="messages__vue">
    <div class="col-md-3">
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
    <div class="col-md-8 messages__list">
      <label class="text-center" style="width: 100%">{{translations.messages}}</label>
      <div class="messages__list-block">
        <div v-for="message in messages" class="messages__message-item" :class="currentUser == message.user_id_from ? 'messages__current_user' : ''">
          <div class="messages__message-item-block">
            <p><em class="messages__message-item-from">{{message.name}}</em></p>
            <span class="messages__message-item-span">{{message.message}}</span>
          </div>
        </div>
      </div>
      <div class="messages__bottom-input">
        <input type="text" v-model="message" @keyup.enter="sendMessage"/>
        <i class="far fa-paper-plane messages__send-icon" @click="sendMessage"></i>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

import "../assets/css/bootstrap.css";

const qs = require("qs");

export default {
  name: "Messages",
  props: {},
  components: {},
  data() {
    return {
      messages: [],
      campaigns: [],
      campaignSelected: 0,
      currentUser: null,
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
        this.currentUser = response.data.current_user;
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
    this.getCampaignsByUser();
  },

  watch: {
    campaignSelected: function(){
      this.getMessagesByFnum();
    }
  }
}
</script>

<style scoped>
  hr{
    margin: 0;
  }

  .messages__vue{
    min-height: 60vh;
  }

  .messages__block{
    padding: 20px 0 0 0;
  }

  .messages__campaign-block{
    min-height: 50px;
    padding: 10px;
  }

  .messages__active-campaign{
    background: #eadede;
  }

  .messages__active-hr{
    border-bottom: 2px solid #12DB42;
  }

  .messages__list{
    min-height: 60vh;
  }

  .messages__list-block{
    min-height: 50vh;
    max-height: 60vh;
    overflow-y: auto;
    overflow-x: hidden;
  }

  .messages__list-block li{
    list-style-type: none;
  }

  .messages__bottom-input{
    position: relative;
    bottom: 0;
    display: flex;
    align-items: center;
  }

  .messages__bottom-input input{
    border-top: unset;
    border-right: unset;
    border-left: unset;
    border-radius: 0;
  }

  .messages__bottom-input input:focus,.messages__bottom-input input:hover{
    box-shadow: unset;
  }

  .messages__send-icon{
    font-size: 25px;
    margin-left: 10px;
  }

  .messages__current_user{
    display: flex;
    justify-content: end;
  }

  .messages__message-item{
    width: 100%;
  }

  .messages__message-item-block{
    width: max-content;
    text-align: right;
  }

  .messages__message-item-span{
    margin: 5px 15px 15px 15px;
    background: #eeebeb;
    padding: 10px;
    border-radius: 5px;
  }

  .messages__message-item-from{
    font-size: 14px;
  }
</style>
