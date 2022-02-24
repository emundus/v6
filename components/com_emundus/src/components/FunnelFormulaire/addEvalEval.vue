<template>
  <div class="container-evaluation">
    <h2 class="heading">{{ funnelCategorie[0] }}</h2>
    <p class="paragraphe-sous-titre">
      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
      elementum tristique.
    </p>
    <div class="ajouter-une-ligne">
      <a href="#" class="waiting-for-everyone w-inline-block"></a>
      <div>Groupe d'évaluateur 1</div>
      <a href="#" class="bin w-inline-block"></a>
    </div>
    <div class="eval-detail">
      <a href="#" class="tick w-inline-block"></a>
      <div>John Doe</div>
      <a href="#" class="minus w-inline-block"></a><a href="#" class="edit w-inline-block"></a>
      <div class="text-block-7">Dernier envoi : 06/08/2019</div>
      <a href="#" class="resend w-inline-block" data-ix="hover-date"></a>
    </div>
    <div class="eval-detail">
      <a href="#" class="tick w-inline-block"></a>
      <div>Benjamin Rivaland</div>
      <a href="#" class="minus w-inline-block"></a><a href="#" class="edit w-inline-block"></a
      ><a href="#" class="resend w-inline-block" data-ix="hover-date"></a>
      <div class="text-block-7">Dernier envoi : 06/08/2019</div>
    </div>
    <div class="eval-detail">
      <a href="#" class="tick w-inline-block"></a>
      <div>Tom Bach</div>
      <a href="#" class="minus w-inline-block"></a><a href="#" class="edit w-inline-block"></a
      ><a href="#" class="resend w-inline-block" data-ix="hover-date"></a>
      <div class="text-block-7">Dernier envoi : 06/08/2019</div>
    </div>
    <div class="eval-detail">
      <a href="#" class="tick-pending w-inline-block"></a>
      <div>Claude François</div>
      <div class="statut-invitation">en attente de confirmation</div>
      <a href="#" class="minus w-inline-block"></a
      ><a href="#" class="resend w-inline-block" data-ix="hover-date"></a
      ><a href="#" class="edit w-inline-block"></a>
      <div class="text-block-7">Dernier envoi : 06/08/2019</div>
    </div>
    <div class="ajouter-une-ligne">
      <a href="#" class="tick w-inline-block"></a>
      <div>Groupe d'évaluateur 2</div>
      <a href="#" class="bin w-inline-block"></a>
    </div>
    <div class="eval-detail">
      <a href="#" class="tick w-inline-block"></a>
      <div>Will Smith</div>
      <a href="#" class="minus w-inline-block"></a><a href="#" class="edit w-inline-block"></a
      ><a href="#" class="resend w-inline-block" data-ix="hover-date"></a>
      <div class="text-block-7">Dernier envoi : 06/08/2019</div>
    </div>
    <div class="eval-detail">
      <a href="#" class="tick w-inline-block"></a>
      <div>Peter Pan</div>
      <a href="#" class="minus w-inline-block"></a><a href="#" class="edit w-inline-block"></a
      ><a href="#" class="resend w-inline-block" data-ix="hover-date"></a>
      <div class="text-block-7">Dernier envoi : 06/08/2019</div>
    </div>
    <div class="eval-detail">
      <a href="#" class="tick w-inline-block"></a>
      <div>Vincent Cassel</div>
      <a href="#" class="minus w-inline-block"></a><a href="#" class="edit w-inline-block"></a
      ><a href="#" class="resend w-inline-block" data-ix="hover-date"></a>
      <div class="text-block-7">Dernier envoi : 06/08/2019</div>
    </div>
    <div class="w-form">
      <form id="email-form" name="email-form" data-name="Email Form">
        <div class="container-flexbox-choisir-ou-plus w-clearfix">
          <select
            id="Groupe-d-valuateur"
            name="Groupe-d-valuateur"
            data-name="Groupe d'évaluateur"
            class="dropdown-toggle w-select"
            ><option value="">Choisir un groupe d'évaluateur</option
            ><option value="Groupe d'évaluateur 1">Groupe d'évaluateur 1</option
            ><option value="Groupe d'évaluateur 2">Groupe d'évaluateur 2</option
            ><option value="Groupe d'évaluateur 3">Groupe d'évaluateur 3</option></select
          ><a href="#" class="plus w-inline-block"><div class="text-block">+</div></a>
        </div>
      </form>
    </div>
    <div class="icon-title equipe"></div>
  </div>
</template>

<script>
export default {
  name: "addEvalEval",

  props: {
    funnelCategorie: Object
  },

  data() {
    return {};
  },

  methods: {},

  created() {}
};
</script>
