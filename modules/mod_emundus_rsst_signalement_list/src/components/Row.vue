<template>
  <tr class="list-row">
    <td>
      <input type="checkbox" class="em-switch input">
    </td>
    <td>

      FL-500
    </td>
    <td>Le technicien est passé </td>
    <td>10/04/2022</td>
    <td>
      <div class="tag done">
        Fait
      </div>
    </td>
    <td>
      <div class="tag done">
        Fait
      </div>
    </td>
    <td>
			<span >
				<list-action-menu></list-action-menu>
			</span>
    </td>
  </tr>
</template>

<script>
import ListActionMenu from './ListActionMenu.vue';

export default {
  name: "Row",
  components: {
    'list-action-menu' : ListActionMenu
  }
}
</script>

<style scoped lang="scss">
.list-row {

  span.tag {
    margin: 0 8px 8px 0;
    padding: 4px 8px;
    border-radius: 4px;
    color: #080C12;
    height: fit-content;
    background: #F2F2F3;
    box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07),
    0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);

    &.done {
      background: #DFF5E9;
    }

    &.todo {
      color: #ACB1B9;
    }

    &.inprogress {
      color: #FFFFFF;
      background: #080C12;
    }
  }

  span.list-td-label,
  span.list-td-subject {
    cursor: pointer;
    transition: all .3s;

    &:hover {
      color: #20835F;
    }
  }
}

tr.list-row td {
  border-left: 0;
  border-right: 0;
  font-size: 12px;
  padding: 0.85rem 0.5rem;
}

.list-row:hover {
  background: #F2F2F3;
}
</style>

