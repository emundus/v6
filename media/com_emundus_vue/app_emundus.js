/******/ (function(modules) { // webpackBootstrap
/******/ 	// install a JSONP callback for chunk loading
/******/ 	function webpackJsonpCallback(data) {
/******/ 		var chunkIds = data[0];
/******/ 		var moreModules = data[1];
/******/ 		var executeModules = data[2];
/******/
/******/ 		// add "moreModules" to the modules object,
/******/ 		// then flag all "chunkIds" as loaded and fire callback
/******/ 		var moduleId, chunkId, i = 0, resolves = [];
/******/ 		for(;i < chunkIds.length; i++) {
/******/ 			chunkId = chunkIds[i];
/******/ 			if(Object.prototype.hasOwnProperty.call(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 				resolves.push(installedChunks[chunkId][0]);
/******/ 			}
/******/ 			installedChunks[chunkId] = 0;
/******/ 		}
/******/ 		for(moduleId in moreModules) {
/******/ 			if(Object.prototype.hasOwnProperty.call(moreModules, moduleId)) {
/******/ 				modules[moduleId] = moreModules[moduleId];
/******/ 			}
/******/ 		}
/******/ 		if(parentJsonpFunction) parentJsonpFunction(data);
/******/
/******/ 		while(resolves.length) {
/******/ 			resolves.shift()();
/******/ 		}
/******/
/******/ 		// add entry modules from loaded chunk to deferred list
/******/ 		deferredModules.push.apply(deferredModules, executeModules || []);
/******/
/******/ 		// run deferred modules when all chunks ready
/******/ 		return checkDeferredModules();
/******/ 	};
/******/ 	function checkDeferredModules() {
/******/ 		var result;
/******/ 		for(var i = 0; i < deferredModules.length; i++) {
/******/ 			var deferredModule = deferredModules[i];
/******/ 			var fulfilled = true;
/******/ 			for(var j = 1; j < deferredModule.length; j++) {
/******/ 				var depId = deferredModule[j];
/******/ 				if(installedChunks[depId] !== 0) fulfilled = false;
/******/ 			}
/******/ 			if(fulfilled) {
/******/ 				deferredModules.splice(i--, 1);
/******/ 				result = __webpack_require__(__webpack_require__.s = deferredModule[0]);
/******/ 			}
/******/ 		}
/******/
/******/ 		return result;
/******/ 	}
/******/
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// object to store loaded and loading chunks
/******/ 	// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 	// Promise = chunk loading, 0 = chunk loaded
/******/ 	var installedChunks = {
/******/ 		"app": 0
/******/ 	};
/******/
/******/ 	var deferredModules = [];
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	var jsonpArray = window["webpackJsonp"] = window["webpackJsonp"] || [];
/******/ 	var oldJsonpFunction = jsonpArray.push.bind(jsonpArray);
/******/ 	jsonpArray.push = webpackJsonpCallback;
/******/ 	jsonpArray = jsonpArray.slice();
/******/ 	for(var i = 0; i < jsonpArray.length; i++) webpackJsonpCallback(jsonpArray[i]);
/******/ 	var parentJsonpFunction = oldJsonpFunction;
/******/
/******/
/******/ 	// add entry module to deferred list
/******/ 	deferredModules.push([0,"chunk-vendors"]);
/******/ 	// run deferred modules when ready
/******/ 	return checkDeferredModules();
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/AttachmentEdit.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/AttachmentEdit.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _services_attachment__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../services/attachment */ \"./src/services/attachment.js\");\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n    name: 'AttachmentEdit',\n    props: {\n        user: {\n            type: String,\n            required: true\n        },\n        fnum: {\n            type: String,\n            required: true\n        },\n        attachment: {\n            type: Object,\n            required: true\n        }\n    },\n    data() {\n        return {\n            description: this.attachment.description,\n            is_validated: this.attachment.is_validated\n        }\n    },\n    methods: {\n        async saveChanges() {\n            const attachment_data = {\n                id: this.attachment.aid,\n                description: this.description,\n                is_validated: this.is_validated\n            };\n\n            const response = await _services_attachment__WEBPACK_IMPORTED_MODULE_0__[\"default\"].updateAttachment(this.fnum, this.user, attachment_data);\n            this.$emit('saveChanges', attachment_data);\n        }\n    }\n});\n\n\n//# sourceURL=webpack:///./src/components/AttachmentEdit.vue?./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/AttachmentPreview.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/AttachmentPreview.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _services_attachment__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../services/attachment */ \"./src/services/attachment.js\");\n//\n//\n//\n//\n//\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n    props: {\n        attachment: {\n            type: Object,\n            required: true\n        },\n    },\n    data() {\n        return {\n            attachmentType: \"\"\n        }\n    },\n    mounted() {\n        this.attachmentType = this.attachment.attachment_type;\n        // this.loadAttachment();\n    },\n    methods: {\n        loadAttachment() {\n            _services_attachment__WEBPACK_IMPORTED_MODULE_0__[\"default\"].loadAttachment(this.attachment.aid, this.attachmentType).then(response => {\n                this.$refs.attachmentPreview.innerHTML = response.data;\n            });\n        }\n    }\n});\n\n\n//# sourceURL=webpack:///./src/components/AttachmentPreview.vue?./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Attachments.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Attachments.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _AttachmentPreview_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AttachmentPreview.vue */ \"./src/components/AttachmentPreview.vue\");\n/* harmony import */ var _AttachmentEdit_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AttachmentEdit.vue */ \"./src/components/AttachmentEdit.vue\");\n/* harmony import */ var _services_attachment_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../services/attachment.js */ \"./src/services/attachment.js\");\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  name: 'Attachments',\n  components: {\n    AttachmentPreview: _AttachmentPreview_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n    AttachmentEdit: _AttachmentEdit_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]\n  },\n  props: {\n    user: {\n      type: String,\n      required: true,\n    },\n    fnum: {\n      type: String,\n      required: true,\n    }\n  },\n  data() {\n    return {\n      loading: true,\n      attachments: [],\n      selectedAttachment: null,\n    };\n  },\n  mounted() {\n    this.getAttachments();\n  },\n  methods: {\n    async getAttachments() {\n      this.attachments = await _services_attachment_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"].getAttachmentsByFnum(this.fnum);\n    },\n    searchInFiles() {\n      this.attachments.forEach((attachment, index) => {\n        // if attachment description contains the search term, show it\n        if (attachment.description.includes(this.$refs[\"searchbar\"].value) || attachment.filename.includes(this.$refs[\"searchbar\"].value)) {\n          this.attachments[index].show = true;\n        } else {\n          this.attachments[index].show = false;\n        }\n      });\n    },\n    openModal(attachment) {\n      this.$modal.show('edit');\n      this.selectedAttachment = attachment;\n    },\n    updateAttachment() {\n      this.getAttachments();\n      this.$modal.hide('edit');\n    },\n    async deleteAttachment(id) {\n      // remove attachment from attachments data\n      this.attachments = this.attachments.filter(attachment => attachment.aid !== aid);\n\n      const response = await _services_attachment_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"].deleteAttachment(this.fnum, aid);\n      if (response.status == true) {\n        // Display tooltip deleted succesfully  \n      }\n    }\n  },\n  computed: {\n    displayedAttachments() {\n      return this.attachments.filter(attachment => {\n        return attachment.show == true || attachment.show == undefined;\n      });\n    }\n  }\n});\n\n\n//# sourceURL=webpack:///./src/components/Attachments.vue?./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"0be82b3e-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/AttachmentEdit.vue?vue&type=template&id=fb825d06&":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"0be82b3e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/AttachmentEdit.vue?vue&type=template&id=fb825d06& ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function() {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\"div\", { attrs: { id: \"attachment-edit\" } }, [\n    _c(\"h2\", [_vm._v(_vm._s(_vm.attachment.filename))]),\n    _c(\"div\", { staticClass: \"editableData\" }, [\n      _c(\"label\", { attrs: { for: \"description\" } }, [_vm._v(\"DESCRIPTION\")]),\n      _c(\"input\", {\n        directives: [\n          {\n            name: \"model\",\n            rawName: \"v-model\",\n            value: _vm.description,\n            expression: \"description\"\n          }\n        ],\n        attrs: { name: \"description\", type: \"text\" },\n        domProps: { value: _vm.description },\n        on: {\n          input: function($event) {\n            if ($event.target.composing) {\n              return\n            }\n            _vm.description = $event.target.value\n          }\n        }\n      }),\n      _c(\"label\", { attrs: { for: \"status\" } }, [_vm._v(\"FILE_STATUS\")]),\n      _c(\n        \"select\",\n        {\n          directives: [\n            {\n              name: \"model\",\n              rawName: \"v-model\",\n              value: _vm.is_validated,\n              expression: \"is_validated\"\n            }\n          ],\n          attrs: { name: \"status\" },\n          on: {\n            change: function($event) {\n              var $$selectedVal = Array.prototype.filter\n                .call($event.target.options, function(o) {\n                  return o.selected\n                })\n                .map(function(o) {\n                  var val = \"_value\" in o ? o._value : o.value\n                  return val\n                })\n              _vm.is_validated = $event.target.multiple\n                ? $$selectedVal\n                : $$selectedVal[0]\n            }\n          }\n        },\n        [\n          _c(\"option\", { attrs: { value: \"\" } }, [_vm._v(\"Indéfini\")]),\n          _c(\"option\", { attrs: { value: \"1\" } }, [_vm._v(\"Valide\")]),\n          _c(\"option\", { attrs: { value: \"-2\" } }, [_vm._v(\"Non Valide\")])\n        ]\n      )\n    ]),\n    _c(\"div\", { staticClass: \"actions\" }, [\n      _c(\n        \"button\",\n        {\n          staticClass: \"btn close-btn\",\n          on: {\n            click: function($event) {\n              return _vm.$emit(\"closeModal\")\n            }\n          }\n        },\n        [_vm._v(\"CLOSE\")]\n      ),\n      _c(\n        \"button\",\n        { staticClass: \"btn save-btn\", on: { click: _vm.saveChanges } },\n        [_vm._v(\"SAVE\")]\n      )\n    ])\n  ])\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack:///./src/components/AttachmentEdit.vue?./node_modules/cache-loader/dist/cjs.js?%7B%22cacheDirectory%22:%22node_modules/.cache/vue-loader%22,%22cacheIdentifier%22:%220be82b3e-vue-loader-template%22%7D!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"0be82b3e-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/AttachmentPreview.vue?vue&type=template&id=20d71aa5&":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"0be82b3e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/AttachmentPreview.vue?vue&type=template&id=20d71aa5& ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function() {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\"div\", { attrs: { id: \"attachment-preview\" } })\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack:///./src/components/AttachmentPreview.vue?./node_modules/cache-loader/dist/cjs.js?%7B%22cacheDirectory%22:%22node_modules/.cache/vue-loader%22,%22cacheIdentifier%22:%220be82b3e-vue-loader-template%22%7D!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"0be82b3e-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Attachments.vue?vue&type=template&id=69a49ca0&scoped=true&":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"0be82b3e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Attachments.vue?vue&type=template&id=69a49ca0&scoped=true& ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function() {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\n    \"div\",\n    [\n      _c(\"h1\", [_vm._v(\"Documents - ..% envoyés\")]),\n      _c(\"div\", { staticClass: \"searchbar\" }, [\n        _c(\"input\", {\n          ref: \"searchbar\",\n          attrs: { type: \"text\", placeholder: \"Key words\" },\n          on: { input: _vm.searchInFiles }\n        })\n      ]),\n      _vm.attachments.length\n        ? _c(\n            \"ul\",\n            _vm._l(_vm.displayedAttachments, function(attachment) {\n              return _c(\"li\", { key: attachment.aid }, [\n                _c(\"a\", { on: { click: _vm.openModal } }, [\n                  _vm._v(_vm._s(attachment.filename))\n                ]),\n                _c(\"span\", [_vm._v(_vm._s(attachment.description))]),\n                _c(\"div\", { staticClass: \"actions\" }, [\n                  _c(\n                    \"span\",\n                    {\n                      on: {\n                        click: function($event) {\n                          return _vm.openModal(attachment)\n                        }\n                      }\n                    },\n                    [_vm._v(\"Edit\")]\n                  ),\n                  _c(\n                    \"span\",\n                    {\n                      on: {\n                        click: function($event) {\n                          return _vm.deleteAttachment(attachment.aid)\n                        }\n                      }\n                    },\n                    [_vm._v(\"Delete\")]\n                  )\n                ])\n              ])\n            }),\n            0\n          )\n        : _c(\"p\", [_vm._v(\"Aucun dossier rattaché à cet utilisateur\")]),\n      _c(\n        \"modal\",\n        { attrs: { name: \"edit\" } },\n        [\n          _c(\"AttachmentPreview\", {\n            attrs: { attachment: _vm.selectedAttachment }\n          }),\n          _c(\"AttachmentEdit\", {\n            attrs: {\n              attachment: _vm.selectedAttachment,\n              user: _vm.user,\n              fnum: _vm.fnum\n            },\n            on: {\n              closeModal: function($event) {\n                return _vm.$modal.hide(\"edit\")\n              },\n              saveChanges: function($event) {\n                return _vm.updateAttachment()\n              }\n            }\n          })\n        ],\n        1\n      )\n    ],\n    1\n  )\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack:///./src/components/Attachments.vue?./node_modules/cache-loader/dist/cjs.js?%7B%22cacheDirectory%22:%22node_modules/.cache/vue-loader%22,%22cacheIdentifier%22:%220be82b3e-vue-loader-template%22%7D!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js?!./node_modules/css-loader/dist/cjs.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!../node_modules/sass-loader/dist/cjs.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Attachments.vue?vue&type=style&index=0&id=69a49ca0&lang=scss&scoped=true&":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??ref--8-oneOf-1-0!./node_modules/css-loader/dist/cjs.js??ref--8-oneOf-1-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--8-oneOf-1-2!../node_modules/sass-loader/dist/cjs.js??ref--8-oneOf-1-3!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Attachments.vue?vue&type=style&index=0&id=69a49ca0&lang=scss&scoped=true& ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// extracted by mini-css-extract-plugin\n    if(false) { var cssReload; }\n  \n\n//# sourceURL=webpack:///./src/components/Attachments.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??ref--8-oneOf-1-0!./node_modules/css-loader/dist/cjs.js??ref--8-oneOf-1-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--8-oneOf-1-2!../node_modules/sass-loader/dist/cjs.js??ref--8-oneOf-1-3!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./src/components/AttachmentEdit.vue":
/*!*******************************************!*\
  !*** ./src/components/AttachmentEdit.vue ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _AttachmentEdit_vue_vue_type_template_id_fb825d06___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AttachmentEdit.vue?vue&type=template&id=fb825d06& */ \"./src/components/AttachmentEdit.vue?vue&type=template&id=fb825d06&\");\n/* harmony import */ var _AttachmentEdit_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AttachmentEdit.vue?vue&type=script&lang=js& */ \"./src/components/AttachmentEdit.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _AttachmentEdit_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _AttachmentEdit_vue_vue_type_template_id_fb825d06___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _AttachmentEdit_vue_vue_type_template_id_fb825d06___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"src/components/AttachmentEdit.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack:///./src/components/AttachmentEdit.vue?");

/***/ }),

/***/ "./src/components/AttachmentEdit.vue?vue&type=script&lang=js&":
/*!********************************************************************!*\
  !*** ./src/components/AttachmentEdit.vue?vue&type=script&lang=js& ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentEdit_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/cache-loader/dist/cjs.js??ref--0-0!../../node_modules/vue-loader/lib??vue-loader-options!./AttachmentEdit.vue?vue&type=script&lang=js& */ \"./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/AttachmentEdit.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentEdit_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack:///./src/components/AttachmentEdit.vue?");

/***/ }),

/***/ "./src/components/AttachmentEdit.vue?vue&type=template&id=fb825d06&":
/*!**************************************************************************!*\
  !*** ./src/components/AttachmentEdit.vue?vue&type=template&id=fb825d06& ***!
  \**************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_0be82b3e_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentEdit_vue_vue_type_template_id_fb825d06___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"0be82b3e-vue-loader-template\"}!../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../node_modules/cache-loader/dist/cjs.js??ref--0-0!../../node_modules/vue-loader/lib??vue-loader-options!./AttachmentEdit.vue?vue&type=template&id=fb825d06& */ \"./node_modules/cache-loader/dist/cjs.js?{\\\"cacheDirectory\\\":\\\"node_modules/.cache/vue-loader\\\",\\\"cacheIdentifier\\\":\\\"0be82b3e-vue-loader-template\\\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/AttachmentEdit.vue?vue&type=template&id=fb825d06&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_0be82b3e_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentEdit_vue_vue_type_template_id_fb825d06___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_0be82b3e_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentEdit_vue_vue_type_template_id_fb825d06___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack:///./src/components/AttachmentEdit.vue?");

/***/ }),

/***/ "./src/components/AttachmentPreview.vue":
/*!**********************************************!*\
  !*** ./src/components/AttachmentPreview.vue ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _AttachmentPreview_vue_vue_type_template_id_20d71aa5___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AttachmentPreview.vue?vue&type=template&id=20d71aa5& */ \"./src/components/AttachmentPreview.vue?vue&type=template&id=20d71aa5&\");\n/* harmony import */ var _AttachmentPreview_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AttachmentPreview.vue?vue&type=script&lang=js& */ \"./src/components/AttachmentPreview.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _AttachmentPreview_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _AttachmentPreview_vue_vue_type_template_id_20d71aa5___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _AttachmentPreview_vue_vue_type_template_id_20d71aa5___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"src/components/AttachmentPreview.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack:///./src/components/AttachmentPreview.vue?");

/***/ }),

/***/ "./src/components/AttachmentPreview.vue?vue&type=script&lang=js&":
/*!***********************************************************************!*\
  !*** ./src/components/AttachmentPreview.vue?vue&type=script&lang=js& ***!
  \***********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentPreview_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/cache-loader/dist/cjs.js??ref--0-0!../../node_modules/vue-loader/lib??vue-loader-options!./AttachmentPreview.vue?vue&type=script&lang=js& */ \"./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/AttachmentPreview.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentPreview_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack:///./src/components/AttachmentPreview.vue?");

/***/ }),

/***/ "./src/components/AttachmentPreview.vue?vue&type=template&id=20d71aa5&":
/*!*****************************************************************************!*\
  !*** ./src/components/AttachmentPreview.vue?vue&type=template&id=20d71aa5& ***!
  \*****************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_0be82b3e_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentPreview_vue_vue_type_template_id_20d71aa5___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"0be82b3e-vue-loader-template\"}!../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../node_modules/cache-loader/dist/cjs.js??ref--0-0!../../node_modules/vue-loader/lib??vue-loader-options!./AttachmentPreview.vue?vue&type=template&id=20d71aa5& */ \"./node_modules/cache-loader/dist/cjs.js?{\\\"cacheDirectory\\\":\\\"node_modules/.cache/vue-loader\\\",\\\"cacheIdentifier\\\":\\\"0be82b3e-vue-loader-template\\\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/AttachmentPreview.vue?vue&type=template&id=20d71aa5&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_0be82b3e_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentPreview_vue_vue_type_template_id_20d71aa5___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_0be82b3e_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentPreview_vue_vue_type_template_id_20d71aa5___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack:///./src/components/AttachmentPreview.vue?");

/***/ }),

/***/ "./src/components/Attachments.vue":
/*!****************************************!*\
  !*** ./src/components/Attachments.vue ***!
  \****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _Attachments_vue_vue_type_template_id_69a49ca0_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Attachments.vue?vue&type=template&id=69a49ca0&scoped=true& */ \"./src/components/Attachments.vue?vue&type=template&id=69a49ca0&scoped=true&\");\n/* harmony import */ var _Attachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Attachments.vue?vue&type=script&lang=js& */ \"./src/components/Attachments.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _Attachments_vue_vue_type_style_index_0_id_69a49ca0_lang_scss_scoped_true___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Attachments.vue?vue&type=style&index=0&id=69a49ca0&lang=scss&scoped=true& */ \"./src/components/Attachments.vue?vue&type=style&index=0&id=69a49ca0&lang=scss&scoped=true&\");\n/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(\n  _Attachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _Attachments_vue_vue_type_template_id_69a49ca0_scoped_true___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _Attachments_vue_vue_type_template_id_69a49ca0_scoped_true___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  \"69a49ca0\",\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"src/components/Attachments.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack:///./src/components/Attachments.vue?");

/***/ }),

/***/ "./src/components/Attachments.vue?vue&type=script&lang=js&":
/*!*****************************************************************!*\
  !*** ./src/components/Attachments.vue?vue&type=script&lang=js& ***!
  \*****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/cache-loader/dist/cjs.js??ref--0-0!../../node_modules/vue-loader/lib??vue-loader-options!./Attachments.vue?vue&type=script&lang=js& */ \"./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Attachments.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack:///./src/components/Attachments.vue?");

/***/ }),

/***/ "./src/components/Attachments.vue?vue&type=style&index=0&id=69a49ca0&lang=scss&scoped=true&":
/*!**************************************************************************************************!*\
  !*** ./src/components/Attachments.vue?vue&type=style&index=0&id=69a49ca0&lang=scss&scoped=true& ***!
  \**************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_8_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_8_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_8_oneOf_1_2_node_modules_sass_loader_dist_cjs_js_ref_8_oneOf_1_3_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachments_vue_vue_type_style_index_0_id_69a49ca0_lang_scss_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/mini-css-extract-plugin/dist/loader.js??ref--8-oneOf-1-0!../../node_modules/css-loader/dist/cjs.js??ref--8-oneOf-1-1!../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../node_modules/postcss-loader/src??ref--8-oneOf-1-2!../../../node_modules/sass-loader/dist/cjs.js??ref--8-oneOf-1-3!../../node_modules/cache-loader/dist/cjs.js??ref--0-0!../../node_modules/vue-loader/lib??vue-loader-options!./Attachments.vue?vue&type=style&index=0&id=69a49ca0&lang=scss&scoped=true& */ \"./node_modules/mini-css-extract-plugin/dist/loader.js?!./node_modules/css-loader/dist/cjs.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!../node_modules/sass-loader/dist/cjs.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Attachments.vue?vue&type=style&index=0&id=69a49ca0&lang=scss&scoped=true&\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_8_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_8_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_8_oneOf_1_2_node_modules_sass_loader_dist_cjs_js_ref_8_oneOf_1_3_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachments_vue_vue_type_style_index_0_id_69a49ca0_lang_scss_scoped_true___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_8_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_8_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_8_oneOf_1_2_node_modules_sass_loader_dist_cjs_js_ref_8_oneOf_1_3_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachments_vue_vue_type_style_index_0_id_69a49ca0_lang_scss_scoped_true___WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_ref_8_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_8_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_8_oneOf_1_2_node_modules_sass_loader_dist_cjs_js_ref_8_oneOf_1_3_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachments_vue_vue_type_style_index_0_id_69a49ca0_lang_scss_scoped_true___WEBPACK_IMPORTED_MODULE_0__) if([\"default\"].indexOf(__WEBPACK_IMPORT_KEY__) < 0) (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_mini_css_extract_plugin_dist_loader_js_ref_8_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_8_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_8_oneOf_1_2_node_modules_sass_loader_dist_cjs_js_ref_8_oneOf_1_3_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachments_vue_vue_type_style_index_0_id_69a49ca0_lang_scss_scoped_true___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));\n\n\n//# sourceURL=webpack:///./src/components/Attachments.vue?");

/***/ }),

/***/ "./src/components/Attachments.vue?vue&type=template&id=69a49ca0&scoped=true&":
/*!***********************************************************************************!*\
  !*** ./src/components/Attachments.vue?vue&type=template&id=69a49ca0&scoped=true& ***!
  \***********************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_0be82b3e_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachments_vue_vue_type_template_id_69a49ca0_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"0be82b3e-vue-loader-template\"}!../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../node_modules/cache-loader/dist/cjs.js??ref--0-0!../../node_modules/vue-loader/lib??vue-loader-options!./Attachments.vue?vue&type=template&id=69a49ca0&scoped=true& */ \"./node_modules/cache-loader/dist/cjs.js?{\\\"cacheDirectory\\\":\\\"node_modules/.cache/vue-loader\\\",\\\"cacheIdentifier\\\":\\\"0be82b3e-vue-loader-template\\\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Attachments.vue?vue&type=template&id=69a49ca0&scoped=true&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_0be82b3e_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachments_vue_vue_type_template_id_69a49ca0_scoped_true___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_0be82b3e_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachments_vue_vue_type_template_id_69a49ca0_scoped_true___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack:///./src/components/Attachments.vue?");

/***/ }),

/***/ "./src/main.js":
/*!*********************!*\
  !*** ./src/main.js ***!
  \*********************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm.js\");\n/* harmony import */ var _components_Attachments_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/Attachments.vue */ \"./src/components/Attachments.vue\");\n/* harmony import */ var vue_js_modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vue-js-modal */ \"../node_modules/vue-js-modal/dist/index.js\");\n/* harmony import */ var vue_js_modal__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(vue_js_modal__WEBPACK_IMPORTED_MODULE_2__);\n\n\n\n\nvue__WEBPACK_IMPORTED_MODULE_0__[\"default\"].config.productionTip = false;\nvue__WEBPACK_IMPORTED_MODULE_0__[\"default\"].use(vue_js_modal__WEBPACK_IMPORTED_MODULE_2___default.a);\n\nif (document.getElementById(\"em-application-attachment\")) {\n  new vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]({\n    render(h) {\n      return h(_components_Attachments_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"], {\n        props: {\n          fnum: this.$el.attributes.fnum.value,\n          user: this.$el.attributes.user.value,\n        },\n      });\n    },\n  }).$mount('#em-application-attachment');  \n}\n\n//# sourceURL=webpack:///./src/main.js?");

/***/ }),

/***/ "./src/services/attachment.js":
/*!************************************!*\
  !*** ./src/services/attachment.js ***!
  \************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _axiosClient__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./axiosClient */ \"./src/services/axiosClient.js\");\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  async getAttachments(user) {\n    try {\n      const response = await Object(_axiosClient__WEBPACK_IMPORTED_MODULE_0__[\"default\"])().get('index.php?option=com_emundus&controller=application&task=getuserattachments', {\n        params: {\n          user_id: user,\n        }\n      });\n\n      return response.data;\n    } catch (e) {\n      throw e;\n    }\n  },\n\n  async getAttachmentsByFnum(fnum) {\n    try {\n      const response = await Object(_axiosClient__WEBPACK_IMPORTED_MODULE_0__[\"default\"])().get('index.php?option=com_emundus&controller=application&task=getattachmentsbyfnum', {\n        params: {\n          fnum : fnum,\n        }\n      });\n\n      // add show attribute to true to all attchments in response data\n      response.data.forEach(attachment => {\n        attachment.show = true;\n      });\n\n      return response.data;\n    } catch (e) {\n      throw e;\n    }\n  },\n\n  async deleteAttachment(fnum, attachment_id) {\n    try {\n      const response = await Object(_axiosClient__WEBPACK_IMPORTED_MODULE_0__[\"default\"])().get('index.php?option=com_emundus&controller=application&task=deleteattachement', {\n        params: {\n          fnum: fnum,\n          ids: JSON.stringify([attachment_id]),\n        }\n      });\n\n      return response;\n    } catch (e) {\n      throw e;\n    }\n  },\n  \n  async updateAttachment(fnum, user, attachment) {\n    try {\n      const response = await Object(_axiosClient__WEBPACK_IMPORTED_MODULE_0__[\"default\"])().get('index.php?option=com_emundus&controller=application&task=updateattachment', {\n        params: {\n          fnum: fnum,\n          user: user,\n          attachment: JSON.stringify(attachment),\n        }\n      });\n\n      return response;\n    } catch (e) {\n      throw e;\n    }\n  }\n});\n\n\n//# sourceURL=webpack:///./src/services/attachment.js?");

/***/ }),

/***/ "./src/services/axiosClient.js":
/*!*************************************!*\
  !*** ./src/services/axiosClient.js ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ \"../node_modules/axios/index.js\");\n/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_0__);\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ((headers = {\n    'Content-Type': 'application/json',\n    'Cache-Control': 'no-cache',\n}) =>\n    axios__WEBPACK_IMPORTED_MODULE_0___default.a.create({\n        timeout: 30000,\n        headers,\n        responseType: 'json',\n        withCredentials: true,\n    }));\n\n//# sourceURL=webpack:///./src/services/axiosClient.js?");

/***/ }),

/***/ 0:
/*!***************************!*\
  !*** multi ./src/main.js ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("module.exports = __webpack_require__(/*! ./src/main.js */\"./src/main.js\");\n\n\n//# sourceURL=webpack:///multi_./src/main.js?");

/***/ })

/******/ });