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

/***/ "./src/blocks/CampusDirectory.js":
/*!***************************************!*\
  !*** ./src/blocks/CampusDirectory.js ***!
  \***************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_CampusDirectory_IntroParagraph__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components/CampusDirectory/IntroParagraph */ "./src/components/CampusDirectory/IntroParagraph.js");
/* harmony import */ var _components_CampusDirectory_PageLayout__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components/CampusDirectory/PageLayout */ "./src/components/CampusDirectory/PageLayout.js");
/* harmony import */ var _components_CampusDirectory_PeopleAndInformation__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../components/CampusDirectory/PeopleAndInformation */ "./src/components/CampusDirectory/PeopleAndInformation.js");






var CampusDirectory = function CampusDirectory() {
  wp.blocks.registerBlockType("ucscblocks/campusdirectory", {
    title: "Campus Directory",
    icon: "welcome-learn-more",
    category: "common",
    attributes: {
      boolIntroParagraph: {
        type: 'boolean'
      },
      introParagraph: {
        type: 'string'
      },
      pageLayout: {
        type: 'string'
      },
      automatedFeeds: {
        type: 'boolean'
      },
      cruzidList: {
        type: 'string'
      },
      strFacultyTypes: {
        type: 'string'
      },
      strStaffTypes: {
        type: 'string'
      },
      strGradTypes: {
        type: 'string'
      },
      manualAdd: {
        type: 'boolean'
      },
      addCruzids: {
        type: 'string'
      },
      excludeCruzids: {
        type: 'string'
      },
      displayDeptartmentAffiliates: {
        type: 'boolean'
      },
      linkToProfile: {
        type: 'boolean'
      },
      strInformationTypes: {
        type: 'string'
      }
    },
    edit: function edit(_ref) {
      var setAttributes = _ref.setAttributes,
          attributes = _ref.attributes;
      var boolIntroParagraph = attributes.boolIntroParagraph,
          introParagraph = attributes.introParagraph,
          pageLayout = attributes.pageLayout,
          automatedFeeds = attributes.automatedFeeds,
          cruzidList = attributes.cruzidList,
          strFacultyTypes = attributes.strFacultyTypes,
          strStaffTypes = attributes.strStaffTypes,
          strGradTypes = attributes.strGradTypes,
          manualAdd = attributes.manualAdd,
          addCruzids = attributes.addCruzids,
          excludeCruzids = attributes.excludeCruzids,
          displayDeptartmentAffiliates = attributes.displayDeptartmentAffiliates,
          linkToProfile = attributes.linkToProfile,
          strInformationTypes = attributes.strInformationTypes;
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Fragment"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["Panel"], {
        header: "Directory Block"
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["PanelBody"], {
        title: "Intro Paragraph"
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_components_CampusDirectory_IntroParagraph__WEBPACK_IMPORTED_MODULE_2__["default"], {
        setAttributes: setAttributes,
        boolIntroParagraph: boolIntroParagraph,
        introParagraph: introParagraph
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["PanelBody"], {
        title: "Page Layout",
        initialOpen: true
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["PanelRow"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_components_CampusDirectory_PageLayout__WEBPACK_IMPORTED_MODULE_3__["default"], {
        setAttributes: setAttributes,
        pageLayout: pageLayout
      }))), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["PanelBody"], {
        title: "People and Information to Display",
        initialOpen: true
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["PanelRow"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_components_CampusDirectory_PeopleAndInformation__WEBPACK_IMPORTED_MODULE_4__["default"], {
        setAttributes: setAttributes,
        automatedFeeds: automatedFeeds,
        cruzidList: cruzidList,
        strFacultyTypes: strFacultyTypes,
        strStaffTypes: strStaffTypes,
        strGradTypes: strGradTypes,
        manualAdd: manualAdd,
        addCruzids: addCruzids,
        excludeCruzids: excludeCruzids,
        displayDeptartmentAffiliates: displayDeptartmentAffiliates,
        linkToProfile: linkToProfile,
        strInformationTypes: strInformationTypes
      })))));
    },
    save: function save(props) {
      return null;
    }
  });
};

/* harmony default export */ __webpack_exports__["default"] = (CampusDirectory);

/***/ }),

/***/ "./src/blocks/ClassSchedule.js":
/*!*************************************!*\
  !*** ./src/blocks/ClassSchedule.js ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
<<<<<<< Updated upstream
<<<<<<< Updated upstream
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
=======
=======
>>>>>>> Stashed changes
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);

<<<<<<< Updated upstream
>>>>>>> Stashed changes


var ClassSchedule = function ClassSchedule() {
  wp.blocks.registerBlockType("ucscblocks/classschedule", {
    title: "Class",
    icon: "smiley",
    category: "common",
    attributes: {},
    edit: function edit(_ref) {
      var setAttributes = _ref.setAttributes,
          attributes = _ref.attributes;
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Fragment"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("h2", null, "Class Schedule Edit Block"));
    },
    save: function save(props) {
      return null;
    }
  });
};

/* harmony default export */ __webpack_exports__["default"] = (ClassSchedule);
=======
>>>>>>> Stashed changes

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


<<<<<<< Updated upstream

<<<<<<< Updated upstream


=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======
=======
>>>>>>> Stashed changes
      console.log('here');
>>>>>>> Stashed changes
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

/***/ "./src/components/CampusDirectory/AutomatedFeeds.js":
/*!**********************************************************!*\
  !*** ./src/components/CampusDirectory/AutomatedFeeds.js ***!
  \**********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CheckboxGroupControl */ "./src/components/CampusDirectory/CheckboxGroupControl.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);





var AutomatedFeeds = function AutomatedFeeds(_ref) {
  var setAttributes = _ref.setAttributes,
      strFacultyTypes = _ref.strFacultyTypes,
      strStaffTypes = _ref.strStaffTypes,
      strGradTypes = _ref.strGradTypes;
  // List of checkbox labels/values
  var arrFacultyTypes = ['All', 'Regular Faculty', 'Lecturer', 'Emeriti', 'Research Professor', 'Researcher', 'Adjunct Faculty', 'Visiting Scholar', 'Graduate Student Instructor', 'Retired'];
  var arrStaffTypes = ['Regular Staff', 'Researcher', 'Postdoctoral Scholar'];
  var arrGradTypes = ['Grad Students'];
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
    className: "flex-labels"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("h5", null, "Faculty Types"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__["default"], {
    setAttributes: setAttributes,
    currentAttributes: strFacultyTypes,
    arrOfLabels: arrFacultyTypes,
    attributeStr: "strFacultyTypes"
  }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("h5", null, "Staff Types"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__["default"], {
    setAttributes: setAttributes,
    currentAttributes: strStaffTypes,
    arrOfLabels: arrStaffTypes,
    attributeStr: "strStaffTypes",
    flexCheckboxes: true
  }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("h5", null, "Graduate Students"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__["default"], {
    setAttributes: setAttributes,
    currentAttributes: strGradTypes,
    arrOfLabels: arrGradTypes,
    attributeStr: "strGradTypes"
  }));
};

/* harmony default export */ __webpack_exports__["default"] = (AutomatedFeeds);

/***/ }),

/***/ "./src/components/CampusDirectory/CheckboxGroupControl.js":
/*!****************************************************************!*\
  !*** ./src/components/CampusDirectory/CheckboxGroupControl.js ***!
  \****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);




var CheckboxGroupControl = function CheckboxGroupControl(_ref) {
  var setAttributes = _ref.setAttributes,
      currentAttributes = _ref.currentAttributes,
      arrOfLabels = _ref.arrOfLabels,
      flexCheckboxes = _ref.flexCheckboxes,
      attributeStr = _ref.attributeStr,
      checkedByDefault = _ref.checkedByDefault;
  // Initialize array that will render checkboxes
  var arrRender = [];
  arrOfLabels.map(function (label) {
    return arrRender.push({
      label: label
    });
  }); // If the attribute hasn't been defined yet (creating a new record)
  // initialize strFacultyTypes to a stringafied object of {labels} => false

  if (typeof currentAttributes === 'undefined') {
    var objAttributeLabels = {};
    arrOfLabels.map(function (label) {
      if (typeof checkedByDefault === 'undefined') {
        objAttributeLabels[label] = false;
      } else {
        objAttributeLabels[label] = checkedByDefault.includes(label);
      }
    });
    var attrObj = {};
    attrObj[attributeStr] = JSON.stringify(objAttributeLabels);
    setAttributes(attrObj);
  } // add state vars/fxn to each object to be rendered


  arrRender.map(function (label) {
    if (typeof currentAttributes === 'undefined') {
      if (typeof checkedByDefault === 'undefined') {
        label.arrCheckedState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["useState"])(false);
      } else {
        label.arrCheckedState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["useState"])(checkedByDefault.includes(label.label));
      }
    } else {
      var objLabels = JSON.parse(currentAttributes);
      label.arrCheckedState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["useState"])(objLabels[label.label]);
    }
  });
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
    className: flexCheckboxes ? 'flex-checkboxes' : ''
  }, arrRender.map(function (label) {
    return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["CheckboxControl"], {
      label: label.label,
      checked: label.arrCheckedState[0],
      onChange: function onChange(newValue) {
        label.arrCheckedState[1](newValue);
        var objLabels = JSON.parse(currentAttributes);
        objLabels[label.label] = newValue;
        var attrObj = {};
        attrObj[attributeStr] = JSON.stringify(objLabels);
        setAttributes(attrObj);
      }
    });
  }));
};

/* harmony default export */ __webpack_exports__["default"] = (CheckboxGroupControl);

/***/ }),

/***/ "./src/components/CampusDirectory/InformationToDisplay.js":
/*!****************************************************************!*\
  !*** ./src/components/CampusDirectory/InformationToDisplay.js ***!
  \****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./CheckboxGroupControl */ "./src/components/CampusDirectory/CheckboxGroupControl.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes





<<<<<<< Updated upstream

<<<<<<< Updated upstream
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
var InformationToDisplay = function InformationToDisplay(_ref) {
  var setAttributes = _ref.setAttributes,
      linkToProfile = _ref.linkToProfile,
      strInformationTypes = _ref.strInformationTypes;
  var localLinkToProfile;
  var setLocalLinkToProfile;

  if (typeof linkToProfile === 'undefined') {
    var _useState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(true);

    var _useState2 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState, 2);

    localLinkToProfile = _useState2[0];
    setLocalLinkToProfile = _useState2[1];
    setAttributes({
      linkToProfile: true
    });
  } else {
    var _useState3 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(linkToProfile);

    var _useState4 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState3, 2);

    localLinkToProfile = _useState4[0];
    setLocalLinkToProfile = _useState4[1];
  }

  var options = [{
    label: 'Yes',
    value: true
  }, {
    label: 'No',
    value: false
  }]; // List of checkbox labels/values

  var arrInformationToDisplay = ['Pronouns', 'Photo', 'Title', 'Department', 'Phone', 'Email', 'Fax', 'Website', 'Office Location', 'Office Hours', 'Mailstop', 'Mailing Address', 'Faculty Areas of Expertise', 'Summary of Expertise'];
  var checkedByDefault = ['Photo', 'Title', 'Department', 'Phone', 'Email', 'Website', 'Office Location', 'Office Hours'];
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", {
    style: {
      marginBottom: "0px"
    }
  }, "Link Name to Individual Profile?"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("p", {
    style: {
      marginTop: "0px"
    }
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("i", {
    style: {
      fontSize: "0.85714rem"
    }
  }, "In a small number of cases, units do not want a person's name to link to a more detailed profile page.")), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
    className: "vertical_radio"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["RadioControl"], {
    selected: localLinkToProfile,
    onChange: function onChange(newLocalLinkToProfile) {
      setLocalLinkToProfile(newLocalLinkToProfile === 'true');
      setAttributes({
        linkToProfile: newLocalLinkToProfile === 'true'
      });
    },
    options: options
  })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
    className: "flex-labels"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", null, "List Layout Information to Display"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_2__["default"], {
    setAttributes: setAttributes,
    currentAttributes: strInformationTypes,
    arrOfLabels: arrInformationToDisplay,
    attributeStr: "strInformationTypes",
    checkedByDefault: checkedByDefault
  })));
};

/* harmony default export */ __webpack_exports__["default"] = (InformationToDisplay);

/***/ }),

/***/ "./src/components/CampusDirectory/IntroParagraph.js":
/*!**********************************************************!*\
  !*** ./src/components/CampusDirectory/IntroParagraph.js ***!
  \**********************************************************/
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
<<<<<<< Updated upstream
<<<<<<< Updated upstream

=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes




<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
=======

>>>>>>> Stashed changes
var IntroParagraph = function IntroParagraph(_ref) {
  var boolIntroParagraph = _ref.boolIntroParagraph,
      introParagraph = _ref.introParagraph,
      setAttributes = _ref.setAttributes;
  var showIntro;
  var setShowIntro;

  if (typeof boolIntroParagraph === 'undefined') {
    setAttributes({
      boolIntroParagraph: false
    });

    var _useState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(false);

    var _useState2 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState, 2);

    showIntro = _useState2[0];
    setShowIntro = _useState2[1];
  } else {
    var _useState3 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(boolIntroParagraph);

    var _useState4 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState3, 2);

    showIntro = _useState4[0];
    setShowIntro = _useState4[1];
  }

  var introParagraphState;
  var setIntroParagraphState;

  if (typeof introParagraph === 'undefined') {
    var _useState5 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])('');

    var _useState6 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState5, 2);

    introParagraphState = _useState6[0];
    setIntroParagraphState = _useState6[1];
    setAttributes({
      introParagraph: ''
    });
  } else {
    var _useState7 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(introParagraph);

    var _useState8 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState7, 2);

    introParagraphState = _useState8[0];
    setIntroParagraphState = _useState8[1];
  }

  var options = [{
    label: 'Yes',
    value: true
  }, {
    label: 'No',
    value: false
  }];
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
    id: "introParagraph",
    className: "vertical_radio"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["RadioControl"], {
    selected: showIntro,
    onChange: function onChange(newShowIntro) {
      setShowIntro(newShowIntro === 'true');
      setAttributes({
        boolIntroParagraph: newShowIntro === 'true'
      });
    },
    options: options
  }), showIntro && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["TextareaControl"], {
    value: introParagraphState,
    onChange: function onChange(newIntroParagraph) {
      setIntroParagraphState(newIntroParagraph);
      setAttributes({
        introParagraph: newIntroParagraph
      });
    }
  }));
};

/* harmony default export */ __webpack_exports__["default"] = (IntroParagraph);

/***/ }),

/***/ "./src/components/CampusDirectory/PageLayout.js":
/*!******************************************************!*\
  !*** ./src/components/CampusDirectory/PageLayout.js ***!
  \******************************************************/
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
<<<<<<< Updated upstream

=======
>>>>>>> Stashed changes




<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
var PageLayout = function PageLayout(_ref) {
  var pageLayout = _ref.pageLayout,
      setAttributes = _ref.setAttributes;
  var options = [{
    label: 'List Layout',
    value: 'list'
  }, {
    label: 'Tiled Layout',
    value: 'tiled'
  }, {
    label: 'Table Layout',
    value: 'table'
  }];
  var localPageLayout;
  var setLocalPageLayout;

  if (typeof pageLayout === 'undefined') {
    var _useState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])('list');

    var _useState2 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState, 2);

    localPageLayout = _useState2[0];
    setLocalPageLayout = _useState2[1];
    setAttributes({
      pageLayout: 'list'
    });
  } else {
    var _useState3 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(pageLayout);

    var _useState4 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState3, 2);

    localPageLayout = _useState4[0];
    setLocalPageLayout = _useState4[1];
  }

  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
    className: "vertical_radio"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["RadioControl"], {
    selected: localPageLayout,
    onChange: function onChange(newPageLayout) {
      setLocalPageLayout(newPageLayout);
      setAttributes({
        pageLayout: newPageLayout
      });
    },
    options: options
  }));
};

/* harmony default export */ __webpack_exports__["default"] = (PageLayout);

/***/ }),

/***/ "./src/components/CampusDirectory/PeopleAndInformation.js":
/*!****************************************************************!*\
  !*** ./src/components/CampusDirectory/PeopleAndInformation.js ***!
  \****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _AutomatedFeeds__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AutomatedFeeds */ "./src/components/CampusDirectory/AutomatedFeeds.js");
/* harmony import */ var _InformationToDisplay__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./InformationToDisplay */ "./src/components/CampusDirectory/InformationToDisplay.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);
<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes






<<<<<<< Updated upstream

<<<<<<< Updated upstream
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
var PeopleAndInformation = function PeopleAndInformation(_ref) {
  var setAttributes = _ref.setAttributes,
      automatedFeeds = _ref.automatedFeeds,
      cruzidList = _ref.cruzidList,
      strFacultyTypes = _ref.strFacultyTypes,
      strStaffTypes = _ref.strStaffTypes,
      strGradTypes = _ref.strGradTypes,
      manualAdd = _ref.manualAdd,
      addCruzids = _ref.addCruzids,
      excludeCruzids = _ref.excludeCruzids,
      displayDeptartmentAffiliates = _ref.displayDeptartmentAffiliates,
      linkToProfile = _ref.linkToProfile,
      strInformationTypes = _ref.strInformationTypes;
  var localAutomatedFeeds;
  var setLocalAutomatedFeeds;

  if (typeof automatedFeeds === 'undefined') {
    var _useState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(true);

    var _useState2 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState, 2);

    localAutomatedFeeds = _useState2[0];
    setLocalAutomatedFeeds = _useState2[1];
    setAttributes({
      automatedFeeds: true
    });
  } else {
    var _useState3 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(automatedFeeds);

    var _useState4 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState3, 2);

    localAutomatedFeeds = _useState4[0];
    setLocalAutomatedFeeds = _useState4[1];
  }

  var localManualAdd;
  var setLocalManualAdd;

  if (typeof manualAdd === 'undefined') {
    var _useState5 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(false);

    var _useState6 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState5, 2);

    localManualAdd = _useState6[0];
    setLocalManualAdd = _useState6[1];
    setAttributes({
      manualAdd: false
    });
  } else {
    var _useState7 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(manualAdd);

    var _useState8 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState7, 2);

    localManualAdd = _useState8[0];
    setLocalManualAdd = _useState8[1];
  }

  var cruzidListState;
  var setCruzidListState;

  if (typeof cruzidList === 'undefined') {
    var _useState9 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])('');

    var _useState10 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState9, 2);

    cruzidListState = _useState10[0];
    setCruzidListState = _useState10[1];
    setAttributes({
      cruzidList: ''
    });
  } else {
    var _useState11 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(cruzidList);

    var _useState12 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState11, 2);

    cruzidListState = _useState12[0];
    setCruzidListState = _useState12[1];
  }

  var addCruzidsList;
  var setAddCruzidsList;

  if (typeof addCruzids === 'undefined') {
    var _useState13 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])('');

    var _useState14 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState13, 2);

    addCruzidsList = _useState14[0];
    setAddCruzidsList = _useState14[1];
    setAttributes({
      addCruzids: ''
    });
  } else {
    var _useState15 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(addCruzids);

    var _useState16 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState15, 2);

    addCruzidsList = _useState16[0];
    setAddCruzidsList = _useState16[1];
  }

  var excludeCruzidsList;
  var setExcludeCruzidsList;

  if (typeof excludeCruzids === 'undefined') {
    var _useState17 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])('');

    var _useState18 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState17, 2);

    excludeCruzidsList = _useState18[0];
    setExcludeCruzidsList = _useState18[1];
    setAttributes({
      excludeCruzids: ''
    });
  } else {
    var _useState19 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(excludeCruzids);

    var _useState20 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState19, 2);

    excludeCruzidsList = _useState20[0];
    setExcludeCruzidsList = _useState20[1];
  }

  var boolDisplayDepartmentAffiliates;
  var setBoolDisplayDepartmentAffiliates;

  if (typeof displayDeptartmentAffiliates === 'undefined') {
    var _useState21 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(false);

    var _useState22 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState21, 2);

    boolDisplayDepartmentAffiliates = _useState22[0];
    setBoolDisplayDepartmentAffiliates = _useState22[1];
    setAttributes({
      displayDeptartmentAffiliates: false
    });
  } else {
    var _useState23 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(displayDeptartmentAffiliates);

    var _useState24 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState23, 2);

    boolDisplayDepartmentAffiliates = _useState24[0];
    setBoolDisplayDepartmentAffiliates = _useState24[1];
  }

  var options = [{
    label: 'Use Automated Feed(s)',
    value: true
  }, {
    label: 'Create My Own List of People to Display',
    value: false
  }];
  var manualAddOptions = [{
    label: 'Yes',
    value: true
  }, {
    label: 'No',
    value: false
  }];
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
    className: "vertical_radio"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["RadioControl"], {
    selected: localAutomatedFeeds,
    onChange: function onChange(newLocalAutomatedFeeds) {
      setLocalAutomatedFeeds(newLocalAutomatedFeeds === 'true');
      setAttributes({
        automatedFeeds: newLocalAutomatedFeeds === 'true'
      });
    },
    options: options
  })), !localAutomatedFeeds && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["TextareaControl"], {
    value: cruzidListState,
    onChange: function onChange(newCruzidListState) {
      setCruzidListState(newCruzidListState);
      setAttributes({
        cruzidList: newCruzidListState
      });
    }
  })), localAutomatedFeeds && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_AutomatedFeeds__WEBPACK_IMPORTED_MODULE_2__["default"], {
    setAttributes: setAttributes,
    strFacultyTypes: strFacultyTypes,
    strStaffTypes: strStaffTypes,
    strGradTypes: strGradTypes
  }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", null, "Manually Add or Exclude Individuals from the feed?"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
    className: "vertical_radio"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["RadioControl"], {
    selected: localManualAdd,
    onChange: function onChange(newLocalManualAdd) {
      setLocalManualAdd(newLocalManualAdd === 'true');
      setAttributes({
        manualAdd: newLocalManualAdd === 'true'
      });
    },
    options: manualAddOptions
  })), localManualAdd && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", null, "Add Individuals to the Feed. (Enter CruzIDs separated by commas)"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["TextareaControl"], {
    value: addCruzidsList,
    onChange: function onChange(newAddCruzidsList) {
      setAddCruzidsList(newAddCruzidsList);
      setAttributes({
        addCruzids: newAddCruzidsList
      });
    }
  }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", null, "Exclude Individuals from the Feed. (Enter CruzIDs separated by commas)"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["TextareaControl"], {
    value: excludeCruzidsList,
    onChange: function onChange(newExcludeCruzidsList) {
      setExcludeCruzidsList(newExcludeCruzidsList);
      setAttributes({
        excludeCruzids: newExcludeCruzidsList
      });
    }
  })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", {
    style: {
      marginBottom: "0px"
    }
  }, "Display Department Affiliates? (Rare for administrative units.)"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("p", {
    style: {
      marginTop: "0px"
    }
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("i", {
    style: {
      fontSize: "0.85714rem"
    }
  }, "For example, a faculty member is officially associated with the History Department but is also affiliated with College Nine because they teach a College Nine Core Course.")), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
    className: "flex-labels"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["CheckboxControl"], {
    label: "Display Affiliates",
    checked: boolDisplayDepartmentAffiliates,
    onChange: function onChange(newValue) {
      setBoolDisplayDepartmentAffiliates(newValue);
      setAttributes({
        displayDeptartmentAffiliates: newValue
      });
    }
  })))), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_InformationToDisplay__WEBPACK_IMPORTED_MODULE_3__["default"], {
    setAttributes: setAttributes,
    linkToProfile: linkToProfile,
    strInformationTypes: strInformationTypes
  }));
};

/* harmony default export */ __webpack_exports__["default"] = (PeopleAndInformation);

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
/* harmony import */ var _blocks_CampusDirectory__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./blocks/CampusDirectory */ "./src/blocks/CampusDirectory.js");
<<<<<<< Updated upstream
<<<<<<< Updated upstream
/* harmony import */ var _blocks_ClassSchedule__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./blocks/ClassSchedule */ "./src/blocks/ClassSchedule.js");

=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes





Object(_blocks_TestDemoUCSC__WEBPACK_IMPORTED_MODULE_0__["default"])();
Object(_blocks_TestDemoUCSC2__WEBPACK_IMPORTED_MODULE_1__["default"])();
Object(_blocks_ContentSharer__WEBPACK_IMPORTED_MODULE_2__["default"])();
Object(_blocks_CourseCatalog__WEBPACK_IMPORTED_MODULE_3__["default"])();
Object(_blocks_CampusDirectory__WEBPACK_IMPORTED_MODULE_4__["default"])();
<<<<<<< Updated upstream
<<<<<<< Updated upstream
Object(_blocks_ClassSchedule__WEBPACK_IMPORTED_MODULE_5__["default"])();
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes

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