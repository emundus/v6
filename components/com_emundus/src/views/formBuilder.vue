<template>
  <div :id="'formBuilder'">
    <modal
        :name="'formBuilder'"
        height="auto"
        transition="fade"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
    >
      <header class="em-flex-row em-flex-space-between">
        <div class="right-actions">
          <span class="material-icons">
            navigate_before
          </span>
        </div>
        <span class="em-h4">{{ title }}</span>
        <div class="left-actions em-flex-row em-flex-space-between">
          <span class="material-icons">
            save
          </span>
          <span>&#124;</span>
          <span class="material-icons">
            visibility
          </span>
          <button class="em-primary-button publish">Publier</button>
        </div>
      </header>
      <div class="body em-flex-row em-flex-space-between">
        <aside class="left-panel em-flex-column">
          <form-builder-elements></form-builder-elements>
        </aside>
        <section class="em-flex-column">
          <form-builder-page
              v-if="currentPage"
              :key="currentPage.id"
              :profile_id="profile_id"
              :page="currentPage"
          ></form-builder-page>
        </section>
        <aside class="right-panel em-flex-column">
          <form-builder-pages
              :pages="pages"
              :selected="selectedPage"
              @select-page="selectedPage = $event"
          ></form-builder-pages>
          <hr>
          <form-builder-documents
              :profile_id="profile_id"
              :campaign_id="campaign_id"
          ></form-builder-documents>
        </aside>
      </div>
    </modal>
  </div>
</template>

<script>
// components
import FormBuilderElements  from "../components/FormBuilder/FormBuilderElements";
import FormBuilderPage      from "../components/FormBuilder/FormBuilderPage";
import FormBuilderPages     from "../components/FormBuilder/FormBuilderPages";
import FormBuilderDocuments from "../components/FormBuilder/FormBuilderDocuments";

// services
import formService from '../services/form.js';

export default {
  name: 'FormBuilder',
  components: {
    FormBuilderElements,
    FormBuilderPage,
    FormBuilderPages,
    FormBuilderDocuments
  },
  data() {
    return {
      profile_id: 0,
      campaign_id: 0,
      title: '',
      pages: [],
      selectedPage: 0,
    }
  },
  created() {
    this.profile_id = this.$store.state.global.datas.prid.value;
    this.campaign_id = this.$store.state.global.datas.cid.value;
    this.getFormTitle();
    this.getPages();
  },
  mounted() {
    this.$modal.show('formBuilder');
  },
  methods: {
    getFormTitle() {
      formService.getProfileLabelByProfileId(this.profile_id).then(response => {
        if (response.status !== false) {
          this.title = response.data.data.label;
        }
      });
    },
    getPages() {
      formService.getFormsByProfileId(this.profile_id).then(response => {
        this.pages = response.data.data;
        this.selectedPage = this.pages[0].id;
      });
    },
  },
  computed: {
    currentPage() {
      return this.pages.find(page => page.id === this.selectedPage);
    }
  }
}
</script>

<style lang="scss">
#formBuilder {
  width: 100%;
  height: 100%;
  background: white;

  header {
    box-shadow: inset 0px -1px 0px #E3E5E8;

    button {
      margin: 8px 16px;
      height: 32px;
    }
  }

  .body {
    height: calc(100% - 48px);

    aside, section {
      height: 100%;
      justify-content: flex-start;
    }

    section {
      width: 100%;
      overflow-y: auto;
    }

    .right-panel {
      width: 288px;
      padding: 16px;
      border-left: solid 1px #E3E5E8;

      div {
        width: 100%;
      }
    }

    .left-panel {
      width: 336px;
      padding: 16px;
      border-right: solid 1px #E3E5E8;
    }

    section {
      background: #f8f8f8;
    }

    .form-builder-title {
      font-weight: 700;
      font-size: 16px;
      line-height: 19px;
      letter-spacing: 0.0015em;
      color: #080C12;
    }
  }
}
</style>