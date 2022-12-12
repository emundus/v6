<template>
  <div>
    <div class="editor"></div>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */

/* IMPORT YOUR SERVICES */

import tinymce from "tinymce";

export default {
  name: "editorQuill",
  components: {},
  props: {
    placeholder: String,
    text: String
  },
  data: () => ({
    editor: null,
    toolbarOptions: [
      ['bold', 'italic', 'underline', 'strike'],
      ['blockquote'],
      ['image','link'],

      [{ 'header': 1 }, { 'header': 2 }],
      [{ 'list': 'ordered'}, { 'list': 'bullet' }],
      [{ 'indent': '-1'}, { 'indent': '+1' }],
      [{ 'size': ['small', false, 'large', 'huge'] }],

      [{ 'color': [] }],
      [{ 'font': [] }],
      [{ 'align': [] }]
    ]
  }),
  mounted() {
    var options = {
      modules: {
        toolbar: this.toolbarOptions,
        imageResize: {},
        imageDrop: true
      },
      placeholder: this.$props.placeholder,
      theme: 'snow'
    };

    this.editor = new Quill('.editor', options);
    if(this.text !== '' && this.text !== null && typeof this.text !== 'undefined') {
      let delta = this.editor.clipboard.convert(this.text);
      this.editor.setContents(delta);
    }

    this.editor.on('editor-change', (eventName, ...args) => {
      if (eventName === 'text-change') {
        this.$emit("input", this.editor.root.innerHTML);
      }
    });

    this.editor.on('selection-change', (range, oldRange, source) => {
      if(range) {
        if (range.length) {
          //...
        } else {
          this.$emit('focusout');
        }
      }
    });
  },
  methods: {}
}
</script>

<style>
.ql-toolbar{
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
  background-color: white;
}
.ql-container{
  border-bottom-left-radius: 8px;
  border-bottom-right-radius: 8px;
  background-color: white;
  height: 90%;
}
.ql-snow .ql-editor h1{
  margin-bottom: 25px;
}
.ql-snow .ql-editor p{
  line-height: 160%;
}
</style>
