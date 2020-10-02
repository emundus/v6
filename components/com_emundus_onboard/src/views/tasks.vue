<template>

</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

export default {
name: "tasks",
  data: () => ({
    openTasks: true,
  }),
  created() {
    axios({
      method: "GET",
      url: "index.php?option=com_emundus_onboard&controller=settings&task=gettasks"
    }).then(response => {
      let html = "<div id='open-tasks' style='display: none' onclick=\"document.getElementsByClassName('task-container')[0].style.transform = 'translateY(0)';document.getElementById('close-tasks').style.display = 'block';document.getElementById('open-tasks').style.display = 'none'\"><i class='fas fa-plus'></i></div>" +
          "<div id='close-tasks' onclick=\"document.getElementsByClassName('task-container')[0].style.transform = 'translateY(80%)';document.getElementById('open-tasks').style.display = 'block';document.getElementById('close-tasks').style.display = 'none'\"><i class='fas fa-minus'></i></div>" +
          "<ul id='tasks_list'>";

      Object.keys(response.data.params).forEach((param) => {
        switch (param){
          case 'first_campaign':
            if(response.data.params[param]) {
              html += "<li><div class='col-md-2'><i class='far fa-times-circle' style='color: darkred'></i></div><a href='configuration-campaigns?view=campaign&layout=add'><span>" + Joomla.JText._("COM_EMUNDUS_ONBOARD_TUTORIAL_CAMPAIGN") + "</span></a></li><hr>";
            } else {
              html += "<li><div class='col-md-2'><i class='far fa-check-circle' style='color: green'></i></div><a href='configuration-campaigns?view=campaign&layout=add'><span>" + Joomla.JText._("COM_EMUNDUS_ONBOARD_TUTORIAL_CAMPAIGN") + "</span></a></li><hr>";
            }
            break;
          case 'first_form':
            if(response.data.params[param]) {
              html += "<li><div class='col-md-2'><i class='far fa-times-circle' style='color: darkred'></i></div><a href='configuration-forms?view=form&layout=add'><span>" + Joomla.JText._("COM_EMUNDUS_ONBOARD_TUTORIAL_FORM") + "</span></a></li><hr>";
            } else {
              html += "<li><div class='col-md-2'><i class='far fa-check-circle' style='color: green'></i></div><a href='configuration-forms?view=form&layout=add'><span>" + Joomla.JText._("COM_EMUNDUS_ONBOARD_TUTORIAL_FORM") + "</span></a></li><hr>";
            }
            break;
          case 'first_formbuilder':
            if(response.data.params[param]) {
              html += "<li><div class='col-md-2'><i class='far fa-times-circle' style='color: darkred'></i></div> <span>" + Joomla.JText._("COM_EMUNDUS_ONBOARD_TUTORIAL_FORMBUILDER") + "</span> </li><hr>";
            } else {
              html += "<li><div class='col-md-2'><i class='far fa-check-circle' style='color: green'></i></div> <span>" + Joomla.JText._("COM_EMUNDUS_ONBOARD_TUTORIAL_FORMBUILDER") + "</span> </li><hr>";
            }
            break;
          case 'first_documents':
            if(response.data.params[param]) {
              html += "<li><div class='col-md-2'><i class='far fa-times-circle' style='color: darkred'></i></div> <span>" + Joomla.JText._("COM_EMUNDUS_ONBOARD_TUTORIAL_DOCUMENTS") + "</span> </li><hr>";
            } else {
              html += "<li><div class='col-md-2'><i class='far fa-check-circle' style='color: green'></i></div> <span>" + Joomla.JText._("COM_EMUNDUS_ONBOARD_TUTORIAL_DOCUMENTS") + "</span> </li><hr>";
            }
            break;
          case 'first_program':
            if(response.data.params[param]) {
              html += "<li><div class='col-md-2'><i class='far fa-times-circle' style='color: darkred'></i></div> <span>" + Joomla.JText._("COM_EMUNDUS_ONBOARD_TUTORIAL_PROGRAM") + "</span> </li><hr>";
            } else {
              html += "<li><div class='col-md-2'><i class='far fa-check-circle' style='color: green'></i></div> <span>" + Joomla.JText._("COM_EMUNDUS_ONBOARD_TUTORIAL_PROGRAM") + "</span> </li><hr>";
            }
            break;
        }
      });

      setTimeout(() => {
        document.getElementById("close-tasks").click();
      },10000);

      html += "</ul>";
      Swal.fire({
        position: 'bottom-end',
        backdrop: false,
        title: 'Progression',
        html: html,
        customClass: {
          container: 'task-container',
          title: 'task-title',
          content: 'task-content'
        },
        showConfirmButton: false
      })
    });
  },
  methods: {

  },

  watch: {
    openTasks: function(){
      document.getElementsByClassName('task-content')[0].style.display = 'none';
    }
  }
}
</script>

<style scoped>

</style>
