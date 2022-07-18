<template>
  <tr class="file-row" :key="file.id" @click="$emit('open-modal',file)">
    <td class="td-file">
      <span>{{ file.fnum }}</span>
    </td>
    <td class="td-file">
      <span>{{ file.applicant_name }}</span>
    </td>
    <td v-for="column in elements" v-if="column.show_in_list_summary == 1" class="td-file">
      <span>{{ file[column.name] }}</span>
    </td>
    <td>
      <div class="em-w-100 em-flex-row" style="justify-content: center;">
        <span v-if="file.time_date != ''" class="material-icons">done</span>
      </div>
    </td>
  </tr>
</template>

<script>
export default {
  name: "EvaluationRow",
  props: {
    file: {
      type: Object,
      required: true,
    },
    elements: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    columns: [],
  }),
}
</script>

<style scoped lang="scss">
.file-row {
  border-bottom: 1px solid #e0e0e0;

  &:hover:not(.checked) {
    background-color: #f2f2f3 !important;
  }

  &.checked {
    background-color: #f0f6fd !important;
  }

  .td-file {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
  }
}
</style>
