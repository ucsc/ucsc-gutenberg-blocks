import CheckboxGroupControl from "./CheckboxGroupControl";

import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const AutomatedFeeds = ({
  setAttributes,
  strFacultyTypes,
  strStaffTypes,
  strGradTypes,
}) => {
  // List of checkbox labels/values
  const arrFacultyTypes = [
    'All',
    'Regular Faculty',
    'Lecturer',
    'Emeriti',
    'Research Professor',
    'Researcher',
    'Adjunct Faculty',
    'Visiting Scholar',
    'Graduate Student Instructor',
    'Retired',
  ];

  const arrStaffTypes = ['Regular Staff', 'Researcher', 'Postdoctoral Scholar'];

  const arrGradTypes = ['Grad Students'];

  return (
    <div className="flex-labels">
      <p className="cd-option-label">Faculty Types</p>
      <CheckboxGroupControl
        setAttributes={setAttributes}
        currentAttributes={strFacultyTypes}
        arrOfLabels={arrFacultyTypes}
        attributeStr="strFacultyTypes"
      />
      <p className="cd-option-label">Staff Types</p>
      <CheckboxGroupControl
        setAttributes={setAttributes}
        currentAttributes={strStaffTypes}
        arrOfLabels={arrStaffTypes}
        attributeStr="strStaffTypes"
        flexCheckboxes
      />
      <p className="cd-option-label">Graduate Students</p>
      <CheckboxGroupControl
        setAttributes={setAttributes}
        currentAttributes={strGradTypes}
        arrOfLabels={arrGradTypes}
        attributeStr="strGradTypes"
      />
    </div>
  );
};

export default AutomatedFeeds;
