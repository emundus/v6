<template>
  <div id="imageM">
    <div class="row rowmodal">
      <!-- image link box -->
      <div class="form-group">
        <label>{{imagePath}}</label>
        <input type="text" id="imagePath" class="form__input field.general-general w-input" v-model="element.params.imagepath"/>
      </div>

      <!-- frontend selection (oui,non)-->
      <div class="form-group">
        <label>{{frontendSelected}}</label>
        <select v-model="element.params.image_front_end_select" id="frontendSelected" class="dropdown-toggle">
          <option value="0" selected>{{isNo}}</option>
          <option value="1">{{isYes}}</option>
        </select>
      </div>

      <!-- afficher le media dans la liste (Oui/Non) -->
      <div class="form-group">
        <label>{{mediaListShow}}</label>
        <select v-model="element.params.show_image_in_table" id="mediaListShow" class="dropdown-toggle">
          <option value="0" selected>{{isNo}}</option>
          <option value="1">{{isYes}}</option>
        </select>
      </div>

      <!-- Position de l'image [None/Left/Right] -->
      <div class="form-group">
        <label>{{imagePosition}}</label>
        <select v-model="element.params.image_float" id="imagePosition" class="dropdown-toggle">
          <option value="0" selected>{{isNone}}</option>
          <option value="1">{{isLeft}}</option>
          <option value="2">{{isRight}}</option>
        </select>
      </div>

      <!-- Lien de l'image -->
      <div class="form-group">
        <label>{{imageLink}}</label>
        <input type="text" id="imageLink" class="form__input field.general-general w-input" v-model="element.params.link_url"/>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "imageM",
  props: {element: Object},
  data() {
    return {
      imagePath: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_PATH_TITLE"),
      imagepath: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_PATH"),
      frontendSelected: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_FRONTEND_SELECTED_TITLE"),
      image_front_end_select: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_FRONTEND_SELECTED"),
      isNo: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_IS_NO"),
      isYes: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_IS_YES"),
      mediaListShow: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_MEDIALIST_SHOW_TITLE"),
      show_image_in_table: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_MEDIALIST_SHOW"),
      imagePosition: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_IMAGE_POSITION_TITLE"),
      image_float: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_IMAGE_POSITION"),
      isNone: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_IS_NONE"),
      isLeft: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_IS_LEFT"),
      isRight: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_IS_RIGHT"),
      imageLink: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_IMAGE_LINK_TITLE"),
      link_url: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_IMAGE_IMAGE_LINK"),
    };
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

#imageM {
  padding: 10px;
}
</style>