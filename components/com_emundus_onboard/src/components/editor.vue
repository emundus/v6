<template xmlns="http://www.w3.org/1999/html">
  <div class="editor">
    <div :id="'tiny_' + selector_id">
    </div>
  </div>
</template>

<script>
  import axios from "axios";
  // Import TinyMCE
  import tinymce from 'tinymce/tinymce';
  import _ from 'lodash'
  import $ from 'jquery'

  // Any plugins you want to use has to be imported
  import 'tinymce/plugins/paste';
  import 'tinymce/plugins/link';
  import 'tinymce/plugins/media';
  import 'tinymce/plugins/preview';
  import 'tinymce/plugins/image';
  import 'tinymce/plugins/code';
  import 'tinymce/plugins/anchor';
  import 'tinymce/plugins/advlist';
  import 'tinymce/plugins/hr';
  import 'tinymce/plugins/emoticons';
  import 'tinymce/plugins/searchreplace';
  import 'tinymce/plugins/charmap';

export default {
  components: {
    'editor': tinymce
  },

  props: {
    text: String,
    lang: String,
    placeholder: String,
    id: String
  },

  data() {
    return {
      selector_id: this.id + _.random(10000, 99999),
      variables: {},
      data: null
    };
  },

  methods: {
    saveText(){
      this.$emit("input", tinymce.activeEditor.getContent());
    }
  },

  watch: {
    text: function() {
      this.$forceUpdate();
    }
  },

  mounted() {
    axios({
      method: "get",
      url: "index.php?option=com_emundus_onboard&controller=settings&task=geteditorvariables"
    }).then(response => {
        this.variables = response.data.data;
    });
    var baseUrl = window.location.protocol + '//' + window.location.host + '/media/com_emundus_onboard/';
    var vm = this;

    let options = {
      selector: '#tiny_' + this.selector_id,
      plugins: 'paste link media preview image code anchor advlist hr emoticons lists searchreplace charmap quickbars imagetools pagebreak autolink print mention',
      toolbar: 'undo redo | forecolor bold italic underline strikethrough | fontselect fontsizeselect formatselect | preview | alignleft aligncenter alignright alignjustify hr pagebreak | bullist numlist | outdent indent | link image insertfile media anchor| charmap emoticons backcolor | searchreplace print',
      fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
      content_css: baseUrl + 'skins/ui/oxide/content.min.css',
      height: '30em',
      branding: false,
      elementpath: false,
      statusbar: false,
      menubar: false,
      a11y_advanced_options: false,
      toolbar_sticky: false,
      quickbars_insert_toolbar: '',
      quickbars_selection_toolbar: 'bold italic underline | quicklink h2 h3 blockquote',
      toolbar_mode: 'sliding',
      placeholder: this.placeholder,
      mentions: {
        delimiter: '/',
        source: (query, process, delimiter) => {
          if (delimiter === '/') {
            process(this.variables);
          }
        },
        queryBy: 'tag',
        delay: 200,
        insert: (item) => {
          setTimeout(() => {
            this.$emit("input", tinymce.activeEditor.getContent());
          },500);
          return '<span>[' + item.tag + ']</span>';
        },
        render: function(item) {
          return '<li>' +
              '<a href="javascript:;"><span>' + item.tag + '</span><p>' + item.description + '</p></a>' +
              '</li>';
        },
        renderDropdown: function() {
          //add twitter bootstrap dropdown-menu class
          return '<ul class="rte-autocomplete dropdown-menu"></ul>';
        }
      },
      setup: (editor) => {
        editor.on('keyup', () => {
          this.$emit("input", tinymce.activeEditor.getContent());
        });
        editor.on('init', () => {
          tinymce.activeEditor.setContent(this.text);
        });
      }
    };
    if(this.lang == 'fr'){
      options.language = 'fr_FR';
      options.language_url = baseUrl + 'languages/fr_FR.js';
      tinymce.init(options);
    } else {
      tinymce.init(options);
    }

    this.$forceUpdate();

    //setInterval(this.saveText, 3000);
  },
};
</script>

<style scoped>
.menubar,
.editor__content {
  border: 1px solid #ccc;
  box-sizing: border-box;
}
.editor__content {
  padding: 10px;
}

.menubar .menubar__button {
  background-color: rgb(240, 240, 240);;
  margin-top: 10px;
}

.menubar__button svg{
  padding: 5px;
  height: 30px;
  width: 30px;
}

.menubar__button svg:hover, .heading strong:hover{
  background: #cecece78;
  border-radius: 4px;
  color: black;
  fill: black;
}

div.forms-emails-editor .menubar {
  margin-left: 1px;
}

.menubar {
  margin-top: -1px;
  background-color: rgb(240, 240, 240);;
}

  .redo, .underline, .paragraph, .heading-3-toolbar, .ordered-list, .code-block{
    margin-right: 15px;
  }

  .redo:after, .underline:after, .paragraph:after, .heading-3-toolbar:after, .ordered-list:after, .code-block:after{
    content: '';
    height: 25px;
    width: 1px;
    position: absolute;
    background: #000;
    margin-left: 15px;
  }

  .heading{
    position: relative;
    top: -18px;
  }

  .heading strong{
    position: relative;
    top: 5px;
    padding: 5px;
  }

  .is-active svg, .is-active strong{
    background: #151931;
    border-radius: 4px;
    color: white;
    fill: white
  }

  .menububble{
    opacity: 0;
    position: absolute;
    transition: opacity 0.3s ease-in-out;
    padding: 5px;
    background: #484444;
    border-radius: 4px;
  }

  .menububble__button{
    background: #484444;
    color: white;
    fill: white;
    vertical-align: middle;
  }

.menububble__button svg{
  padding: 5px;
  height: 30px;
  width: 30px;
}

.menububble__button svg:hover{
  background: #cecece78;
  border-radius: 4px;
  color: black;
  fill: black;
}

  .bubble-active{
    opacity: 1 !important;
  }

.mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before{
  opacity: 0.5;
  color: #1B1F3C !important;
}
</style>
