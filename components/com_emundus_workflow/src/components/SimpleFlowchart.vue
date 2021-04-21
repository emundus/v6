<template>
  <div class="flowchart-container"
       @mousemove="handleMove"
       @mouseup="handleUp"
       @mousedown="handleDown">
    <!--    <svg width="100%" :height="`${height}vh`">-->
    <svg width="100%" :height="`${height}vh`">
      <flowchart-link v-bind.sync="link" v-for="(link, index) in lines" :key="`link${index}`" @deleteLink="linkDelete(link.id)"></flowchart-link>
    </svg>
    <modal-config-element v-for="(node, index) in scene.nodes" :ID="node.id" :element="node" @linkingStart="linkingStart" @linkingStop="linkingStop" @linkDelete="linkDelete"> {{ node.id }}</modal-config-element>
    <flowchart-node v-bind.sync="node" v-for="(node, index) in scene.nodes" :key="`node${index}`" :options="nodeOptions" @linkingStart="linkingStart(node.id)" @linkingStop="linkingStop(node.id)" @nodeSelected="nodeSelected(node.id, $event)" v-bind:style="{ background: node.background }" :params="step"></flowchart-node>
  </div>
</template>

<script>
import FlowchartLink from './FlowchartLink.vue';
import FlowchartNode from './FlowchartNode.vue';
import { getMousePosition } from '../assets/position';
import axios from 'axios';
import Swal from "sweetalert2";
import ModalConfigElement from "../ModalConfigElement";
const qs = require('qs');

export default {
  name: 'VueFlowchart',
  props: {
    step: Object,
    scene: {
      type: Object,
      default() {
        return {
          centerX: 1024,
          scale: 1,
          centerY: 140,
          nodes: [],
          links: [],
          background: '',
        }
      }
    },
    height: {
      type: Number,
      default: 60,
    },
  },
  data() {
    return {
      action: {
        linking: false,
        dragging: false,
        scrolling: false,
        selected: 0,
      },
      mouse: {
        x: 0,
        y: 0,
        lastX: 0,
        lastY: 0,
      },
      draggingLink: null,
      rootDivOffset: {
        top: 0,
        left: 0
      },
    };
  },
  components: {
    ModalConfigElement,
    FlowchartLink,
    FlowchartNode,
  },
  computed: {
    nodeOptions() {
      return {
        centerY: this.scene.centerY,
        centerX: this.scene.centerX,
        scale: this.scene.scale,
        offsetTop: this.rootDivOffset.top,
        offsetLeft: this.rootDivOffset.left,
        selected: this.action.selected,
      }
    },
    lines() {
      const lines = this.scene.links.map((link) => {
        const fromNode = this.findNodeWithID(link.from)
        const toNode = this.findNodeWithID(link.to)
        let x, y, cy, cx, ex, ey;
        x = this.scene.centerX + fromNode.x;
        y = this.scene.centerY + fromNode.y;
        [cx, cy] = this.getPortPosition('bottom', x, y);
        x = this.scene.centerX + toNode.x;
        y = this.scene.centerY + toNode.y;
        [ex, ey] = this.getPortPosition('top', x, y);
        return {
          start: [cx, cy],
          end: [ex, ey],
          id: link.id,
        };
      })
      if (this.draggingLink) {
        let x, y, cy, cx;
        const fromNode = this.findNodeWithID(this.draggingLink.from)
        x = this.scene.centerX + fromNode.x;
        y = this.scene.centerY + fromNode.y;
        [cx, cy] = this.getPortPosition('bottom', x, y);
        // push temp dragging link, mouse cursor postion = link end postion
        lines.push({
          start: [cx, cy],
          end: [this.draggingLink.mx, this.draggingLink.my],
        })
      }
      return lines;
    }
  },
  mounted() {
    this.rootDivOffset.top = this.$el ? this.$el.offsetTop : 0;
    this.rootDivOffset.left = this.$el ? this.$el.offsetLeft : 0;
    // console.log(22222, this.rootDivOffset);
  },
  methods: {
    findNodeWithID(id) {
      return this.scene.nodes.find((item) => {
        return id === item.id
      })
    },
    getPortPosition(type, x, y) {
      if (type === 'top') {
        return [x + 40, y];
      }
      else if (type === 'bottom') {
        return [x + 40, y + 80];
      }
    },
    linkingStart(index) {
      this.action.linking = true;
      this.draggingLink = {
        from: index,
        mx: 0,
        my: 0,
      };
    },

    linkingStop(index) {
      // add new Link
      if (this.draggingLink && this.draggingLink.from !== index) {
        // check link existence
        const existed = this.scene.links.find((link) => {
          return link.from === this.draggingLink.from && link.to === index;
        })
        if (!existed) {
          let maxID = Math.max(0, ...this.scene.links.map((link) => {
            return link.id
          }))
          const newLink = {
            from: this.draggingLink.from,
            to: index,
          };

          //axios --> call to the api of create new link --> with params = {from,to,workflow_id}
          //workflow_id --> rewrite the function of get id
          var _links = {
            from: newLink.from,
            to: index,
            workflow_id: this.getWorkflowIdFromURL(),
            link_label: '',
            step_id: this.step.id,
          }

          // axios({
          //   method: 'post',
          //   url: 'index.php?option=com_emundus_workflow&controller=item&task=checkmatchingitems',
          //   headers: {
          //     "Content-Type": "application/x-www-form-urlencoded"
          //   },
          //   data: qs.stringify({
          //     data: _links
          //   })
          // }).then(answer => {
          //   if(answer.data.data == true) {
          axios({
            method: 'post',
            url: 'index.php?option=com_emundus_workflow&controller=item&task=createlink',
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              data: _links
            })
          }).then(response => {
            newLink['id'] = response.data.data;
          }).catch(error => {
            console.log(error);
          })
          this.scene.links.push(newLink)
          this.$emit('linkAdded', newLink)
          //   }
          //   else {
          //     Swal.fire({
          //       icon: 'error',
          //       title: 'Erreur',
          //       html: 'Les status entre les deux items ne sont pas pareils',
          //       timer: 1500,
          //       showConfirmButton:false,
          //     })
          //   }
          // })
        }
      }
      this.draggingLink = null
    },

    // get workflow id from url
    getWorkflowIdFromURL: function () {
      return window.location.href.split('id=')[1];
    },

    linkDelete(id) {
      const deletedLink = this.scene.links.find((item) => {
        return item.id === id;
      });
      if (deletedLink) {
        this.scene.links = this.scene.links.filter((item) => {
          return item.id !== id;
        });
        this.$emit('linkBreak', deletedLink);
        this.$swal('Merci', 'Cette liaison est supprimée', 'success');
        this.deleteLink(id);
      }
    },
    nodeSelected(id, e) {
      this.action.dragging = id;
      this.action.selected = id;
      this.$emit('nodeClick', id);
      this.mouse.lastX = e.pageX || e.clientX + document.documentElement.scrollLeft
      this.mouse.lastY = e.pageY || e.clientY + document.documentElement.scrollTop
    },
    handleMove(e) {
      if (this.action.linking) {
        [this.mouse.x, this.mouse.y] = getMousePosition(this.$el, e);
        [this.draggingLink.mx, this.draggingLink.my] = [this.mouse.x, this.mouse.y];
      }
      if (this.action.dragging) {
        this.mouse.x = e.pageX || e.clientX + document.documentElement.scrollLeft
        this.mouse.y = e.pageY || e.clientY + document.documentElement.scrollTop
        let diffX = this.mouse.x - this.mouse.lastX;
        let diffY = this.mouse.y - this.mouse.lastY;

        this.mouse.lastX = this.mouse.x;
        this.mouse.lastY = this.mouse.y;
        this.moveSelectedNode(diffX, diffY);
      }
      if (this.action.scrolling) {
        [this.mouse.x, this.mouse.y] = getMousePosition(this.$el, e);
        let diffX = this.mouse.x - this.mouse.lastX;
        let diffY = this.mouse.y - this.mouse.lastY;

        this.mouse.lastX = this.mouse.x;
        this.mouse.lastY = this.mouse.y;

        this.scene.centerX += diffX;
        this.scene.centerY += diffY;

        // this.hasDragged = true
      }
    },

    handleUp(e) {
      const target = e.target || e.srcElement;

      // save workflow when mouse up --> drag and drop
      this.scene.nodes.findIndex((item) => {
        if (item.id === this.action.dragging) {
          var _saveNode = {
            id: item.id,
            type: item.type,
            axisX: item.x,
            axisY: item.y,
            style: item.background,
            item_label: document.getElementById('label_' + item.id).innerText,
          }

          axios({
            method: 'post',
            url: 'index.php?option=com_emundus_workflow&controller=item&task=saveworkflow',
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              data: _saveNode
            })
          }).then(response => {
            console.log(response);
          }).catch(error => {
            console.log(error);
          })
        }
      })

      if (this.$el.contains(target)) {
        if (typeof target.className !== 'string' || target.className.indexOf('node-input') < 0) {
          this.draggingLink = null;
        }
        if (typeof target.className === 'string' && target.className.indexOf('node-delete') > -1) {
          this.nodeDelete(this.action.dragging);
        }
        if (typeof target.className === 'string' && target.className.indexOf('duplicate-option') > -1) {
          this.nodeCloned(this.action.dragging);
        }
        if (typeof target.className === 'string' && target.className.indexOf('configuration') > -1) {
          this.nodeConfig(this.action.dragging);
        }
      }
      this.action.linking = false;
      this.action.dragging = null;
      this.action.scrolling = false;
    },

    handleDown(e) {
      const target = e.target || e.srcElement;
      // console.log('for scroll', target, e.keyCode, e.which)
      if ((target === this.$el || target.matches('svg, svg *')) && e.which === 1) {
        this.action.scrolling = true;
        [this.mouse.lastX, this.mouse.lastY] = getMousePosition(this.$el, e);
        this.action.selected = null; // deselectAll
      }
      this.$emit('canvasClick', e);
    },
    moveSelectedNode(dx, dy) {
      let index = this.scene.nodes.findIndex((item) => {
        return item.id === this.action.dragging
      })
      let left = this.scene.nodes[index].x + dx / this.scene.scale;
      let top = this.scene.nodes[index].y + dy / this.scene.scale;
      this.$set(this.scene.nodes, index, Object.assign(this.scene.nodes[index], {
        x: left,
        y: top,
      }));
    },
    nodeDelete(id) {
      this.$emit('nodeDelete', id)
      axios({
        method: 'post',
        url: "index.php?option=com_emundus_workflow&controller=item&task=getitem",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          id: id,
        })
      }).then(response => {
        var _type = (response.data.data)[0];

        if(_type.item_name == 'Initialisation') {
          Swal.fire({
            icon: 'error',
            title: 'Erreur',
            html: 'Vous pouvez pas supprimer le bloc <h2 style="color:red">INITIALISATION!',
            timer: 1500,
            showConfirmButton:false,
          })
          console.log('cannot delete');
        }
        else {
          this.scene.nodes = this.scene.nodes.filter((node) => {
            return node.id !== id;
          })
          this.scene.links = this.scene.links.filter((link) => {
            return link.from !== id && link.to !== id
          })

          this.alertDelete(id)
        }
      })
    },

    nodeConfig(id) {
      this.$emit('nodeConfig', id);
      //if type is init or cloture --> skipp
      axios({
        method: 'post',
        url: "index.php?option=com_emundus_workflow&controller=item&task=getitem",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          id: id,
        })
      }).then(response => {
        var _type = (response.data.data)[0];
        if(_type.item_name == 'Initialisation' || _type.item_name == 'Cloture') {
          //skip
        }
        else {
          this.$modal.show('elementModal' + id);
        }
      }).catch(error => {
        console.log(error);
      })
    },

    nodeCloned(id) {
      this.$emit('nodeClone', id)

      //check if the node_id is init or not
      axios({
        method: 'post',
        url: "index.php?option=com_emundus_workflow&controller=item&task=getitem",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          id: id,
        })
      }).then(response => {
        var _type = (response.data.data)[0];

        if(_type.item_name == 'Initialisation') {
          Swal.fire({
            icon: 'error',
            title: 'Erreur',
            html: 'Vous pouvez pas cloner le bloc <h2 style="color:red">INITIALISATION!',
            timer: 1500,
            showConfirmButton:false,
          })
        }
        else {
          this.alertClone(id);
        }
      })
    },

    // delete item by id
    deleteItem: function(id) {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=deleteitem',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({id})
      }).then(response => {
      }).catch(error => {
        console.log(error);
      })
    },

    //delete link by id
    deleteLink: function(id) {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=deletelink',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({id})
      }).then(response => {
      }).catch(error => {
        console.log(error);
      })
    },

    alertDelete: function(id) {
      Swal.fire({
        icon: 'success',
        title: 'Congrat',
        text: 'Le bloc est supprimé!',
        timer: 2000,
        showConfirmButton:false,
      })
      this.deleteItem(id);
    },

    alertClone: function(id) {
      Swal.fire({
        icon: 'success',
        title: 'Congrat',
        text: 'Le bloc est dupliqué!',
        timer: 2000,
        showConfirmButton:false,
      })
      this.cloneItem(id);
    },

    cloneItem: async function(id) {
      let _response = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getitem', { params: {id: id}});
      var olditem = (_response.data.data)[0];

      var newitem = {
        item_name: olditem.item_name,
        item_id: olditem.item_id,
        workflow_id: olditem.workflow_id,
        item_label: document.getElementById('label_' + id).innerText || 'anonyme',
      }

      axios({
        method: 'post',
        url: "index.php?option=com_emundus_workflow&controller=item&task=createitem&sub_option=clone&workflowid=" + newitem.workflow_id + "&olditemid=" + newitem.item_id,
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: newitem
        })
      }).then(response => {
        axios({
          method: 'post',
          url: "index.php?option=com_emundus_workflow&controller=item&task=getitem",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            id: response.data.data,
          })
        }).then(answer => {
          var _style = (answer.data.data)[0];
          this.scene.nodes.push({
            id: response.data.data,
            x: -500 + Math.floor((Math.random() * 100) + 1),
            y: 70 + Math.floor((Math.random() * 100) + 1),
            type: newitem.item_name,
            label: newitem.item_label,
            background: _style.CSS_style,
          })
        })
      })
    },


  },
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.flowchart-container {
  margin: 0;
  background-color: #eee;
  position: relative;
  overflow: hidden;
  box-shadow: 5px 5px #eee;
  /*background-image: linear-gradient(#b1b9c6 .1em, transparent .1em), linear-gradient(90deg, #B1B9C6 .1em, transparent .1em);*/
  background-image: radial-gradient(circle, black 1px, rgba(0, 0, 0, 0) 1px);
  background-size: 2em 2em;
  border-style: none;
  width: 1850px;
}

.flowchart-container svg {
  cursor: grab;
  height: 60vh;
}

.swal2-styled.swal2-confirm {
  border-radius: 5px !important;
}

.swal2-styled.swal2-confirm:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}

.swal2-styled.swal2-cancel {
  border-radius: 5px !important;
}

.swal2-styled.swal2-cancel:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}
</style>
