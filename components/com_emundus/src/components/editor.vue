<template xmlns="http://www.w3.org/1999/html">
  <div class="editor">
    <editor :id="'tiny_' + selector_id" api-key="auv9u5s193r2jqvljrb4j42v076v2go7ns8r2g7dyhso7h9j" :init="options">
    </editor>
  </div>
</template>

<script>
  import axios from "axios";
  // Import TinyMCE
  import Editor from '@tinymce/tinymce-vue';
  import _ from 'lodash'
  import $ from 'jquery'

export default {
  components: {
    'editor': Editor
  },

  props: {
    text: String,
    lang: String,
    placeholder: String,
    id: String,
    height: String,
    enable_variables: Boolean
  },

  data() {
    return {
      selector_id: this.id + _.random(10000, 99999),
      variables: {},
      data: null,
      baseUrl: window.location.protocol + '//' + window.location.host + '/media/com_emundus_vue/',
      options: {
        plugins: 'lists link media image table'
      },
    };
  },

  methods: {
    saveText(){
			if (typeof Editor.activeEditor !== undefined && Editor.activeEditor !== null) {
				this.$emit("input", Editor.activeEditor.getContent());
			}
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
      url: "index.php?option=com_emundus&controller=settings&task=geteditorvariables"
    }).then(response => {
        this.variables = response.data.data;
    });
    this.baseUrl = window.location.protocol + '//' + window.location.host + '/media/com_emundus_vue/';

    this.options = {
      selector: '#tiny_' + this.selector_id,
      images_upload_url: 'index.php?option=com_emundus&controller=settings&task=uploadimages',
      plugins: 'paste link media preview image code advlist hr emoticons lists searchreplace charmap quickbars pagebreak autolink print mention',
      toolbar: 'undo redo | forecolor bold italic underline strikethrough | fontsizeselect | image preview | alignleft aligncenter alignright alignjustify hr | bullist numlist | outdent indent | insertfile media anchor| charmap emoticons backcolor | searchreplace print',
      fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
      content_css: this.baseUrl + 'skins/ui/oxide/content.min.css',
      convert_urls: false,
      height: this.height,
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
      file_picker_types: 'image',
      paste_data_images: true,
      file_picker_callback: function(cb, value, meta) {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');

        input.onchange = function() {
          var file = this.files[0];
          var reader = new FileReader();

          reader.onload = function () {
            var id = 'blobid' + (new Date()).getTime();
            var blobCache =  Editor.activeEditor.editorUpload.blobCache;
            var base64 = reader.result.split(',')[1];
            var blobInfo = blobCache.create(id, file, base64);
            blobCache.add(blobInfo);

            // call the callback and populate the Title field with the file name
            cb(blobInfo.blobUri(), { title: file.name });
          };
          reader.readAsDataURL(file);
        };

        input.click();
      },
      mentions: {
        delimiter: '/',
        source: (query, process, delimiter) => {
          if (delimiter === '/' && this.enable_variables == true) {
            process(this.variables);
          }
        },
        queryBy: 'tag',
        delay: 200,
        insert: (item) => {
          setTimeout(() => {
            this.$emit("input", Editor.activeEditor.getContent());
          },500);
          return '<span>[' + item.tag + ']</span>';
        },
        render: function(item) {
          return '<li class="email-tags">' +
              '<a href="javascript:;"><span>' + item.tag + '</span><p>' + item.description + '</p></a>' +
              '</li>';
        },
        renderDropdown: function() {
          //add twitter bootstrap dropdown-menu class
          return '<ul class="rte-autocomplete em-autocomplete dropdown-menu"></ul>';
        }
      },
      setup: (editor) => {
        editor.on('keyup', () => {
          this.$emit("input", Editor.activeEditor.getContent());
        });
        editor.on('blur', () => {
          this.$emit("focusout", Editor.activeEditor.getContent());
        });
        editor.on('init', () => {
          Editor.activeEditor.setContent(this.text);
        });
      }
    }

    if(this.lang == 'fr'){
      this.options.language = 'fr_FR';
      this.options.language_url = this.baseUrl + 'languages/fr_FR.js';
      //Editor.init(options);
    } else {
      //Editor.init(options);
    }

    this.$forceUpdate();

    setInterval(this.saveText, 3000);
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
