<template>
  <div>
    <div v-for="translation in translations_rows" class="em-mb-32 em-neutral-100-box em-p-24">
      <div v-for="field in translation" class="em-mb-24">
        <p>{{ field.reference_label.toUpperCase() }}</p>
        <div class="justify-content-between em-mt-16 em-grid-50 em-ml-24">
          <p class="em-neutral-700-color">{{ field.default_lang }}</p>
          <input v-if="field.field_type === 'field'" class="mb-0 em-input" type="text" :value="field.lang_to" />
          <textarea v-if="field.field_type === 'textarea'" class="mb-0 em-input" :value="field.lang_to" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Multiselect from "vue-multiselect";
import mixin from "com_emundus/src/mixins/mixin";

export default {
  name: "TranslationRow",
  components: {
    Multiselect
  },
  mixins: [mixin],
  props: {
    section: Object,
    translations: Array
  },
  data() {
    return {
      translations_rows: {},
      key_fields: [],
    }
  },
  created(){
    this.key_fields = Object.keys(this.$props.section.indexedFields);

    Object.values(this.$props.translations).forEach((translations_reference) => {
      Object.values(translations_reference).forEach((translation) => {
        console.log(translation);
        if(this.key_fields.includes(translation.reference_field) && translation.reference_table === this.$props.section.Name){
          translation.reference_label = this.$props.section.indexedFields[translation.reference_field].Label;
          translation.field_type = this.$props.section.indexedFields[translation.reference_field].Type;
          if(!this.translations_rows.hasOwnProperty(translation.reference_id)) {
            this.translations_rows[translation.reference_id] = [];
          }
          this.translations_rows[translation.reference_id].push(translation);
        }
      })
    })
  }
}
</script>

<style scoped>

</style>
