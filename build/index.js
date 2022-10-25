/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/blocks/Accordion.js":
/*!*********************************!*\
  !*** ./src/blocks/Accordion.js ***!
  \*********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);



const Accordion = () => {
  wp.blocks.registerBlockType("ucscblocks/accordion", {
    title: "Accordion",
    icon: "menu",
    category: "common",
    attributes: {
      openOnPageLoad: {
        type: 'boolean'
      },
      title: {
        type: 'string'
      }
    },
    edit: _ref => {
      let {
        setAttributes,
        attributes
      } = _ref;
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
        key: "setting"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Panel, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
        label: "Open on page load",
        checked: attributes.openOnPageLoad,
        onChange: newValue => setAttributes({
          openOnPageLoad: newValue
        })
      })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("details", {
        className: "ucsc-accordion",
        open: "true"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("summary", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        placeholder: "Accordion Item Title",
        value: attributes.title,
        onKeyUp: event => {
          event.preventDefault();
        },
        onChange: e => setAttributes({
          title: e.target.value
        }),
        style: {
          "width": "100%"
        }
      })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks, null)));
    },
    save: () => {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks.Content, null));
    }
  });
};
/* harmony default export */ __webpack_exports__["default"] = (Accordion);

/***/ }),

/***/ "./src/blocks/AccordionWrapper.js":
/*!****************************************!*\
  !*** ./src/blocks/AccordionWrapper.js ***!
  \****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);



const Accordion = () => {
  wp.blocks.registerBlockType("ucscblocks/accordion-wrapper", {
    title: "Accordion Wrapper",
    icon: "button",
    category: "common",
    attributes: {},
    edit: _ref => {
      let {
        setAttributes,
        attributes
      } = _ref;
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        style: {
          padding: "0 25px 25px 25px",
          border: "1px solid"
        }
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
        style: {
          paddingTop: "5px",
          marginTop: "0px",
          fontSize: "14px"
        }
      }, "Accordion Wrapper"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks, null));
    },
    save: () => {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks.Content, null));
    }
  });
};
/* harmony default export */ __webpack_exports__["default"] = (Accordion);

/***/ }),

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
/* harmony import */ var _components_CampusDirectory_CampusDirectoryDepartmentDropdown__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components//CampusDirectory/CampusDirectoryDepartmentDropdown */ "./src/components/CampusDirectory/CampusDirectoryDepartmentDropdown.js");
/* harmony import */ var _components_DivisionDropdown__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components/DivisionDropdown */ "./src/components/DivisionDropdown.js");
/* harmony import */ var _components_CampusDirectory_PageLayout__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../components/CampusDirectory/PageLayout */ "./src/components/CampusDirectory/PageLayout.js");
/* harmony import */ var _components_CampusDirectory_PeopleAndInformation__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../components/CampusDirectory/PeopleAndInformation */ "./src/components/CampusDirectory/PeopleAndInformation.js");







const CampusDirectory = () => {
  wp.blocks.registerBlockType("ucscblocks/campusdirectory", {
    title: "Campus Directory",
    icon: "welcome-learn-more",
    category: "common",
    attributes: {
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
      linkOutToCampusDirectory: {
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
        type: "string"
      }
    },
    edit: _ref => {
      let {
        setAttributes,
        attributes
      } = _ref;
      const {
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
        linkOutToCampusDirectory,
        strInformationTypes,
        strInformationTypesTable,
        department,
        division,
        deptOrDiv
      } = attributes;
      const [configuredCorrectly, setConfiguredCorrectly] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(true);
      const [resp, setResp] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)({});
      (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
        fetch('/wp-json/ucscgutenbergblocks/v1/campusdirectoryrequirements').then(res => res.text()).then(text => {
          const resp = JSON.parse(text);
          if (!resp.ldap_pass) setConfiguredCorrectly(false);
          setResp(resp);
        });
      }, []);
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, configuredCorrectly && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Panel, {
        className: "directory-block",
        header: "Directory Block"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelBody, {
        title: "Layout Type",
        initialOpen: true
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelRow, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_CampusDirectory_PageLayout__WEBPACK_IMPORTED_MODULE_4__["default"], {
        setAttributes: setAttributes,
        pageLayout: pageLayout
      }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelBody, {
        title: "People and Information to Display",
        initialOpen: true
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelRow, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_CampusDirectory_PeopleAndInformation__WEBPACK_IMPORTED_MODULE_5__["default"], {
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
        linkOutToCampusDirectory: linkOutToCampusDirectory,
        strInformationTypes: strInformationTypes,
        strInformationTypesTable: strInformationTypesTable,
        pageLayout: pageLayout,
        division: division,
        department: department,
        deptOrDiv: deptOrDiv
      })))), !configuredCorrectly && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, "This Block is not Configured Correctly"), !resp.ldap_pass && resp.multisite && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h4", null, "The LDAP password can be set at the network level ", (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
        target: "_blank",
        href: "/wp-admin/network/settings.php?page=ucsc-gutenberg-blocks-network-settings"
      }, "here.")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h6", null, "Or the LDAP password can be set at the site level below.")), !resp.ldap_pass && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h4", null, "The LDAP password needs to be set here ", (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
        target: "_blank",
        href: "/wp-admin/options-general.php?page=ucsc_gutenberg_blocks_settings_page"
      }, "here."))));
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
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_DepartmentDropdown__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components/DepartmentDropdown */ "./src/components/DepartmentDropdown.js");
/* harmony import */ var _components_SubjectDropdown__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components/SubjectDropdown */ "./src/components/SubjectDropdown.js");





const ClassSchedule = () => {
  wp.blocks.registerBlockType("ucscblocks/classschedule", {
    title: "Class Schedule",
    icon: "schedule",
    category: "common",
    attributes: {
      subjectOrDept: {
        type: "string"
      },
      department: {
        type: "string"
      },
      subject: {
        type: "string"
      }
    },
    edit: _ref => {
      let {
        setAttributes,
        attributes
      } = _ref;
      const {
        department,
        subject,
        subjectOrDept
      } = attributes;
      let localSubjectOrDept;
      let setLocalSubjectOrDept;
      if (typeof subjectOrDept === 'undefined') {
        [localSubjectOrDept, setLocalSubjectOrDept] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)("dept");
        setAttributes({
          subjectOrDept: "dept"
        });
      } else {
        [localSubjectOrDept, setLocalSubjectOrDept] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(subjectOrDept);
      }
      const options = [{
        label: 'Department',
        value: 'dept'
      }, {
        label: 'Subject',
        value: 'subject'
      }];
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Panel, {
        header: "Class Schedule Block"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelBody, {
        title: "Set Department or Subject",
        initialOpen: true
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "vertical_radio"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.RadioControl, {
        selected: localSubjectOrDept,
        onChange: newLocalSubjectOrDept => {
          setLocalSubjectOrDept(newLocalSubjectOrDept);
          setAttributes({
            subjectOrDept: newLocalSubjectOrDept
          });
        },
        options: options
      })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("hr", null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_DepartmentDropdown__WEBPACK_IMPORTED_MODULE_2__["default"], {
        label: "Departments",
        department: department,
        setAttributes: setAttributes,
        disabled: subjectOrDept !== "dept"
      }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_SubjectDropdown__WEBPACK_IMPORTED_MODULE_3__["default"], {
        label: "Subjects",
        subject: subject,
        setAttributes: setAttributes,
        disabled: subjectOrDept !== "subject"
      }))));
    },
    save: props => {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_DepartmentDropdown__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components/DepartmentDropdown */ "./src/components/DepartmentDropdown.js");
/* harmony import */ var _components_SubjectDropdown__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components/SubjectDropdown */ "./src/components/SubjectDropdown.js");





const CourseCatalog = () => {
  wp.blocks.registerBlockType("ucscblocks/coursecatalog", {
    title: "Course Catalog",
    icon: "book-alt",
    category: "common",
    attributes: {
      subjectOrDept: {
        type: "string"
      },
      department: {
        type: "string"
      },
      subject: {
        type: "string"
      }
    },
    edit: _ref => {
      let {
        setAttributes,
        attributes
      } = _ref;
      const {
        department,
        subject,
        subjectOrDept
      } = attributes;
      let localSubjectOrDept;
      let setLocalSubjectOrDept;
      if (typeof subjectOrDept === 'undefined') {
        [localSubjectOrDept, setLocalSubjectOrDept] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)("dept");
        setAttributes({
          subjectOrDept: "dept"
        });
      } else {
        [localSubjectOrDept, setLocalSubjectOrDept] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(subjectOrDept);
      }
      const options = [{
        label: 'Department',
        value: 'dept'
      }, {
        label: 'Subject',
        value: 'subject'
      }];
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Panel, {
        header: "Course Catalog Block"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelBody, {
        title: "Set Department or Subject",
        initialOpen: true
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "vertical_radio"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.RadioControl, {
        selected: localSubjectOrDept,
        onChange: newLocalSubjectOrDept => {
          setLocalSubjectOrDept(newLocalSubjectOrDept);
          setAttributes({
            subjectOrDept: newLocalSubjectOrDept
          });
        },
        options: options
      })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("hr", null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_DepartmentDropdown__WEBPACK_IMPORTED_MODULE_2__["default"], {
        label: "Departments",
        department: department,
        setAttributes: setAttributes,
        disabled: subjectOrDept !== "dept"
      }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_SubjectDropdown__WEBPACK_IMPORTED_MODULE_3__["default"], {
        label: "Subjects",
        subject: subject,
        setAttributes: setAttributes,
        disabled: subjectOrDept !== "subject"
      }))));
    },
    save: props => {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CheckboxGroupControl */ "./src/components/CampusDirectory/CheckboxGroupControl.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);




const AutomatedFeeds = _ref => {
  let {
    setAttributes,
    strFacultyTypes,
    strStaffTypes,
    strGradTypes
  } = _ref;
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

/***/ "./src/components/CampusDirectory/CampusDirectoryDepartmentDropdown.js":
/*!*****************************************************************************!*\
  !*** ./src/components/CampusDirectory/CampusDirectoryDepartmentDropdown.js ***!
  \*****************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);



const DepartmentDropdown = _ref => {
  let {
    department,
    setAttributes,
    label,
    disabled = false
  } = _ref;
  const [departments, setDepartments] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  let localDepartment;
  let setLocalDepartment;
  if (typeof department === 'undefined') {
    [localDepartment, setLocalDepartment] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('---');
    setAttributes({
      department: '---'
    });
  } else {
    [localDepartment, setLocalDepartment] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(department);
  }
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    fetch('/wp-json/ucscgutenbergblocks/v1/cddepartmentcode').then(res => res.text()).then(text => {
      const resp = JSON.parse(text);
      setDepartments(resp);
    });
  }, []);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      width: "max-content"
    }
  }, departments.length > 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
    label: label,
    value: department,
    options: departments,
    onChange: newDepartment => {
      setAttributes({
        department: newDepartment
      });
      setLocalDepartment(newDepartment);
    },
    disabled: disabled
  }), !departments.length && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, "Department Dropdown Loading...")));
};
/* harmony default export */ __webpack_exports__["default"] = (DepartmentDropdown);

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



const CheckboxGroupControl = _ref => {
  let {
    setAttributes,
    currentAttributes,
    arrOfLabels,
    flexCheckboxes,
    attributeStr,
    checkedByDefault
  } = _ref;
  // Initialize array that will render checkboxes
  const arrRender = [];
  arrOfLabels.map(label => arrRender.push({
    label
  }));

  // If the attribute hasn't been defined yet (creating a new record)
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
  }

  // add state vars/fxn to each object to be rendered
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




const InformationToDisplay = _ref => {
  let {
    setAttributes,
    linkToProfile,
    linkOutToCampusDirectory,
    strInformationTypes,
    pageLayout
  } = _ref;
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
  let boolLinkOutToCampusDirectory;
  let setBoolLinkOutToCampusDirectory;
  if (typeof linkOutToCampusDirectory === 'undefined') {
    [boolLinkOutToCampusDirectory, setBoolLinkOutToCampusDirectory] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
    setAttributes({
      linkOutToCampusDirectory: false
    });
  } else {
    [boolLinkOutToCampusDirectory, setBoolLinkOutToCampusDirectory] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(linkOutToCampusDirectory);
  }
  const options = [{
    label: 'Yes',
    value: true
  }, {
    label: 'No',
    value: false
  }];

  // List of checkbox labels/values
  const arrInformationToDisplay = ['Pronouns', 'Photo', 'Title', 'Department', 'Phone', 'Campus Email', 'Other Email', 'Fax', 'Website', 'Office Location', 'Office Hours', 'Mailstop', 'Mailing Address', 'Faculty Areas of Expertise', 'Summary of Expertise'];
  const checkedByDefault = ['Photo', 'Title', 'Department', 'Phone', 'Campus Email', 'Website', 'Office Location', 'Office Hours'];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("hr", null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Link Name to Individual Profile?"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", null, "In a small number of cases, units do not want a person's name to link to a more detailed profile page.")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
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
    style: {
      paddingTop: "10px"
    },
    className: "flex-labels"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
    label: "Link to campusdirectory.ucsc.edu",
    checked: boolLinkOutToCampusDirectory,
    onChange: newValue => {
      setBoolLinkOutToCampusDirectory(newValue);
      setAttributes({
        linkOutToCampusDirectory: newValue
      });
    },
    disabled: !localLinkToProfile
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("hr", null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex-labels"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, pageLayout === 'list' ? "List" : "Tiled", " Layout Information to Display"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__["default"], {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CheckboxGroupControl */ "./src/components/CampusDirectory/CheckboxGroupControl.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);




const InformationToDisplay = _ref => {
  let {
    setAttributes,
    linkToProfile,
    linkOutToCampusDirectory,
    strInformationTypesTable
  } = _ref;
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
  let boolLinkOutToCampusDirectory;
  let setBoolLinkOutToCampusDirectory;
  if (typeof linkOutToCampusDirectory === 'undefined') {
    [boolLinkOutToCampusDirectory, setBoolLinkOutToCampusDirectory] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
    setAttributes({
      linkOutToCampusDirectory: false
    });
  } else {
    [boolLinkOutToCampusDirectory, setBoolLinkOutToCampusDirectory] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(linkOutToCampusDirectory);
  }
  const options = [{
    label: 'Yes',
    value: true
  }, {
    label: 'No',
    value: false
  }];

  // List of checkbox labels/values
  const arrInformationToDisplay = ['Pronouns', 'Title', 'Department', 'Phone', 'Campus Email', 'Other Email', 'Fax', 'Website', 'Office Location', 'Office Hours', 'Mailstop', 'Mailing Address', 'Faculty Areas of Expertise', 'Summary of Expertise'];
  const checkedByDefault = ['Title', 'Department', 'Phone', 'Campus Email'];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("hr", null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Link Name to Individual Profile?"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", null, "In a small number of cases, units do not want a person's name to link to a more detailed profile page.")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
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
    style: {
      paddingTop: "10px"
    },
    className: "flex-labels"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
    label: "Link to campusdirectory.ucsc.edu",
    checked: boolLinkOutToCampusDirectory,
    onChange: newValue => {
      setBoolLinkOutToCampusDirectory(newValue);
      setAttributes({
        linkOutToCampusDirectory: newValue
      });
    },
    disabled: !localLinkToProfile
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("hr", null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex-labels"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Table Layout Information to Display"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_CheckboxGroupControl__WEBPACK_IMPORTED_MODULE_1__["default"], {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);



const PageLayout = _ref => {
  let {
    pageLayout,
    setAttributes
  } = _ref;
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
/* harmony import */ var _InformationToDisplayTable__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./InformationToDisplayTable */ "./src/components/CampusDirectory/InformationToDisplayTable.js");
/* harmony import */ var _CampusDirectoryDepartmentDropdown__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./CampusDirectoryDepartmentDropdown */ "./src/components/CampusDirectory/CampusDirectoryDepartmentDropdown.js");
/* harmony import */ var _DivisionDropdown__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../DivisionDropdown */ "./src/components/DivisionDropdown.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__);








const PeopleAndInformation = _ref => {
  let {
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
    linkOutToCampusDirectory,
    strInformationTypes,
    strInformationTypesTable,
    pageLayout,
    division,
    department,
    deptOrDiv
  } = _ref;
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
  let localDeptOrDiv;
  let setLocalDeptOrDiv;
  if (typeof deptOrDiv === 'undefined') {
    [localDeptOrDiv, setLocalDeptOrDiv] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('dept');
    setAttributes({
      deptOrDiv: 'dept'
    });
  } else {
    [localDeptOrDiv, setLocalDeptOrDiv] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(deptOrDiv);
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
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.RadioControl, {
    selected: localAutomatedFeeds,
    onChange: newLocalAutomatedFeeds => {
      setLocalAutomatedFeeds(newLocalAutomatedFeeds === 'true');
      setAttributes({
        automatedFeeds: newLocalAutomatedFeeds === 'true'
      });
    },
    options: options
  })), !localAutomatedFeeds && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "List Individuals (Enter CruzIDs separated by commas)"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", null, "List individuals in the order you'd like them to appear on the page.")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.TextareaControl, {
    value: cruzidListState,
    onChange: newCruzidListState => {
      setCruzidListState(newCruzidListState);
      setAttributes({
        cruzidList: newCruzidListState
      });
    }
  })), localAutomatedFeeds && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Set Department or Division"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vertical_radio"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.RadioControl, {
    selected: deptOrDiv,
    options: [{
      label: 'Department',
      value: 'dept'
    }, {
      label: 'Division',
      value: 'div'
    }],
    onChange: value => {
      setAttributes({
        deptOrDiv: value
      });
      setLocalDeptOrDiv(value);
    }
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("hr", null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_CampusDirectoryDepartmentDropdown__WEBPACK_IMPORTED_MODULE_4__["default"], {
    label: "Department",
    department: department,
    setAttributes: setAttributes,
    disabled: !(deptOrDiv === 'dept')
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_DivisionDropdown__WEBPACK_IMPORTED_MODULE_5__["default"], {
    label: "Division",
    division: division,
    setAttributes: setAttributes,
    disabled: !(deptOrDiv === 'div')
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_AutomatedFeeds__WEBPACK_IMPORTED_MODULE_1__["default"], {
    setAttributes: setAttributes,
    strFacultyTypes: strFacultyTypes,
    strStaffTypes: strStaffTypes,
    strGradTypes: strGradTypes
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Manually Add or Exclude Individuals from the feed?"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vertical_radio"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.RadioControl, {
    selected: localManualAdd,
    onChange: newLocalManualAdd => {
      setLocalManualAdd(newLocalManualAdd === 'true');
      setAttributes({
        manualAdd: newLocalManualAdd === 'true'
      });
    },
    options: manualAddOptions
  })), localManualAdd && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Add Individuals to the Feed. (Enter CruzIDs separated by commas)"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.TextareaControl, {
    value: addCruzidsList,
    onChange: newAddCruzidsList => {
      setAddCruzidsList(newAddCruzidsList);
      setAttributes({
        addCruzids: newAddCruzidsList
      });
    }
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Exclude Individuals from the Feed. (Enter CruzIDs separated by commas)"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.TextareaControl, {
    value: excludeCruzidsList,
    onChange: newExcludeCruzidsList => {
      setExcludeCruzidsList(newExcludeCruzidsList);
      setAttributes({
        excludeCruzids: newExcludeCruzidsList
      });
    }
  })), deptOrDiv === "dept" && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("hr", null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h5", null, "Display Department Affiliates? (Rare for administrative units.)"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", null, "For example, a faculty member is officially associated with the History Department but is also affiliated with College Nine because they teach a College Nine Core Course.")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex-labels"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.CheckboxControl, {
    label: "Display Affiliates",
    checked: boolDisplayDepartmentAffiliates,
    onChange: newValue => {
      setBoolDisplayDepartmentAffiliates(newValue);
      setAttributes({
        displayDeptartmentAffiliates: newValue
      });
    }
  })))), pageLayout !== "table" ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_InformationToDisplay__WEBPACK_IMPORTED_MODULE_2__["default"], {
    setAttributes: setAttributes,
    linkToProfile: linkToProfile,
    linkOutToCampusDirectory: linkOutToCampusDirectory,
    strInformationTypes: strInformationTypes,
    pageLayout: pageLayout
  }) : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_InformationToDisplayTable__WEBPACK_IMPORTED_MODULE_3__["default"], {
    setAttributes: setAttributes,
    linkToProfile: linkToProfile,
    linkOutToCampusDirectory: linkOutToCampusDirectory,
    strInformationTypesTable: strInformationTypesTable
  }));
};
/* harmony default export */ __webpack_exports__["default"] = (PeopleAndInformation);

/***/ }),

/***/ "./src/components/DepartmentDropdown.js":
/*!**********************************************!*\
  !*** ./src/components/DepartmentDropdown.js ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);



const DepartmentDropdown = _ref => {
  let {
    department,
    setAttributes,
    label,
    disabled = false
  } = _ref;
  const [departments, setDepartments] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  let localDepartment;
  let setLocalDepartment;
  if (typeof department === 'undefined') {
    [localDepartment, setLocalDepartment] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('---');
    setAttributes({
      department: '---'
    });
  } else {
    [localDepartment, setLocalDepartment] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(department);
  }
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    fetch('/wp-json/ucscgutenbergblocks/v1/departmentcode').then(res => res.text()).then(text => {
      const resp = JSON.parse(text);
      setDepartments(resp);
    });
  }, []);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      width: "max-content"
    }
  }, departments.length > 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
    label: label,
    value: department,
    options: departments,
    onChange: newDepartment => {
      setAttributes({
        department: newDepartment
      });
      setLocalDepartment(newDepartment);
    },
    disabled: disabled
  }), !departments.length && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, "Subject Dropdown Loading...")));
};
/* harmony default export */ __webpack_exports__["default"] = (DepartmentDropdown);

/***/ }),

/***/ "./src/components/DivisionDropdown.js":
/*!********************************************!*\
  !*** ./src/components/DivisionDropdown.js ***!
  \********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);



const DivisionDropdown = _ref => {
  let {
    division,
    setAttributes,
    label,
    disabled = false
  } = _ref;
  const [divisions, setDivisions] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  let localDivision;
  let setLocalDivision;
  if (typeof division === 'undefined') {
    [localDivision, setLocalDivision] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('---');
    setAttributes({
      division: '---'
    });
  } else {
    [localDivision, setLocalDivision] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(division);
  }
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    fetch('/wp-json/ucscgutenbergblocks/v1/divisioncode').then(res => res.text()).then(text => {
      const resp = JSON.parse(text);
      setDivisions(resp);
    });
  }, []);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      width: "max-content"
    }
  }, divisions.length > 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
    label: label,
    value: division,
    options: divisions,
    onChange: newDivision => {
      setAttributes({
        division: newDivision
      });
      setLocalDivision(newDivision);
    },
    disabled: disabled
  }), !divisions.length && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, "Divisions Dropdown Loading...")));
};
/* harmony default export */ __webpack_exports__["default"] = (DivisionDropdown);

/***/ }),

/***/ "./src/components/SubjectDropdown.js":
/*!*******************************************!*\
  !*** ./src/components/SubjectDropdown.js ***!
  \*******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);



const SubjectDropdown = _ref => {
  let {
    subject,
    setAttributes,
    label,
    disabled = false
  } = _ref;
  const [subjects, setSubjects] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  let localSubject;
  let setLocalSubject;
  if (typeof subject === 'undefined') {
    [localSubject, setLocalSubject] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('---');
    setAttributes({
      subject: '---'
    });
  } else {
    [localSubject, setLocalSubject] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(subject);
  }
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    fetch('/wp-json/ucscgutenbergblocks/v1/subjectcode').then(res => res.text()).then(text => {
      const resp = JSON.parse(text);
      setSubjects(resp);
    });
  }, []);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      width: "max-content"
    }
  }, subjects.length > 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
    label: label,
    value: subject,
    options: subjects,
    onChange: newSubject => {
      setAttributes({
        subject: newSubject
      });
      setLocalSubject(newSubject);
    },
    disabled: disabled
  }), !subjects.length && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, "Subject Dropdown Loading...")));
};
/* harmony default export */ __webpack_exports__["default"] = (SubjectDropdown);

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ (function(module) {

module.exports = window["wp"]["blockEditor"];

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
/* harmony import */ var _blocks_CourseCatalog__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./blocks/CourseCatalog */ "./src/blocks/CourseCatalog.js");
/* harmony import */ var _blocks_CampusDirectory__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./blocks/CampusDirectory */ "./src/blocks/CampusDirectory.js");
/* harmony import */ var _blocks_ClassSchedule__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./blocks/ClassSchedule */ "./src/blocks/ClassSchedule.js");
/* harmony import */ var _blocks_Accordion__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./blocks/Accordion */ "./src/blocks/Accordion.js");
/* harmony import */ var _blocks_AccordionWrapper__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./blocks/AccordionWrapper */ "./src/blocks/AccordionWrapper.js");
// import TestDemoUCSC from './blocks/TestDemoUCSC';
// import TestDemoUCSC2 from './blocks/TestDemoUCSC2';
// import ContentSharer from './blocks/ContentSharer';







// import FeedbackForm from './blocks/FeedbackForm';

// TestDemoUCSC();
// TestDemoUCSC2();
// ContentSharer();

(0,_blocks_CourseCatalog__WEBPACK_IMPORTED_MODULE_0__["default"])();
(0,_blocks_CampusDirectory__WEBPACK_IMPORTED_MODULE_1__["default"])();
(0,_blocks_ClassSchedule__WEBPACK_IMPORTED_MODULE_2__["default"])();
(0,_blocks_Accordion__WEBPACK_IMPORTED_MODULE_3__["default"])();
(0,_blocks_AccordionWrapper__WEBPACK_IMPORTED_MODULE_4__["default"])();

// FeedbackForm();
}();
/******/ })()
;
//# sourceMappingURL=index.js.map