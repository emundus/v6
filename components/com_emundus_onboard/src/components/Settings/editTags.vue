<template>
    <div class="container-evaluation">
      <p class="text-center">{{ translations.descTags }}</p>
<!--      <a @click="pushTag" class="bouton-ajouter-green bouton-ajouter pointer mb-1" style="width: max-content">
        <div class="add-button-div">
          <em class="fas fa-plus mr-1"></em>
          {{ translations.addTag }}
        </div>
      </a>-->
      <div class="col-md-12 tags__list-block" style="justify-content: center">
        <div v-for="(category, index) in tags" class="col-md-3 tags__category-block">
          <div class="d-flex mb-1">
            <h5 class="mb-0" :id="'tags_category_' + index" :contenteditable="editable.category === index" @keypress.enter.prevent="saveCategory(index)">{{category.category}}</h5>
            <span @click="setEditableCategory(index,category.category)">
              <i class="fas fa-pen tags__edit-label"></i>
            </span>
          </div>
          <div>
            <div v-for="(tag, key) in category[0]" class="tags__tag-item">
              <span :class="tag.class + ' tags__tag-item-span'">{{tag.label}}</span>
            </div>
            <a @click="pushTag(index,category.category)" class="bouton-ajouter-green bouton-ajouter pointer tags__add-button" style="width: max-content">
              <div class="add-button-div">
                <em class="fas fa-plus mr-1"></em>
                {{ translations.addTag }}
              </div>
            </a>
          </div>
        </div>
        <div class="col-md-3 tags__category-block-empty">
          <div class="text-center pointer" :title="translations.addCategory" @click="pushCategory">
            <i class="fas fa-plus-circle"></i>
            <p class="mt-1">{{translations.addCategory}}</p>
          </div>
        </div>
      </div>
<!--        <div v-for="(tag, index) in tags" class="status-item tags-item" :id="'tag_' + tag.id">
            <div class="status-field">
                <input type="text" v-model="tag.label">
                <input type="hidden" :class="tag.class">
            </div>
            <v-swatches
                    v-model="tag.class"
                    :swatches="swatches"
                    shapes="circles"
                    row-length="8"
                    show-border
                    popover-x="left"
                    popover-y="top"
            ></v-swatches>
            <button type="button" @click="removeTag(tag,index)" class="remove-tag"><i class="fas fa-times"></i></button>
        </div>-->
    </div>
</template>

<script>
    import axios from "axios";
    import VSwatches from 'vue-swatches'
    import 'vue-swatches/dist/vue-swatches.css'

    const qs = require("qs");

    export default {
        name: "editTags",

        components: {
            VSwatches
        },

        props: {},

        data() {
            return {
                tags: [],
                categories: [],
                oldcategory: null,

                editable:{
                  category: null,
                  tag: null
                },
                show: false,
                swatches: [
                    '#DCC6E0', '#947CB0', '#663399', '#6BB9F0', '#19B5FE', '#013243', '#7BEFB2', '#3FC380', '#1E824C', '#FFFD7E',
                    '#FFFD54', '#F7CA18', '#FABE58', '#E87E04', '#D35400', '#EC644B', '#CF000F', '#E5283B', '#E08283', '#D2527F',
                    '#DB0A5B', '#999999'
                ],
              translations:{
                descTags: Joomla.JText._('COM_EMUNDUS_ONBOARD_TAGSDESCRIPTION'),
                addTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_SETTINGS_ADDTAG"),
                addCategory: Joomla.JText._("COM_EMUNDUS_ONBOARD_SETTINGS_ADDCATEGORY"),
              }
            };
        },

        methods: {
            getTags() {
                axios.get("index.php?option=com_emundus_onboard&controller=settings&task=gettags")
                    .then(response => {
                      this.tags = response.data.data;
                      this.categories = Object.keys(this.tags);
                        /*setTimeout(() => {
                            this.tags.forEach(element => {
                                this.getHexColors(element);
                            });
                        }, 100);*/
                    });

            },

            getHexColors(element) {
                let tags_class = document.querySelector('.' + element.class);
                let style = getComputedStyle(tags_class);
                let rgbs = style.backgroundColor.split('(')[1].split(')')[0].split(',');
                element.class = this.rgbToHex(parseInt(rgbs[0]),parseInt(rgbs[1]),parseInt(rgbs[2]));
            },

            rgbToHex(r, g, b) {
                return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
            },

            pushTag(index,category) {
                this.$emit("LaunchLoading");
                axios({
                    method: "post",
                    url: 'index.php?option=com_emundus_onboard&controller=settings&task=createtag',
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    data: qs.stringify({
                      category: category
                    })
                }).then((newtag) => {
                    this.tags[index][0].push(newtag.data);
                    this.$emit("StopLoading");
                });
            },

            removeTag(tag, index) {
                this.$emit("LaunchLoading");
                axios({
                    method: "post",
                    url: 'index.php?option=com_emundus_onboard&controller=settings&task=deletetag',
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    data: qs.stringify({
                        id: tag.id
                    })
                }).then(() => {
                    this.tags.splice(index,1);
                    this.$emit("StopLoading");
                });
            },

            pushCategory(category) {
              this.$emit("LaunchLoading");
              axios({
                method: "post",
                url: 'index.php?option=com_emundus_onboard&controller=settings&task=createtagcategory',
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded"
                },
              }).then((newcategory) => {
                this.tags.push(newcategory.data);
                this.$emit("StopLoading");
              });
            },

            saveCategory(category) {
              this.editable.category = null;
              axios({
                method: "post",
                url: 'index.php?option=com_emundus_onboard&controller=settings&task=updatetagcategory',
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded"
                },
                data: qs.stringify({
                  oldcategory: this.oldcategory,
                  newcategory: document.getElementById('tags_category_' + category).textContent,
                })
              }).then(() => {
                this.$emit("StopLoading");
                this.tags[category].category = document.getElementById('tags_category_' + category).textContent;
              });
               // console.log(document.getElementById('tags_category_' + category).textContent)
              // Need to post category label with axios
            },

            setEditableCategory(index,category){
              this.oldcategory = category;
              this.editable.category = index;
              setTimeout(function() {
                document.getElementById('tags_category_' + index).focus();
              }, 100);

            }
        },

        created() {
            this.getTags();
        }
    };
</script>
<style scoped>
    .remove-tag{
        border-radius: 50%;
        height: 42px;
        width: 44px;
        transition: all 0.3s ease-in-out;
        margin-left: 1em;
    }
    .remove-tag:hover{
        background-color: red;
    }
    .remove-tag:hover > .fa-trash {
        color: white;
    }

    .fa-trash{
        color: red;
        cursor: pointer;
        width: 15px;
        height: 15px;
    }

    .tags-item .vue-swatches__wrapper{
        right: 3.8em;
    }
    .bouton-sauvergarder-et-continuer{
      justify-content: center;
    }
    .create-tag{
      width: max-content;
      margin-bottom: 20px;
    }
</style>
