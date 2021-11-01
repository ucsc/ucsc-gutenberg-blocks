/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/blocks/ContentSharer.js":
/*!*************************************!*\
  !*** ./src/blocks/ContentSharer.js ***!
  \*************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);




const TestDemoUCSC = () => {
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
    edit: ({
      setAttributes,
      attributes
    }) => {
      const {
        siteid,
        postType
      } = attributes;
      let selectedSite, setSelectedSite;

      if (typeof siteid === 'undefined') {
        setAttributes({
          siteid: 1
        });
        [selectedSite, setSelectedSite] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(1);
      } else {
        [selectedSite, setSelectedSite] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(siteid);
      }

      let selectedPostType, setSelectedPostType;

      if (typeof postType === 'undefined') {
        setAttributes({
          postType: ""
        });
        [selectedPostType, setSelectedPostType] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)("");
      } else {
        [selectedPostType, setSelectedPostType] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(postType);
      }

      const [sites, setSites] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
      const [postTypes, setPostTypes] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);

      const fetchPostTypes = siteid => {
        fetch('/wp-json/ucscgutenbergblocks/v1/posttypes?siteid=' + siteid).then(res => res.text()).then(text => {
          const postTypes = JSON.parse(text);
          const pTypes = [];
          Object.keys(postTypes).map(type => {
            pTypes.push({
              label: type,
              value: type
            });
          });
          setPostTypes(pTypes);
        });
      };

      (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
        fetch('/wp-json/ucscgutenbergblocks/v1/sites').then(res => res.text()).then(text => {
          setSites(JSON.parse(text));
        });
        fetchPostTypes(selectedSite);
      }, []);
      const options = sites.map(site => {
        return {
          label: site.domain,
          value: site.blog_id
        };
      });

      const userSelectedASite = e => {
        setSelectedSite(e.target.value);
        setAttributes({
          siteid: e.target.value
        });
        fetchPostTypes(e.target.value);
      };

      const userSelectedAPostType = e => {
        setSelectedPostType(e.target.value);
        setAttributes({
          postType: e.target.value
        });
      };

      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, "Content Sharerer"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h4", null, "Site List"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
        value: selectedSite,
        onChange: userSelectedASite
      }, options.map(option => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: option.value
      }, option.label))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h4", null, "Post Types"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
        value: selectedPostType,
        onChange: userSelectedAPostType
      }, postTypes.map(type => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: type.value
      }, type.label))));
    },
    save: props => {
      return null;
    }
  });
};

/* harmony default export */ __webpack_exports__["default"] = (TestDemoUCSC);

/***/ }),

/***/ "./src/blocks/TestDemoUCSC.js":
/*!************************************!*\
  !*** ./src/blocks/TestDemoUCSC.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);


const TestDemoUCSC = () => {
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
    edit: ({
      setAttributes,
      attributes
    }) => {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "text",
        placeholder: "sky",
        value: attributes.skyColor,
        onChange: e => setAttributes({
          skyColor: e.target.value
        })
      }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "text",
        placeholder: "grass",
        value: attributes.grassColor,
        onChange: e => setAttributes({
          grassColor: e.target.value
        })
      }));
    },
    save: props => {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_Hello__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../components/Hello */ "./src/components/Hello.js");



const TestDemoUCSC = () => {
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
    edit: ({
      setAttributes,
      attributes
    }) => {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_Hello__WEBPACK_IMPORTED_MODULE_1__["default"], null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "text",
        placeholder: "sky",
        value: attributes.skyColor,
        onChange: e => setAttributes({
          skyColor: e.target.value
        })
      }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "text",
        placeholder: "grass",
        value: attributes.grassColor,
        onChange: e => setAttributes({
          grassColor: e.target.value
        })
      }));
    },
    save: props => {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);


const Hello = () => {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "Hello"));
};

/* harmony default export */ __webpack_exports__["default"] = (Hello);

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _blocks_TestDemoUCSC__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./blocks/TestDemoUCSC */ "./src/blocks/TestDemoUCSC.js");
/* harmony import */ var _blocks_TestDemoUCSC2__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./blocks/TestDemoUCSC2 */ "./src/blocks/TestDemoUCSC2.js");
/* harmony import */ var _blocks_ContentSharer__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./blocks/ContentSharer */ "./src/blocks/ContentSharer.js");



(0,_blocks_TestDemoUCSC__WEBPACK_IMPORTED_MODULE_0__["default"])();
(0,_blocks_TestDemoUCSC2__WEBPACK_IMPORTED_MODULE_1__["default"])();
(0,_blocks_ContentSharer__WEBPACK_IMPORTED_MODULE_2__["default"])();
}();
/******/ })()
;
//# sourceMappingURL=index.js.map