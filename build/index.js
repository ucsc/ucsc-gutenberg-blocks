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
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _components_DepartmentDropdown__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components/DepartmentDropdown */ "./src/components/DepartmentDropdown.js");
/* harmony import */ var _components_DivisionDropdown__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../components/DivisionDropdown */ "./src/components/DivisionDropdown.js");
/* harmony import */ var _components_CampusDirectory_PageLayout__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../components/CampusDirectory/PageLayout */ "./src/components/CampusDirectory/PageLayout.js");
/* harmony import */ var _components_CampusDirectory_PeopleAndInformation__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../components/CampusDirectory/PeopleAndInformation */ "./src/components/CampusDirectory/PeopleAndInformation.js");









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
      },
      strInformationTypesTable: {
        type: 'string'
      },
      department: {
        type: "string"
      },
      division: {
        type: "string"
      },
      deptOrDiv: {
        type: "string",
        default: "dept"
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
          strInformationTypes = attributes.strInformationTypes,
          strInformationTypesTable = attributes.strInformationTypesTable,
          department = attributes.department,
          division = attributes.division,
          deptOrDiv = attributes.deptOrDiv;

      var _useState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])(true),
          _useState2 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState, 2),
          configuredCorrectly = _useState2[0],
          setConfiguredCorrectly = _useState2[1];

      var _useState3 = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])({}),
          _useState4 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState3, 2),
          resp = _useState4[0],
          setResp = _useState4[1];

      Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useEffect"])(function () {
        fetch('/wp-json/ucscgutenbergblocks/v1/campusdirectoryrequirements').then(function (res) {
          return res.text();
        }).then(function (text) {
          var resp = JSON.parse(text);
          if (!resp.ldap_pass || !resp.deptdiv) setConfiguredCorrectly(false);
          setResp(resp);
        });
      }, []);
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["Fragment"], null, configuredCorrectly && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["Panel"], {
        className: "directory-block",
        header: "Directory Block"
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["PanelBody"], {
        title: "Set Department or Division",
        initialOpen: true
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["RadioControl"], {
        selected: deptOrDiv,
        options: [{
          label: 'Department',
          value: 'dept'
        }, {
          label: 'Division',
          value: 'div'
        }],
        onChange: function onChange(value) {
          return setAttributes({
            deptOrDiv: value
          });
        }
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("hr", null), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_components_DepartmentDropdown__WEBPACK_IMPORTED_MODULE_3__["default"], {
        label: "Department",
        department: department,
        setAttributes: setAttributes,
        disabled: !(deptOrDiv === 'dept')
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_components_DivisionDropdown__WEBPACK_IMPORTED_MODULE_4__["default"], {
        label: "Division",
        division: division,
        setAttributes: setAttributes,
        disabled: !(deptOrDiv === 'div')
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["PanelBody"], {
        title: "Layout Type",
        initialOpen: true
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["PanelRow"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_components_CampusDirectory_PageLayout__WEBPACK_IMPORTED_MODULE_5__["default"], {
        setAttributes: setAttributes,
        pageLayout: pageLayout
      }))), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["PanelBody"], {
        title: "People and Information to Display",
        initialOpen: true
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["PanelRow"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_components_CampusDirectory_PeopleAndInformation__WEBPACK_IMPORTED_MODULE_6__["default"], {
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
        strInformationTypes: strInformationTypes,
        strInformationTypesTable: strInformationTypesTable,
        pageLayout: pageLayout
      })))), !configuredCorrectly && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["Fragment"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h2", null, "This Block is not Configured Correctly"), !resp.ldap_pass && resp.multisite && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["Fragment"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h4", null, "The LDAP password can be set at the network level ", Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("a", {
        target: "_blank",
        href: "/wp-admin/network/settings.php?page=ucsc-gutenberg-blocks-network-settings"
      }, "here.")), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h6", null, "Or the LDAP password can be set at the site level below.")), !resp.ldap_pass && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h4", null, "The LDAP password needs to be set here ", Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("a", {
        target: "_blank",
        href: "/wp-admin/options-general.php?page=ucsc_gutenberg_blocks_settings_page"
      }, "here."))));
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
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_DepartmentDropdown__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components/DepartmentDropdown */ "./src/components/DepartmentDropdown.js");




var ClassSchedule = function ClassSchedule() {
  wp.blocks.registerBlockType("ucscblocks/classschedule", {
    title: "Class Schedule",
    icon: "smiley",
    category: "common",
    attributes: {
      department: {
        type: "string"
      }
    },
    edit: function edit(_ref) {
      var setAttributes = _ref.setAttributes,
          attributes = _ref.attributes;
      var department = attributes.department;
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Fragment"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["Panel"], {
        header: "Class Schedule Block"
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["PanelBody"], {
        title: "Set Subject",
        initialOpen: true
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_components_DepartmentDropdown__WEBPACK_IMPORTED_MODULE_2__["default"], {
        label: "Subject",
        department: department,
        setAttributes: setAttributes
      }))));
    },
    save: function save(props) {
      return null;
    }
  });
};

/* harmony default export */ __webpack_exports__["default"] = (ClassSchedule);

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
/* harmony import */ var _components_DepartmentDropdown__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components/DepartmentDropdown */ "./src/components/DepartmentDropdown.js");




var CourseCatalog = function CourseCatalog() {
  wp.blocks.registerBlockType("ucscblocks/coursecatalog", {
    title: "Course Catalog",
    icon: "book-alt",
    category: "common",
    attributes: {
      department: {
        type: "string"
      }
    },
    edit: function edit(_ref) {
      var setAttributes = _ref.setAttributes,
          attributes = _ref.attributes;
      var department = attributes.department;
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Fragment"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["Panel"], {
        header: "Course Catalog Block"
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__["PanelBody"], {
        title: "Set Subject",
        initialOpen: true
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_components_DepartmentDropdown__WEBPACK_IMPORTED_MODULE_2__["default"], {
        label: "Subject",
        department: department,
        setAttributes: setAttributes
      }))));
    },
    save: function save(props) {
      return null;
    }
  });
};

/* harmony default export */ __webpack_exports__["default"] = (CourseCatalog);

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
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", null, "Link Name to Individual Profile?"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("p", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("i", null, "In a small number of cases, units do not want a person's name to link to a more detailed profile page.")), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
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

/***/ "./src/components/CampusDirectory/InformationToDisplayTable.js":
/*!*********************************************************************!*\
  !*** ./src/components/CampusDirectory/InformationToDisplayTable.js ***!
  \*********************************************************************/
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






var InformationToDisplay = function InformationToDisplay(_ref) {
  var setAttributes = _ref.setAttributes,
      linkToProfile = _ref.linkToProfile,
      strInformationTypesTable = _ref.strInformationTypesTable;
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

  var arrInformationToDisplay = ['Pronouns', 'Title', 'Department', 'Phone', 'Email', 'Fax', 'Website', 'Office Location', 'Office Hours', 'Mailstop', 'Mailing Address', 'Faculty Areas of Expertise', 'Summary of Expertise'];
  var checkedByDefault = ['Title', 'Department', 'Phone', 'Email'];
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", null, "Link Name to Individual Profile?"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("p", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("i", null, "In a small number of cases, units do not want a person's name to link to a more detailed profile page.")), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
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
    currentAttributes: strInformationTypesTable,
    arrOfLabels: arrInformationToDisplay,
    attributeStr: "strInformationTypesTable",
    checkedByDefault: checkedByDefault
  })));
};

/* harmony default export */ __webpack_exports__["default"] = (InformationToDisplay);

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
/* harmony import */ var _InformationToDisplayTable__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./InformationToDisplayTable */ "./src/components/CampusDirectory/InformationToDisplayTable.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__);








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
      strInformationTypes = _ref.strInformationTypes,
      strInformationTypesTable = _ref.strInformationTypesTable,
      pageLayout = _ref.pageLayout;
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
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__["RadioControl"], {
    selected: localAutomatedFeeds,
    onChange: function onChange(newLocalAutomatedFeeds) {
      setLocalAutomatedFeeds(newLocalAutomatedFeeds === 'true');
      setAttributes({
        automatedFeeds: newLocalAutomatedFeeds === 'true'
      });
    },
    options: options
  })), !localAutomatedFeeds && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", null, "List Individuals (Enter CruzIDs separated by commas)"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("p", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("i", null, "List individuals in the order you'd like them to appear on the page.")), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__["TextareaControl"], {
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
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__["RadioControl"], {
    selected: localManualAdd,
    onChange: function onChange(newLocalManualAdd) {
      setLocalManualAdd(newLocalManualAdd === 'true');
      setAttributes({
        manualAdd: newLocalManualAdd === 'true'
      });
    },
    options: manualAddOptions
  })), localManualAdd && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", null, "Add Individuals to the Feed. (Enter CruzIDs separated by commas)"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__["TextareaControl"], {
    value: addCruzidsList,
    onChange: function onChange(newAddCruzidsList) {
      setAddCruzidsList(newAddCruzidsList);
      setAttributes({
        addCruzids: newAddCruzidsList
      });
    }
  }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", null, "Exclude Individuals from the Feed. (Enter CruzIDs separated by commas)"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__["TextareaControl"], {
    value: excludeCruzidsList,
    onChange: function onChange(newExcludeCruzidsList) {
      setExcludeCruzidsList(newExcludeCruzidsList);
      setAttributes({
        excludeCruzids: newExcludeCruzidsList
      });
    }
  })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("h5", null, "Display Department Affiliates? (Rare for administrative units.)"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("p", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("i", null, "For example, a faculty member is officially associated with the History Department but is also affiliated with College Nine because they teach a College Nine Core Course.")), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
    className: "flex-labels"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__["CheckboxControl"], {
    label: "Display Affiliates",
    checked: boolDisplayDepartmentAffiliates,
    onChange: function onChange(newValue) {
      setBoolDisplayDepartmentAffiliates(newValue);
      setAttributes({
        displayDeptartmentAffiliates: newValue
      });
    }
  })))), pageLayout !== "table" ? Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_InformationToDisplay__WEBPACK_IMPORTED_MODULE_3__["default"], {
    setAttributes: setAttributes,
    linkToProfile: linkToProfile,
    strInformationTypes: strInformationTypes
  }) : Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_InformationToDisplayTable__WEBPACK_IMPORTED_MODULE_4__["default"], {
    setAttributes: setAttributes,
    linkToProfile: linkToProfile,
    strInformationTypesTable: strInformationTypesTable
  }));
};

/* harmony default export */ __webpack_exports__["default"] = (PeopleAndInformation);

/***/ }),

/***/ "./src/components/DepartmentDropdown.js":
/*!**********************************************!*\
  !*** ./src/components/DepartmentDropdown.js ***!
  \**********************************************/
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





var DepartmentDropdown = function DepartmentDropdown(_ref) {
  var department = _ref.department,
      setAttributes = _ref.setAttributes,
      label = _ref.label,
      _ref$disabled = _ref.disabled,
      disabled = _ref$disabled === void 0 ? false : _ref$disabled;

  var _useState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])([]),
      _useState2 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState, 2),
      departments = _useState2[0],
      setDepartments = _useState2[1];

  Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useEffect"])(function () {
    fetch('/wp-json/ucscgutenbergblocks/v1/departmentcode').then(function (res) {
      return res.text();
    }).then(function (text) {
      var resp = JSON.parse(text);
      setDepartments(resp);
    });
  }, []);
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["Fragment"], null, departments.length > 0 && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
    style: {
      width: "max-content"
    }
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["SelectControl"], {
    label: label,
    value: department,
    options: departments,
    onChange: function onChange(newDepartment) {
      return setAttributes({
        department: newDepartment
      });
    },
    disabled: disabled
  })));
};

/* harmony default export */ __webpack_exports__["default"] = (DepartmentDropdown);

/***/ }),

/***/ "./src/components/DivisionDropdown.js":
/*!********************************************!*\
  !*** ./src/components/DivisionDropdown.js ***!
  \********************************************/
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





var DivisionDropdown = function DivisionDropdown(_ref) {
  var division = _ref.division,
      setAttributes = _ref.setAttributes,
      label = _ref.label,
      _ref$disabled = _ref.disabled,
      disabled = _ref$disabled === void 0 ? false : _ref$disabled;

  var _useState = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useState"])([]),
      _useState2 = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_useState, 2),
      divisions = _useState2[0],
      setDivisions = _useState2[1];

  Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["useEffect"])(function () {
    fetch('/wp-json/ucscgutenbergblocks/v1/divisioncode').then(function (res) {
      return res.text();
    }).then(function (text) {
      var resp = JSON.parse(text);
      setDivisions(resp);
    });
  }, []);
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["Fragment"], null, divisions.length > 0 && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])("div", {
    style: {
      width: "max-content"
    }
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["SelectControl"], {
    label: label,
    value: division,
    options: divisions,
    onChange: function onChange(newDivisions) {
      return setAttributes({
        division: newDivisions
      });
    },
    disabled: disabled
  })));
};

/* harmony default export */ __webpack_exports__["default"] = (DivisionDropdown);

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _blocks_CourseCatalog__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./blocks/CourseCatalog */ "./src/blocks/CourseCatalog.js");
/* harmony import */ var _blocks_CampusDirectory__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./blocks/CampusDirectory */ "./src/blocks/CampusDirectory.js");
/* harmony import */ var _blocks_ClassSchedule__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./blocks/ClassSchedule */ "./src/blocks/ClassSchedule.js");
// import TestDemoUCSC from './blocks/TestDemoUCSC';
// import TestDemoUCSC2 from './blocks/TestDemoUCSC2';
// import ContentSharer from './blocks/ContentSharer';


 // import FeedbackForm from './blocks/FeedbackForm';
// TestDemoUCSC();
// TestDemoUCSC2();
// ContentSharer();

Object(_blocks_CourseCatalog__WEBPACK_IMPORTED_MODULE_0__["default"])();
Object(_blocks_CampusDirectory__WEBPACK_IMPORTED_MODULE_1__["default"])();
Object(_blocks_ClassSchedule__WEBPACK_IMPORTED_MODULE_2__["default"])(); // FeedbackForm();

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