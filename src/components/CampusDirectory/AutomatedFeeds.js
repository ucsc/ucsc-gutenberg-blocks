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
      <h5>Faculty Types</h5>
      <CheckboxGroupControl
        setAttributes={setAttributes}
        currentAttributes={strFacultyTypes}
        arrOfLabels={arrFacultyTypes}
        attributeStr="strFacultyTypes"
      />
      <h5>Staff Types</h5>
      <CheckboxGroupControl
        setAttributes={setAttributes}
        currentAttributes={strStaffTypes}
        arrOfLabels={arrStaffTypes}
        attributeStr="strStaffTypes"
        flexCheckboxes
      />
      <h5>Graduate Students</h5>
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
