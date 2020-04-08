<template>
  <div class="container-evaluation">
    <h2 class="heading">{{ funnelCategorie }}</h2>
    <p class="paragraphe-sous-titre">
      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
      elementum tristique.
    </p>
    <div class="ajouter-une-ligne">
      <a href="#" class="tick w-inline-block"></a>
      <div>Accusé de réception</div>
      <a href="#" class="edit w-inline-block"></a><a href="#" class="bin w-inline-block"></a>
    </div>
    <div class="ajouter-une-ligne">
      <a href="#" class="tick w-inline-block"></a>
      <div>Déclencheur d'email 1</div>
      <a href="#" class="edit w-inline-block"></a><a href="#" class="bin w-inline-block"></a>
    </div>
    <div class="w-form">
      <form id="email-form" name="email-form" data-name="Email Form">
        <div class="container-flexbox-choisir-ou-plus w-clearfix">
          <select
            id="D-clencheur-Email"
            name="D-clencheur-Email"
            data-name="Déclencheur Email"
            class="dropdown-toggle"
            ><option value="">{{ ChooseEmailTrigger }}</option
            ><option value="Déclencheur d'email 2">Déclencheur d'email 2</option
            ><option value="Déclencheur d'email 3">Déclencheur d'email 3</option
            ><option value="Déclencheur d'email 4">Déclencheur d'email 4</option></select
          ><button class="plus w-inline-block">+</button>
        </div>
      </form>
    </div>
    <div class="icon-title email"></div>
  </div>
</template>

<script>
export default {
  name: "addEmail",

  props: {
    funnelCategorie: String
  },

  data() {
    return {
      ChooseEmailTrigger: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSE_EMAIL_TRIGGER")
    };
  }
};
</script>
