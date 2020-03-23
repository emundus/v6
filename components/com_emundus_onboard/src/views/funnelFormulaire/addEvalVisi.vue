<template>
  <div>
    <div class="column-menu w-col w-col-3 menu-brighter">
      <div class="container-menu-funnel">
        <div class="container-nom-formulaire">
          <div class="title-formulaire">FORMULAIRE&nbsp;1</div>
        </div>
        <div class="divider-menu"></div>
        <div class="div-block-2">
          <div class="w-row">
            <div class="w-col w-col-10">
              <a href="#" class="menu-item">Informations générales</a>
            </div>
            <div class="w-col w-col-2"><a href="#" class="partiel w-inline-block"></a></div>
          </div>
        </div>
        <div class="div-block-2">
          <div class="w-row">
            <div class="w-col w-col-10"><a href="#" class="menu-item">Documents</a></div>
            <div class="w-col w-col-2"><a href="#" class="not-visible w-inline-block"></a></div>
          </div>
        </div>
      </div>
    </div>

    <div
      class="column-funnel brighter w-col w-col-9"
      style="width: 110% !important; margin-left: 16% !important; padding-left: 5%;"
    >
      <div class="container-evaluation">
        <h2 class="heading">{{ funnelCategorie[0] }}</h2>
        <p class="paragraphe-sous-titre">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
          elementum tristique.
        </p>
        <div class="icon-title evaluation"></div>
      </div>
      <div class="container-in-funnel w-container inforsPersos">
        <div class="container-evaluation">
          <h2 class="heading">{{ funnelCategorie[1] }}</h2>
          <div class="container-sous-categorie">
            <h3 class="heading-3">{{ funnelCategorie[2] }}</h3>
            <a href="#" class="partiel absolute-position w-inline-block"></a>
          </div>
          <div class="container-content-sous-categorie">
            <div class="w-row">
              <div class="column-3 w-col w-col-1"></div>
              <div class="w-col w-col-11">
                <div class="ajouter-une-ligne">
                  <a href="#" class="visible absolute-position w-inline-block"></a>
                  <div>Nom</div>
                </div>
              </div>
            </div>
            <div class="w-row">
              <div class="column-3 w-col w-col-1"></div>
              <div class="w-col w-col-11">
                <div class="ajouter-une-ligne">
                  <div>Prénom</div>
                  <a href="#" class="visible absolute-position w-inline-block"></a>
                </div>
              </div>
            </div>
            <div class="w-row">
              <div class="column-3 w-col w-col-1"></div>
              <div class="w-col w-col-11">
                <div class="ajouter-une-ligne">
                  <a href="#" class="not-visible absolute-position w-inline-block"></a>
                  <div>Email</div>
                </div>
              </div>
            </div>
          </div>
          <div class="icon-title espace-candidature-formulaire"></div>
        </div>
        <div class="container-evaluation"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "addEvalVisi",

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
