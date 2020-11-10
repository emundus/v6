<template>
  <div id="videoM">
    <div class="row rowmodal">
      <div class="form-group">
        <!-- width -->
        <label>{{iswidth}}</label>
        <input type="text" id="width" class="form__input field.general-general w-input" v-model="element.params.width" value="60"/>
      </div>

      <!-- default -->
      <div class="form-group">
        <label>{{isdefault}}</label>
        <input type="text" id="default" class="form__input field.general-general w-input" v-model="element.params.default"/>
      </div>

      <!-- related videos -->
      <div class="form-group">
        <label>{{includerelatedvideo}}</label>
        <select v-model="element.params.include_related" id="selectedVideos" class="dropdown-toggle">
          <option value="0">{{no}}</option>
          <option value="1">{{yes}}</option>
        </select>
      </div>

      <!-- autoplay videos  -->
      <div class="form-group">
        <label>{{autoplayvideo}}</label>
        <select v-model="element.params.youtube_autoplay" id="autoplayVideos" class="dropdown-toggle">
          <option value="0">{{isNo}}</option>
          <option value="1">{{isYes}}</option>
        </select>
      </div>

      <!-- full screen -->
      <div class="form-group">
        <label>{{autofullscreen}}</label>
        <select v-model="element.params.youtube_fullscreen" id="fullscreenVideos" class="dropdown-toggle">
          <option value="0">{{isNo}}</option>
          <option value="1">{{isYes}}</option>
        </select>
      </div>

      <!-- enable cookies -->
      <div class="form-group">
        <label>{{enabledelayedcookies}}</label>
        <select v-model="element.params.enable_delayed_cookies" id="enableCookiesVideos" class="dropdown-toggle">
          <option value="0">{{isNo}}</option>
          <option value="1">{{isYes}}</option>
        </select>
      </div>

      <!-- player size  -->
      <div class="form-group">
        <label>{{isplayersize}}</label>
        <select v-model="element.params.playersize" id="playersize" class="dropdown-toggle">
          <option value="small">{{smallSize}}</option>
          <option value="medium">{{mediumSize}}</option>
          <option value="normal">{{normalSize}}</option>
          <option value="big">{{bigSize}}</option>
        </select>
      </div>

      <!-- customized width -->
      <div class="form-group">
        <label>{{customizedwidth}}</label>
        <input type="text" id="customizedwidth" class="form__input field.general-general w-input" v-model="element.params.or_width_player"/>
      </div>

      <!-- customized heigth -->
      <div class="form-group">
        <label>{{customizedheight}}</label>
        <input type="text" id="customizedheight" class="form__input field.general-general w-input" v-model="element.params.or_height_player"/>
      </div>

      <!-- display in table -->
      <div class="form-group">
        <label>{{displayintable}}</label>
        <select v-model="element.params.display_in_table" id="displayintable" class="dropdown-toggle">
          <option value="0">{{miniTable}}</option>
          <option value="1">{{displayTable}}</option>
          <option value="2">{{normalTable}}</option>
        </select>
      </div>

      <!-- display link  -->
      <div class="form-group">
        <label>{{displaylink}}</label>
        <select v-model="element.params.display_link" id="displaylink" class="dropdown-toggle">
          <option value="0">{{rawLink}}</option>
          <option value="1">{{txtLink}}</option>
          <option value="2">{{watchvideolink}}</option>
        </select>
      </div>

      <!-- text link -->
      <div class="form-group">
        <label>{{textlink}}</label>
        <input type="text" id="textlink" class="form__input field.general-general w-input" v-model="element.params.text_link"/>
      </div>

      <!-- target link -->
      <div class="form-group">
        <label>{{targetlink}}</label>
        <select v-model="element.params.target_link" id="targetlink" class="dropdown-toggle">
          <option value="0">{{currentwindow}}</option>
          <option value="1">{{newwindow}}</option>
          <option value="2">{{islightbox}}</option>
        </select>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: "videoM",
  props: { element: Object },
  data: function () {
    return {
      iswidth: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_WIDTH_TITLE"),
      width: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_WIDTH"),
      isdefault: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_DEFAULT_TITLE"),
      default: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_DEFAULT"),
      includerelatedvideo: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_INCLUDE_RELATED_VIDEO_TITLE"),
      include_related: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_INCLUDE_RELATED_VIDEO"),
      isNo: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_IS_NO"),
      isYes: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_IS_YES"),
      autoplayvideo: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_AUTOPLAY_TITLE"),
      youtube_autoplay: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_AUTOPLAY"),
      autofullscreen: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_AUTOFULLSCREEN_TITLE"),
      youtube_fullscreen: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_AUTOFULLSCREEN"),
      enabledelayedcookies: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_ENABLE_DELAYED_COOKIES_TITLE"),
      enable_delayed_cookies: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_ENABLE_DELAYED_COOKIES"),
      isplayersize: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_PLAYER_SIZE_TITLE"),
      playersize: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_PLAYER_SIZE"),
      smallSize: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_SMALL_SIZE"),
      mediumSize: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_MEDIUM_SIZE"),
      normalSize: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_NORMAL_SIZE"),
      bigSize: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_BIG_SIZE"),
      customizedwidth: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_CUSTOMIZED_WIDTH_TITLE"),
      or_width_player: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_CUSTOMIZED_WIDTH"),
      customizedheight: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_CUSTOMIZED_HEIGHT_TITLE"),
      or_height_player: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_CUSTOMIZED_HEIGHT"),
      displayintable: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_DISPLAY_TABLE_TITLE"),
      display_in_table: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_DISPLAY_TABLE"),
      miniTable: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_TABLE_MINI"),
      displayTable: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_TABLE_DISPLAY"),
      normalTable: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_TABLE_NORMAL"),
      displaylink:Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_DISPLAY_LINK_TITLE"),
      display_link: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_DISPLAY_LINK"),
      rawLink: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_RAW_LINK"),
      txtLink: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_TXT_LINK"),
      watchvideolink: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_WATCH_LINK"),
      textlink: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_TEXT_LINK_TITLE"),
      text_link: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_TEXT_LINK"),
      targetlink: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_TARGET_LINK_TITLE"),
      target_link: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_TARGET_LINK"),
      currentwindow: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_CURRENT_WINDOW"),
      newwindow: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_NEW_WINDOW"),
      islightbox: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_VIDEO_LIGHT_BOX"),
    }
  },
  methods: {},
  watch: {},
}
</script>

<style scoped>
.rowmodal {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

#videoM{
  padding: 10px;
}
</style>