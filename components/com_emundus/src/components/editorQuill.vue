<template>
  <div>
    <div :class="'editor_'+this.$attrs.id"></div>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */

/* IMPORT YOUR SERVICES */

import tinymce from "tinymce";
import client from "../services/axiosClient";
import axios from "axios";

export default {
  name: "editorQuill",
  components: {},
  props: {
    placeholder: String,
    text: String,
    enable_variables: {
      type: Boolean,
      default: false
    },
    limit: {
      type: Number,
      default: null
    },
    toolbar: {
      type: String,
      default: 'complete'
    }
  },
  data: () => ({
    editor: null,
    toolbarOptions: {
      complete: [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote'],
        ['link'],

        [{'header': 1}, {'header': 2}],
        [{'list': 'ordered'}, {'list': 'bullet'}],
        [{'indent': '-1'}, {'indent': '+1'}],
        [{'size': ['small', false, 'large', 'huge']}],

        [{'color': []}],
        [{'font': []}],
        [{'align': []}],
      ],
      light: [
        ['bold', 'italic', 'underline', 'strike'],
      ]
    }
  }),
  mounted() {
    axios({
      method: "get",
      url: "index.php?option=com_emundus&controller=settings&task=geteditorvariables"
    }).then(response => {
      this.variables = response.data.data;
    });

    var options = {
      modules: {
        toolbar: this.toolbarOptions[this.$props.toolbar],
        imageResize: {},
        mention: null
      },
      placeholder: this.$props.placeholder,
      theme: 'snow'
    };

    if(this.$props.enable_variables){
      options.modules.mention = {
        allowedChars: /^[A-Za-z\sÅÄÖåäö]*$/,
        mentionDenotationChars: ["/"],
        source: (searchTerm, renderList, mentionChar) => {
          let values;

          if (mentionChar === "/") {
            values = this.variables;
          }

          if (searchTerm.length === 0) {
            renderList(values, searchTerm);
          } else {
            const matches = [];
            for (let i = 0; i < values.length; i++)
              if (
                  ~values[i].value.toLowerCase().indexOf(searchTerm.toLowerCase())
              )
                matches.push(values[i]);
            renderList(matches, searchTerm);
          }
        },
        onSelect: (item, insertItem) => {
          insertItem(
              {
                denotationChar: "",
                id: item.id,
                value: "[" + item.value + ']',
              },
              true
          );
        },
        renderItem: (item, searchTerm) => {
          return `<div><p>${item.value}</p><p class="em-font-size-12">${item.description}</p></div>`;
        }
      }
    }

    this.editor = new Quill('.editor_'+this.$attrs.id, options);
    if (this.text !== '' && this.text !== null && typeof this.text !== 'undefined') {
      let delta = this.editor.clipboard.convert(this.text);
      this.editor.setContents(delta);
    }

    this.editor.on('editor-change', (eventName, ...args) => {
      if (eventName === 'text-change') {
        if(this.$props.limit){
          if (this.editor.getLength() > this.$props.limit) {
            this.editor.deleteText(this.$props.limit, this.editor.getLength());
          }
        }
        if(this.editor.root.innerHTML === null){
          this.editor.root.innerHTML = '';
        }
        this.$emit("input", this.editor.root.innerHTML);
      }
    });

    this.editor.on('selection-change', (range, oldRange, source) => {
      if (range) {
        if (range.length) {
          //...
        } else {
          this.$emit('focusout');
        }
      }
    });

    var toolbar = this.editor.getModule('toolbar');
    toolbar.addHandler('image', this.imageHandler);
  },
  methods: {
    async imageHandler() {
      const input = document.createElement('input');

      input.setAttribute('type', 'file');
      input.setAttribute('accept', 'image/*');
      input.click();

      input.onchange = async () => {
        var file = input.files[0];
        var formData = new FormData();

        formData.append('image', file);

        var fileName = file.name;

        const res = await this.uploadFiles(file, fileName);
      }
    },
    async uploadFiles(file, fileName) {
      try {
        const formData = new FormData();
        formData.append('image', file);

        await client().post(`index.php?option=com_emundus&controller=settings&task=uploadimagetocustomfolder`,
            formData,
            {
              headers: {
                'Content-Type': 'multipart/form-data'
              }
            }
        ).then((response) => {
          const range = this.editor.getSelection();

          this.editor.insertEmbed(range.index, 'image', response.data.file);
        });
      } catch (e) {
        return {
          status: false,
          msg: e.message
        };
      }
    }
  }
}
</script>

<style>
.ql-toolbar {
  border-top-left-radius: var(--em-form-br);
  border-top-right-radius: var(--em-form-br);
  background-color: white;
}

.ql-container {
  border-bottom-left-radius: var(--em-form-br);
  border-bottom-right-radius: var(--em-form-br);
  background-color: white;
  height: 90%;
}

.ql-snow .ql-editor p {
  line-height: 160%;
}

.ql-mention-list{
  overflow-y: scroll !important;
  max-height: 250px;
}

.ql-mention-list-item {
  padding: 8px !important;
}

.editor_campResume{
  max-height: 85px;
}
</style>
