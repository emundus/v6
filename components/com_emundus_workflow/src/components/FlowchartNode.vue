<template>
  <div class="flowchart-node" :style="nodeStyle" 
    @mousedown="handleMousedown"
    @mouseover="handleMouseOver"
    @mouseleave="handleMouseLeave"
    v-bind:class="{selected: options.selected === id}">
    <div class="node-port node-input"
       @mousedown="inputMouseDown"
       @mouseup="inputMouseUp">
    </div>
    <div class="node-main">
      <div v-text="type" class="node-type"></div>
      <p contenteditable="true" class="node-label" :id="'label_'+id" v-text="label"/>
    </div>
    <div class="node-port node-output" 
      @mousedown="outputMouseDown">
    </div>
    <div v-show="show.delete" class="node-delete">&times;</div>
    <div v-show="show.clone" class="duplicate-option" :id="id" ref="duplicate">Dupliquer</div>
    <div class="configuration" :id="id" ref="configuration">Configurer</div>
  </div>
</template>

<script>
import {DateTime as LuxonDateTime} from "luxon";
import axios from "axios";
const qs = require('qs');

export default {
  name: 'FlowchartNode',
  props: {
    background: {
      type: String,
      default: '',
    },
    id: {
      type: Number,
      default: 1000,
      validator(val) {
        return typeof val === 'number'
      }
    },
    x: {
      type: Number,
      default: 0,
      validator(val) {
        return typeof val === 'number'
      }
    },    
    y: {
      type: Number,
      default: 0,
      validator(val) {
        return typeof val === 'number'
      }
    },
    type: {
      type: String,
      default: 'Element'
    },
    label: {
      type: String,
      default: 'input name'
    },
    options: {
      type: Object,
      default() {
        return {
          centerX: 1024,
          scale: 1,
          centerY: 140,
        }
      }
    }
  },
  data() {
    return {
      show: {
        delete: false,
        clone: false,
      }
    }
  },
  mounted() {
  },
  computed: {
    nodeStyle() {
      return {
        top: this.options.centerY + this.y * this.options.scale + 'px', // remove: this.options.offsetTop + 
        left: this.options.centerX + this.x * this.options.scale + 'px', // remove: this.options.offsetLeft + 
        transform: `scale(${this.options.scale})`,
      }
    }
  },
  methods: {
    handleMousedown(e) {
      const target = e.target || e.srcElement;
      // console.log(target);
      if (target.className.indexOf('node-input') < 0 && target.className.indexOf('node-output') < 0) {
        this.$emit('nodeSelected', e);
      }
    },
    handleMouseOver() {
      this.show.delete = true;
      this.show.clone = true;
    },
    handleMouseLeave() {
      this.show.delete = false;
      this.show.clone = false;
    },
    outputMouseDown(e) {
      this.$emit('linkingStart')
      e.preventDefault();
    },
    inputMouseDown(e) {
      e.preventDefault();
    },
    inputMouseUp(e) {
      this.$emit('linkingStop')
      e.preventDefault();
    },

    handleDuplicatItem(e) {
      this.$emit('duplicateItem');
      e.preventDefault();
    },

    handleConfigure(e) {
      this.$emit('configureItem');
      e.preventDefault();
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.flowchart-node {
  margin: 0;
  width: 80px;
  height: 80px;
  position: absolute;
  box-sizing: border-box;
  border: none;
  background: white;
  z-index: 1;
  opacity: 0.9;
  cursor: move;
  transform-origin: top left;
  /*border-radius: 0.75rem;*/
}
.flowchart-node .node-main {
  text-align: center;
}
.flowchart-node .node-main .node-type {
  background: #f85;
  color: white;
  font-size: 13px;
  padding: 6px;
  /*border-radius: 0.75rem;*/
}
.flowchart-node .node-main .node-label {
  font-size: 13px;
  text-decoration: underline;
  cursor: auto;
}
.flowchart-node .node-port {
  position: absolute;
  width: 12px;
  height: 12px;
  left: 50%;
  transform: translate(-50%);
  border: 1px solid #ccc;
  border-radius: 100px;
  background: white;
}
.flowchart-node .node-port:hover {
  background: #f85;
  border: 1px solid #f85;
}
.flowchart-node .node-input {
  top: -8px;
}
.flowchart-node .node-output {
  bottom: -8px;
}
.flowchart-node .node-delete {
  position: absolute;
  right: -6px;
  top: -6px;
  font-size: 12px;
  width: 12px;
  height: 12px;
  color: #f85;
  cursor: pointer;
  background: white;
  border: 1px solid #f85;
  border-radius: 100px;
  text-align: center;
}
.flowchart-node .node-delete:hover {
  background: #f85;
  color: white;
}

.flowchart-node .duplicate-option {
  position: absolute;
  margin: 20px 60px;
  width: 12px;
  height: 12px;
  color: #ba0f8a;
  cursor: pointer;
  text-align: center;
  font-size: x-small;
}

.flowchart-node .remove-option {
  font-size: small;
  text-align: center;
  width: -moz-fit-content !important;
  height: fit-content;
  display: inline-block;
  background: #d94c4c;
  position: absolute;
  margin: -5px 85px !important;
  cursor: pointer;
  color: white;
  border-radius: 0.25rem !important;
}

.configuration {
  font-size: x-small;
  color: #0010ff;
  text-align: center;
  margin: 0px 5px;
  cursor: pointer;
}

.configuration:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19) !important;
}
.selected {
  box-shadow: 0 0 0 2px #f85;
}
</style>
