<template>
  <div id="form-builder-documents">
    <span class="form-builder-title">Tous les documents</span>
    <div
        v-for="document in documents"
        :key="document.id"
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
    }
  }
}
</script>

<style lang="scss">
#form-builder-documents {
  p {
    cursor: pointer;
    margin: 15px 0;
    font-weight: 400;
    font-size: 14px;
    line-height: 18px;
  }
}
</style>