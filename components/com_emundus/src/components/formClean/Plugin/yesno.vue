<template>
  <div id="yesnoF">
    <div class="row rowmodal">
      <!--<div class="form-group">
        <label>{{fieldtype}} :</label>
        <select v-model="element.params.password" id='selectIdTest' class="dropdown-toggle" :disabled="files != 0 && element.params.password == 6">
          <option value="0">{{textfield}}</option>
          <option value="2">{{phonefield}}</option>
          <option value="3">{{emailfield}}</option>
          <option value="6" v-if="files == 0 || (files != 0 && element.params.password == 6)">{{numberfield}}</option>
        </select>
      </div>
      <div class="form-group">
        <label>{{placeholdertext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.placeholder" />
      </div>
      <div class="form-group">
        <label>{{helptext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.rollover" />
      </div>
      <div class="form-group">
        <label>{{maxlength}} :</label>
        <input type="number" min="1" class="form__input field-general w-input" v-model="element.params.maxlength" />
      </div>

            v-show - shox this div when option 4 is checked
      <div class="form-group" v-if="element.params.password == 0">
        <label>{{inputmasktitle}}</label>
        <input type="text" id="format" class="form__input field-general w-input" v-model="element.params.text_input_mask" :placeholder="[[ inputmaskplaceholder ]]"/>
        <label style="font-size: small">{{tipData}}</label>
      </div>
    </div>-->
    </div>
  </div>
</template>

<script>
export default {
name: "yesnoF"
}
</script>

<style scoped>

</style>
