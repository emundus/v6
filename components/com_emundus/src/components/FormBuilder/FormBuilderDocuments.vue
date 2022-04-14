<template>
  <div id="form-builder-documents" class="em-p-16">
    <div id="form-builder-title" class="em-flex-row em-flex-space-between">
      <span>Tous les documents</span>
      <span
           class="material-icons"
          @click="createDocument"
      >
        add
      </span>
    </div>
    <div
        v-for="document in documents"
        :key="document.id"
        @click="$emit('show-documents')"
    >
      <p>{{ document.label }}</p>
    </div>
  </div>
</template>

<script>
import formService from '../../services/form.js';

export default {
  name: 'FormBuilderDocuments',
  props: {
    profile_id: {
      type: Number,
      required: true
    },
    campaign_id: {
      type: Number,
      required: true
    },
  },
  data () {
    return {
      documents: [],
    }
  },
  created () {
    this.getDocuments();
  },
  methods: {
    getDocuments () {
      formService.getDocuments(this.profile_id).then(response => {
        this.documents = response.data.data;
      });
    },
    createDocument() {
      this.$emit('open-create-document');
    },
  }
}
</script>

<style lang="scss">
#form-builder-documents {
  #form-builder-title {
    margin-top: 0;
    font-weight: 700;
    font-size: 16px;
    line-height: 19px;
    letter-spacing: .0015em;
    color: #080c12;
  }

  p {
    cursor: pointer;
    margin: 15px 0;
    font-weight: 400;
    font-size: 14px;
    line-height: 18px;
  }
}
</style>