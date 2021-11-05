/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@babel/runtime/helpers/arrayLikeToArray.js":
/*!*****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/arrayLikeToArray.js ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _arrayLikeToArray(arr, len) {
  if (len == null || len > arr.length) len = arr.length;

  for (var i = 0, arr2 = new Array(len); i < len; i++) {
    arr2[i] = arr[i];
  }

  return arr2;
}

module.exports = _arrayLikeToArray;
module.exports["default"] = module.exports, module.exports.__esModule = true;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/arrayWithHoles.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/arrayWithHoles.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _arrayWithHoles(arr) {
  if (Array.isArray(arr)) return arr;
}

module.exports = _arrayWithHoles;
module.exports["default"] = module.exports, module.exports.__esModule = true;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/iterableToArrayLimit.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/iterableToArrayLimit.js ***!
  \*********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _iterableToArrayLimit(arr, i) {
  var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"];

  if (_i == null) return;
  var _arr = [];
  var _n = true;
  var _d = false;

  var _s, _e;

  try {
    for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) {
      _arr.push(_s.value);

      if (i && _arr.length === i) break;
    }
  } catch (err) {
    _d = true;
    _e = err;
  } finally {
    try {
      if (!_n && _i["return"] != null) _i["return"]();
    } finally {
      if (_d) throw _e;
    }
  }

  return _arr;
}

module.exports = _iterableToArrayLimit;
module.exports["default"] = module.exports, module.exports.__esModule = true;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/nonIterableRest.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/nonIterableRest.js ***!
  \****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _nonIterableRest() {
  throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}

module.exports = _nonIterableRest;
module.exports["default"] = module.exports, module.exports.__esModule = true;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/slicedToArray.js":
/*!**************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/slicedToArray.js ***!
  \**************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var arrayWithHoles = __webpack_require__(/*! ./arrayWithHoles.js */ "./node_modules/@babel/runtime/helpers/arrayWithHoles.js");

var iterableToArrayLimit = __webpack_require__(/*! ./iterableToArrayLimit.js */ "./node_modules/@babel/runtime/helpers/iterableToArrayLimit.js");

var unsupportedIterableToArray = __webpack_require__(/*! ./unsupportedIterableToArray.js */ "./node_modules/@babel/runtime/helpers/unsupportedIterableToArray.js");

var nonIterableRest = __webpack_require__(/*! ./nonIterableRest.js */ "./node_modules/@babel/runtime/helpers/nonIterableRest.js");

function _slicedToArray(arr, i) {
  return arrayWithHoles(arr) || iterableToArrayLimit(arr, i) || unsupportedIterableToArray(arr, i) || nonIterableRest();
}

module.exports = _slicedToArray;
module.exports["default"] = module.exports, module.exports.__esModule = true;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/unsupportedIterableToArray.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/unsupportedIterableToArray.js ***!
  \***************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var arrayLikeToArray = __webpack_require__(/*! ./arrayLikeToArray.js */ "./node_modules/@babel/runtime/helpers/arrayLikeToArray.js");

function _unsupportedIterableToArray(o, minLen) {
  if (!o) return;
  if (typeof o === "string") return arrayLikeToArray(o, minLen);
  var n = Object.prototype.toString.call(o).slice(8, -1);
  if (n === "Object" && o.constructor) n = o.constructor.name;
  if (n === "Map" || n === "Set") return Array.from(o);
  if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return arrayLikeToArray(o, minLen);
}

module.exports = _unsupportedIterableToArray;
module.exports["default"] = module.exports, module.exports.__esModule = true;

/***/ }),

/***/ "./src/blocks/ContentSharer.js":
/*!*************************************!*\
  !*** ./src/blocks/ContentSharer.js ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);





var TestDemoUCSC = function TestDemoUCSC() {
  wp.blocks.registerBlockType("ucscblocks/contentsharer", {
    title: "Content Sharer",
    icon: "admin-site-alt3",
    category: "common",
    attributes: {
      siteid: {
        type: "string"
      },
      postType: {
        type: "string"
      }
    },
    edit: function edit(_ref) {
      var setAttributes = _ref.setAttributes,
          attributes = _ref.attributes;
      var siteid = attributes.siteid,
          postType = attributes.postType;
      var selectedSite, setSelectedSite;

      if (typeof siteid === 'undefined') {
        setAttributes({
          siteid: 1
        });

        var _useState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(1);

        var _useState2 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState, 2);

        selectedSite = _useState2[0];
        setSelectedSite = _useState2[1];
      } else {
        var _useState3 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(siteid);

        var _useState4 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState3, 2);

        selectedSite = _useState4[0];
        setSelectedSite = _useState4[1];
      }

      var selectedPostType, setSelectedPostType;

      if (typeof postType === 'undefined') {
        setAttributes({
          postType: ""
        });

        var _useState5 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])("");

        var _useState6 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState5, 2);

        selectedPostType = _useState6[0];
        setSelectedPostType = _useState6[1];
      } else {
        var _useState7 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(postType);

        var _useState8 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState7, 2);

        selectedPostType = _useState8[0];
        setSelectedPostType = _useState8[1];
      }

      var _useState9 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])([]),
          _useState10 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState9, 2),
          sites = _useState10[0],
          setSites = _useState10[1];

      var _useState11 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])([]),
          _useState12 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState11, 2),
          postTypes = _useState12[0],
          setPostTypes = _useState12[1];

      var fetchPostTypes = function fetchPostTypes(siteid) {
        fetch('/wp-json/ucscgutenbergblocks/v1/posttypes?siteid=' + siteid).then(function (res) {
          return res.text();
        }).then(function (text) {
          var postTypes = JSON.parse(text);
          var pTypes = [];
          Object.keys(postTypes).map(function (type) {
            pTypes.push({
              label: type,
              value: type
            });
          });
          setPostTypes(pTypes);
        });
      };

      Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useEffect"])(function () {
        fetch('/wp-json/ucscgutenbergblocks/v1/sites').then(function (res) {
          return res.text();
        }).then(function (text) {
          setSites(JSON.parse(text));
        });
        fetchPostTypes(selectedSite);
      }, []);
      var options = sites.map(function (site) {
        return {
          label: site.domain,
          value: site.blog_id
        };
      });

      var userSelectedASite = function userSelectedASite(e) {
        setSelectedSite(e.target.value);
        setAttributes({
          siteid: e.target.value
        });
        fetchPostTypes(e.target.value);
      };

      var userSelectedAPostType = function userSelectedAPostType(e) {
        setSelectedPostType(e.target.value);
        setAttributes({
          postType: e.target.value
        });
      };

      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["Fragment"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h2", null, "Content Sharerer"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h4", null, "Site List"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("select", {
        value: selectedSite,
        onChange: userSelectedASite
      }, options.map(function (option) {
        return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("option", {
          value: option.value
        }, option.label);
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h4", null, "Post Types"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("select", {
        value: selectedPostType,
        onChange: userSelectedAPostType
      }, postTypes.map(function (type) {
        return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("option", {
          value: type.value
        }, type.label);
      })));
    },
    save: function save(props) {
      return null;
    }
  });
};

/* harmony default export */ __webpack_exports__["default"] = (TestDemoUCSC);

/***/ }),

/***/ "./src/blocks/CourseCatalog.js":
/*!*************************************!*\
  !*** ./src/blocks/CourseCatalog.js ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);




var CourseCatalog = function CourseCatalog() {
  wp.blocks.registerBlockType("ucscblocks/coursecatalog", {
    title: "Course Catalog",
    icon: "book-alt",
    category: "common",
    attributes: {
      subject: {
        type: "string"
      }
    },
    edit: function edit(_ref) {
      var setAttributes = _ref.setAttributes,
          attributes = _ref.attributes;
      console.log('here');
      console.log(attributes); //   const { siteid, postType } = attributes;
      //   let selectedSite, setSelectedSite;
      //   if (typeof siteid === 'undefined') {
      //     setAttributes({
      //       siteid: 1
      //     });
      //     [selectedSite, setSelectedSite] = useState(1);
      //   } else {
      //     [selectedSite, setSelectedSite] = useState(siteid);
      //   }
      //   let selectedPostType, setSelectedPostType;
      //   if (typeof postType === 'undefined') {
      //     setAttributes({
      //       postType: ""
      //     });
      //     [selectedPostType, setSelectedPostType] = useState("");
      //   } else {
      //     [selectedPostType, setSelectedPostType] = useState(postType);
      //   }
      //   const [sites, setSites] = useState([]);
      //   const [postTypes, setPostTypes] = useState([]);
      //   const fetchPostTypes = (siteid) => {
      //     fetch('/wp-json/ucscgutenbergblocks/v1/posttypes?siteid=' + siteid)
      //       .then(res => res.text())
      //       .then((text) => {
      //         const postTypes = JSON.parse(text);
      //         const pTypes = [];
      //         Object.keys(postTypes).map(type => {
      //           pTypes.push({
      //             label: type,
      //             value: type
      //           })
      //         })
      //         setPostTypes(pTypes);
      //       })
      //   }
      //   useEffect(() => {
      //     fetch('/wp-json/ucscgutenbergblocks/v1/sites')
      //       .then(res => res.text())
      //       .then((text) => {
      //         setSites(JSON.parse(text));
      //       });
      //     fetchPostTypes(selectedSite);
      //   }, []);
      //   const options = sites.map(site => {
      //     return {
      //       label: site.domain,
      //       value: site.blog_id
      //     }
      //   })
      //   const userSelectedASite = (e) => {
      //     setSelectedSite(e.target.value);
      //     setAttributes({
      //       siteid: e.target.value
      //     });
      //     fetchPostTypes(e.target.value);
      //   }
      //   const userSelectedAPostType = (e) => {
      //     setSelectedPostType(e.target.value);
      //     setAttributes({
      //       postType: e.target.value
      //     });
      //   }

      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Fragment"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("h2", null, "Course Catalog"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("h4", null, "subject"));
    },
    save: function save(props) {
      return null;
    }
  });
};

/* harmony default export */ __webpack_exports__["default"] = (CourseCatalog);

/***/ }),

/***/ "./src/blocks/TestDemoUCSC.js":
/*!************************************!*\
  !*** ./src/blocks/TestDemoUCSC.js ***!
  \************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);


var TestDemoUCSC = function TestDemoUCSC() {
  wp.blocks.registerBlockType("ucscblocks/gutenberg", {
    title: "Test Demo UCSC",
    icon: "smiley",
    category: "common",
    attributes: {
      skyColor: {
        type: "string"
      },
      grassColor: {
        type: "string"
      }
    },
    edit: function edit(_ref) {
      var setAttributes = _ref.setAttributes,
          attributes = _ref.attributes;
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Fragment"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("input", {
        type: "text",
        placeholder: "sky",
        value: attributes.skyColor,
        onChange: function onChange(e) {
          return setAttributes({
            skyColor: e.target.value
          });
        }
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("input", {
        type: "text",
        placeholder: "grass",
        value: attributes.grassColor,
        onChange: function onChange(e) {
          return setAttributes({
            grassColor: e.target.value
          });
        }
      }));
    },
    save: function save(props) {
      return null;
    }
  });
};

/* harmony default export */ __webpack_exports__["default"] = (TestDemoUCSC);

/***/ }),

/***/ "./src/blocks/TestDemoUCSC2.js":
/*!*************************************!*\
  !*** ./src/blocks/TestDemoUCSC2.js ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_Hello__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../components/Hello */ "./src/components/Hello.js");



var TestDemoUCSC = function TestDemoUCSC() {
  wp.blocks.registerBlockType("ucscblocks/block2", {
    title: "Test Demo 2 UCSC",
    icon: "smiley",
    category: "common",
    attributes: {
      skyColor: {
        type: "string"
      },
      grassColor: {
        type: "string"
      }
    },
    edit: function edit(_ref) {
      var setAttributes = _ref.setAttributes,
          attributes = _ref.attributes;
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Fragment"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_components_Hello__WEBPACK_IMPORTED_MODULE_1__["default"], null), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("input", {
        type: "text",
        placeholder: "sky",
        value: attributes.skyColor,
        onChange: function onChange(e) {
          return setAttributes({
            skyColor: e.target.value
          });
        }
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("input", {
        type: "text",
        placeholder: "grass",
        value: attributes.grassColor,
        onChange: function onChange(e) {
          return setAttributes({
            grassColor: e.target.value
          });
        }
      }));
    },
    save: function save(props) {
      return null;
    }
  });
};

/* harmony default export */ __webpack_exports__["default"] = (TestDemoUCSC);

/***/ }),

/***/ "./src/components/Hello.js":
/*!*********************************!*\
  !*** ./src/components/Hello.js ***!
  \*********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);


var Hello = function Hello() {
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("p", null, "Hello"));
};

/* harmony default export */ __webpack_exports__["default"] = (Hello);

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _blocks_TestDemoUCSC__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./blocks/TestDemoUCSC */ "./src/blocks/TestDemoUCSC.js");
/* harmony import */ var _blocks_TestDemoUCSC2__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./blocks/TestDemoUCSC2 */ "./src/blocks/TestDemoUCSC2.js");
/* harmony import */ var _blocks_ContentSharer__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./blocks/ContentSharer */ "./src/blocks/ContentSharer.js");
/* harmony import */ var _blocks_CourseCatalog__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./blocks/CourseCatalog */ "./src/blocks/CourseCatalog.js");




Object(_blocks_TestDemoUCSC__WEBPACK_IMPORTED_MODULE_0__["default"])();
Object(_blocks_TestDemoUCSC2__WEBPACK_IMPORTED_MODULE_1__["default"])();
Object(_blocks_ContentSharer__WEBPACK_IMPORTED_MODULE_2__["default"])();
Object(_blocks_CourseCatalog__WEBPACK_IMPORTED_MODULE_3__["default"])();

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = window["wp"]["components"]; }());

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = window["wp"]["element"]; }());

/***/ })

/******/ });
//# sourceMappingURL=index.js.map