/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/blocks/CampusDirectory.js":
/*!***************************************!*\
  !*** ./src/blocks/CampusDirectory.js ***!
  \***************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_CampusDirectory_IntroParagraph__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components/CampusDirectory/IntroParagraph */ "./src/components/CampusDirectory/IntroParagraph.js");
/* harmony import */ var _components_CampusDirectory_PageLayout__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components/CampusDirectory/PageLayout */ "./src/components/CampusDirectory/PageLayout.js");
/* harmony import */ var _components_CampusDirectory_PeopleAndInformation__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../components/CampusDirectory/PeopleAndInformation */ "./src/components/CampusDirectory/PeopleAndInformation.js");






const CampusDirectory = () => {
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
    edit: ({
      setAttributes,
      attributes
    }) => {
      const {
        boolIntroParagraph,
        introParagraph,
        pageLayout,
        automatedFeeds,
        cruzidList,
        strFacultyTypes,
        strStaffTypes,
        strGradTypes,
        manualAdd,
        addCruzids,
        excludeCruzids,
        displayDeptartmentAffiliates,
        linkToProfile,
        strInformationTypes
      } = attributes;
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Panel, {
        header: "Directory Block"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelBody, {
        title: "Intro Paragraph"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_CampusDirectory_IntroParagraph__WEBPACK_IMPORTED_MODULE_2__["default"], {
        setAttributes: setAttributes,
        boolIntroParagraph: boolIntroParagraph,
        introParagraph: introParagraph
      })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelBody, {
        title: "Page Layout",
        initialOpen: true
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelRow, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_CampusDirectory_PageLayout__WEBPACK_IMPORTED_MODULE_3__["default"], {
        setAttributes: setAttributes,
        pageLayout: pageLayout
      }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelBody, {
        title: "People and Information to Display",
        initialOpen: true
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelRow, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_CampusDirectory_PeopleAndInformation__WEBPACK_IMPORTED_MODULE_4__["default"], {
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
    save: props => {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);


const ClassSchedule = () => {
  wp.blocks.registerBlockType("ucscblocks/classschedule", {
    title: "Class",
    icon: "smiley",
    category: "common",
    attributes: {},
    edit: ({
      setAttributes,
      attributes
    }) => {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, "Class Schedule Edit Block"));
    },
    save: props => {
      return null;
    }
  });
};

/* harmony default export */ __webpack_exports__["default"] = (ClassSchedule);

/***/ }),

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

/***/ "./src/blocks/CourseCatalog.js":
/*!*************************************!*\
  !*** ./src/blocks/CourseCatalog.js ***!
  \*************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);




const CourseCatalog = () => {
  wp.blocks.registerBlockType("ucscblocks/coursecatalog", {
    title: "Course Catalog",
    icon: "book-alt",
    category: "common",
    attributes: {
      subject: {
        type: "string"
      }
    },
    edit: ({
      setAttributes,
      attributes
    }) => {
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

      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, "Course Catalog"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h4", null, "subject"));
    },
    save: props => {
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

/***/ "./src/components/CampusDirectory/AutomatedFeeds.js":
/*!**********************************************************!*\
  !*** ./src/components/CampusDirectory/AutomatedFeeds.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CheckboxGroupControl */ "./src/components/CampusDirectory/CheckboxGroupControl.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);





const AutomatedFeeds = ({
  setAttributes,
  strFacultyTypes,
  strStaffTypes,
  strGradTypes
}) => {
  // List of checkbox labels/values
  const arrFacultyTypes = ['All', 'Regular Faculty', 'Lecturer', 'Emeriti', 'Research Professor', 'Researcher', 'Adjunct Faculty', 'Visiting Scholar', 'Graduate Student Instructor', 'Retired'];
  const arrStaffTypes = ['Regular Staff', 'Researcher', 'Postdoctoral Scholar'];
  const arrGradTypes = ['Grad Students'];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex-labels"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Faculty Types"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__["default"], {
    setAttributes: setAttributes,
    currentAttributes: strFacultyTypes,
    arrOfLabels: arrFacultyTypes,
    attributeStr: "strFacultyTypes"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Staff Types"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__["default"], {
    setAttributes: setAttributes,
    currentAttributes: strStaffTypes,
    arrOfLabels: arrStaffTypes,
    attributeStr: "strStaffTypes",
    flexCheckboxes: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Graduate Students"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__["default"], {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);




const CheckboxGroupControl = ({
  setAttributes,
  currentAttributes,
  arrOfLabels,
  flexCheckboxes,
  attributeStr,
  checkedByDefault
}) => {
  // Initialize array that will render checkboxes
  const arrRender = [];
  arrOfLabels.map(label => arrRender.push({
    label
  })); // If the attribute hasn't been defined yet (creating a new record)
  // initialize strFacultyTypes to a stringafied object of {labels} => false

  if (typeof currentAttributes === 'undefined') {
    const objAttributeLabels = {};
    arrOfLabels.map(label => {
      if (typeof checkedByDefault === 'undefined') {
        objAttributeLabels[label] = false;
      } else {
        objAttributeLabels[label] = checkedByDefault.includes(label);
      }
    });
    const attrObj = {};
    attrObj[attributeStr] = JSON.stringify(objAttributeLabels);
    setAttributes(attrObj);
  } // add state vars/fxn to each object to be rendered


  arrRender.map(label => {
    if (typeof currentAttributes === 'undefined') {
      if (typeof checkedByDefault === 'undefined') {
        label.arrCheckedState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
      } else {
        label.arrCheckedState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(checkedByDefault.includes(label.label));
      }
    } else {
      const objLabels = JSON.parse(currentAttributes);
      label.arrCheckedState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(objLabels[label.label]);
    }
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: flexCheckboxes ? 'flex-checkboxes' : ''
  }, arrRender.map(label => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CheckboxControl, {
    label: label.label,
    checked: label.arrCheckedState[0],
    onChange: newValue => {
      label.arrCheckedState[1](newValue);
      const objLabels = JSON.parse(currentAttributes);
      objLabels[label.label] = newValue;
      const attrObj = {};
      attrObj[attributeStr] = JSON.stringify(objLabels);
      setAttributes(attrObj);
    }
  })));
};

/* harmony default export */ __webpack_exports__["default"] = (CheckboxGroupControl);

/***/ }),

/***/ "./src/components/CampusDirectory/InformationToDisplay.js":
/*!****************************************************************!*\
  !*** ./src/components/CampusDirectory/InformationToDisplay.js ***!
  \****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CheckboxGroupControl */ "./src/components/CampusDirectory/CheckboxGroupControl.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);





const InformationToDisplay = ({
  setAttributes,
  linkToProfile,
  strInformationTypes
}) => {
  let localLinkToProfile;
  let setLocalLinkToProfile;

  if (typeof linkToProfile === 'undefined') {
    [localLinkToProfile, setLocalLinkToProfile] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(true);
    setAttributes({
      linkToProfile: true
    });
  } else {
    [localLinkToProfile, setLocalLinkToProfile] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(linkToProfile);
  }

  const options = [{
    label: 'Yes',
    value: true
  }, {
    label: 'No',
    value: false
  }]; // List of checkbox labels/values

  const arrInformationToDisplay = ['Pronouns', 'Photo', 'Title', 'Department', 'Phone', 'Email', 'Fax', 'Website', 'Office Location', 'Office Hours', 'Mailstop', 'Mailing Address', 'Faculty Areas of Expertise', 'Summary of Expertise'];
  const checkedByDefault = ['Photo', 'Title', 'Department', 'Phone', 'Email', 'Website', 'Office Location', 'Office Hours'];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", {
    style: {
      marginBottom: "0px"
    }
  }, "Link Name to Individual Profile?"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      marginTop: "0px"
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    style: {
      fontSize: "0.85714rem"
    }
  }, "In a small number of cases, units do not want a person's name to link to a more detailed profile page.")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vertical_radio"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.RadioControl, {
    selected: localLinkToProfile,
    onChange: newLocalLinkToProfile => {
      setLocalLinkToProfile(newLocalLinkToProfile === 'true');
      setAttributes({
        linkToProfile: newLocalLinkToProfile === 'true'
      });
    },
    options: options
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex-labels"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "List Layout Information to Display"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__["default"], {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);




const IntroParagraph = ({
  boolIntroParagraph,
  introParagraph,
  setAttributes
}) => {
  let showIntro;
  let setShowIntro;

  if (typeof boolIntroParagraph === 'undefined') {
    setAttributes({
      boolIntroParagraph: false
    });
    [showIntro, setShowIntro] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  } else {
    [showIntro, setShowIntro] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(boolIntroParagraph);
  }

  let introParagraphState;
  let setIntroParagraphState;

  if (typeof introParagraph === 'undefined') {
    [introParagraphState, setIntroParagraphState] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
    setAttributes({
      introParagraph: ''
    });
  } else {
    [introParagraphState, setIntroParagraphState] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(introParagraph);
  }

  const options = [{
    label: 'Yes',
    value: true
  }, {
    label: 'No',
    value: false
  }];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    id: "introParagraph",
    className: "vertical_radio"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.RadioControl, {
    selected: showIntro,
    onChange: newShowIntro => {
      setShowIntro(newShowIntro === 'true');
      setAttributes({
        boolIntroParagraph: newShowIntro === 'true'
      });
    },
    options: options
  }), showIntro && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextareaControl, {
    value: introParagraphState,
    onChange: newIntroParagraph => {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);




const PageLayout = ({
  pageLayout,
  setAttributes
}) => {
  const options = [{
    label: 'List Layout',
    value: 'list'
  }, {
    label: 'Tiled Layout',
    value: 'tiled'
  }, {
    label: 'Table Layout',
    value: 'table'
  }];
  let localPageLayout;
  let setLocalPageLayout;

  if (typeof pageLayout === 'undefined') {
    [localPageLayout, setLocalPageLayout] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('list');
    setAttributes({
      pageLayout: 'list'
    });
  } else {
    [localPageLayout, setLocalPageLayout] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(pageLayout);
  }

  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vertical_radio"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.RadioControl, {
    selected: localPageLayout,
    onChange: newPageLayout => {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _AutomatedFeeds__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AutomatedFeeds */ "./src/components/CampusDirectory/AutomatedFeeds.js");
/* harmony import */ var _InformationToDisplay__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./InformationToDisplay */ "./src/components/CampusDirectory/InformationToDisplay.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);






const PeopleAndInformation = ({
  setAttributes,
  automatedFeeds,
  cruzidList,
  strFacultyTypes,
  strStaffTypes,
  strGradTypes,
  manualAdd,
  addCruzids,
  excludeCruzids,
  displayDeptartmentAffiliates,
  linkToProfile,
  strInformationTypes
}) => {
  let localAutomatedFeeds;
  let setLocalAutomatedFeeds;

  if (typeof automatedFeeds === 'undefined') {
    [localAutomatedFeeds, setLocalAutomatedFeeds] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(true);
    setAttributes({
      automatedFeeds: true
    });
  } else {
    [localAutomatedFeeds, setLocalAutomatedFeeds] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(automatedFeeds);
  }

  let localManualAdd;
  let setLocalManualAdd;

  if (typeof manualAdd === 'undefined') {
    [localManualAdd, setLocalManualAdd] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
    setAttributes({
      manualAdd: false
    });
  } else {
    [localManualAdd, setLocalManualAdd] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(manualAdd);
  }

  let cruzidListState;
  let setCruzidListState;

  if (typeof cruzidList === 'undefined') {
    [cruzidListState, setCruzidListState] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
    setAttributes({
      cruzidList: ''
    });
  } else {
    [cruzidListState, setCruzidListState] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(cruzidList);
  }

  let addCruzidsList;
  let setAddCruzidsList;

  if (typeof addCruzids === 'undefined') {
    [addCruzidsList, setAddCruzidsList] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
    setAttributes({
      addCruzids: ''
    });
  } else {
    [addCruzidsList, setAddCruzidsList] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(addCruzids);
  }

  let excludeCruzidsList;
  let setExcludeCruzidsList;

  if (typeof excludeCruzids === 'undefined') {
    [excludeCruzidsList, setExcludeCruzidsList] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
    setAttributes({
      excludeCruzids: ''
    });
  } else {
    [excludeCruzidsList, setExcludeCruzidsList] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(excludeCruzids);
  }

  let boolDisplayDepartmentAffiliates;
  let setBoolDisplayDepartmentAffiliates;

  if (typeof displayDeptartmentAffiliates === 'undefined') {
    [boolDisplayDepartmentAffiliates, setBoolDisplayDepartmentAffiliates] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
    setAttributes({
      displayDeptartmentAffiliates: false
    });
  } else {
    [boolDisplayDepartmentAffiliates, setBoolDisplayDepartmentAffiliates] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(displayDeptartmentAffiliates);
  }

  const options = [{
    label: 'Use Automated Feed(s)',
    value: true
  }, {
    label: 'Create My Own List of People to Display',
    value: false
  }];
  const manualAddOptions = [{
    label: 'Yes',
    value: true
  }, {
    label: 'No',
    value: false
  }];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vertical_radio"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RadioControl, {
    selected: localAutomatedFeeds,
    onChange: newLocalAutomatedFeeds => {
      setLocalAutomatedFeeds(newLocalAutomatedFeeds === 'true');
      setAttributes({
        automatedFeeds: newLocalAutomatedFeeds === 'true'
      });
    },
    options: options
  })), !localAutomatedFeeds && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextareaControl, {
    value: cruzidListState,
    onChange: newCruzidListState => {
      setCruzidListState(newCruzidListState);
      setAttributes({
        cruzidList: newCruzidListState
      });
    }
  })), localAutomatedFeeds && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_AutomatedFeeds__WEBPACK_IMPORTED_MODULE_1__["default"], {
    setAttributes: setAttributes,
    strFacultyTypes: strFacultyTypes,
    strStaffTypes: strStaffTypes,
    strGradTypes: strGradTypes
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Manually Add or Exclude Individuals from the feed?"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vertical_radio"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RadioControl, {
    selected: localManualAdd,
    onChange: newLocalManualAdd => {
      setLocalManualAdd(newLocalManualAdd === 'true');
      setAttributes({
        manualAdd: newLocalManualAdd === 'true'
      });
    },
    options: manualAddOptions
  })), localManualAdd && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Add Individuals to the Feed. (Enter CruzIDs separated by commas)"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextareaControl, {
    value: addCruzidsList,
    onChange: newAddCruzidsList => {
      setAddCruzidsList(newAddCruzidsList);
      setAttributes({
        addCruzids: newAddCruzidsList
      });
    }
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Exclude Individuals from the Feed. (Enter CruzIDs separated by commas)"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextareaControl, {
    value: excludeCruzidsList,
    onChange: newExcludeCruzidsList => {
      setExcludeCruzidsList(newExcludeCruzidsList);
      setAttributes({
        excludeCruzids: newExcludeCruzidsList
      });
    }
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", {
    style: {
      marginBottom: "0px"
    }
  }, "Display Department Affiliates? (Rare for administrative units.)"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      marginTop: "0px"
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    style: {
      fontSize: "0.85714rem"
    }
  }, "For example, a faculty member is officially associated with the History Department but is also affiliated with College Nine because they teach a College Nine Core Course.")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex-labels"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.CheckboxControl, {
    label: "Display Affiliates",
    checked: boolDisplayDepartmentAffiliates,
    onChange: newValue => {
      setBoolDisplayDepartmentAffiliates(newValue);
      setAttributes({
        displayDeptartmentAffiliates: newValue
      });
    }
  })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_InformationToDisplay__WEBPACK_IMPORTED_MODULE_2__["default"], {
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
/* harmony import */ var _blocks_CourseCatalog__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./blocks/CourseCatalog */ "./src/blocks/CourseCatalog.js");
/* harmony import */ var _blocks_CampusDirectory__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./blocks/CampusDirectory */ "./src/blocks/CampusDirectory.js");
/* harmony import */ var _blocks_ClassSchedule__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./blocks/ClassSchedule */ "./src/blocks/ClassSchedule.js");






(0,_blocks_TestDemoUCSC__WEBPACK_IMPORTED_MODULE_0__["default"])();
(0,_blocks_TestDemoUCSC2__WEBPACK_IMPORTED_MODULE_1__["default"])();
(0,_blocks_ContentSharer__WEBPACK_IMPORTED_MODULE_2__["default"])();
(0,_blocks_CourseCatalog__WEBPACK_IMPORTED_MODULE_3__["default"])();
(0,_blocks_CampusDirectory__WEBPACK_IMPORTED_MODULE_4__["default"])();
(0,_blocks_ClassSchedule__WEBPACK_IMPORTED_MODULE_5__["default"])();
}();
/******/ })()
;
//# sourceMappingURL=index.js.map