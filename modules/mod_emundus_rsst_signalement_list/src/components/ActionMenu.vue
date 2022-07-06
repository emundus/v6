<template>
  <div>
    <div class="container-2 w-container" style="max-width: unset">
      <transition :name="'slide-down'" type="transition">
        <div>
            <nav aria-label="action" class="em-flex-col-start">
              <a>
               Action 2
              </a>
              <a >
                Action 2
              </a>

            </nav>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
  export default {
    name: "ActionMenu",
  };
</script>

<style lang="scss" scoped>
  div nav a:hover {
    cursor: pointer;
  }

  .action-submenu {
    padding: 7px !important;
    background-color: transparent;
    -webkit-transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
    transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
    font-size: 12px;
    color: black;
    font-family: Lato, 'Helvetica Neue', Arial, Helvetica, sans-serif !important;
    &:hover {
     color: #16AFE1;
   }
  }
</style>
