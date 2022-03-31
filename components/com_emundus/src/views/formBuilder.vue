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
          <span
              id="go-back"
              class="material-icons em-p-12-16"
              @click="goTo('/formulaires')"
          >
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
        <aside class="left-panel em-flex-row em-flex-start">
          <div class="tabs em-flex-column em-flex-start">
            <div class="tab" v-for="(tab, index) in leftPanel.tabs" :key="index" :class="{ active: tab.active }">
              <span

                  class="material-icons"
                  @click="tab.url ? goTo(tab.url, 'blank') : selectTab(index)">
                {{ tab.icon }}
              </span>
            </div>
          </div>
          <div class="tab-content em-flex-start">
            <form-builder-elements
                v-if="leftPanelActiveTab === 'Elements'"
                @drag-end="onDragElementEnd"
                @element-created="onElementCreated"
            ></form-builder-elements>
          </div>
        </aside>
        <section class="em-flex-column">
          <form-builder-page
              ref="formBuilderPage"
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
              :profile_id="profile_id"
              @select-page="selectedPage = $event"
              @add-page="getPages"
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
      leftPanel: {
        tabs: [
          {
            title: 'Elements',
            icon: 'edit_note',
            active: true
          },
          {
            title: 'Translations',
            icon: 'translate',
            active: false,
            url: '/parametres-globaux'
          },
        ],
      },
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
    onDragElementEnd(event) {
    },
    onElementCreated() {
      // refresh form-builder-page sub component

      this.$refs.formBuilderPage.getSections();
    },
    selectTab(index) {
      // unset selected tab
      this.leftPanel.tabs.forEach(tab => {
        tab.active = false;
      });
      // set selected tab
      this.leftPanel.tabs.forEach((tab, i) => {
        tab.active = (i === index);
      });
    },
    goTo(url, blank = false) {
      const baseUrl = window.location.origin;

      if(blank) {
        window.open(baseUrl + url, '_blank');
      } else {
        window.location.href = baseUrl + url;
      }
    }
  },
  computed: {
    currentPage() {
      return this.pages.find(page => page.id === this.selectedPage);
    },
    leftPanelActiveTab() {
      return this.leftPanel.tabs.find(tab => tab.active).title;
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

    #go-back {
      cursor: pointer;
    }

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
      padding: 0;
      border-right: solid 1px #E3E5E8;
      align-self: flex-start;

      .tabs {
        align-items: flex-start;
        border-right: solid 1px #E3E5E8;

        .tab {
          cursor: pointer;

          &.active {
            background-color: #f8f8f8;
          }

          .material-icons {
            padding: 16px;
            font-size: 22px !important;
          }
        }
      }

      .tab-content {
        align-items: flex-start;
        padding: 0 16px;
        height: 100%;
      }
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