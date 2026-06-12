import { Panel, PanelBody, RadioControl, CheckboxControl } from '@wordpress/components';

import DepartmentDropdown from '../components/DepartmentDropdown';
import SubjectDropdown from '../components/SubjectDropdown';


import { useState } from '@wordpress/element';



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
      },
      defaultColumns: {
        type: "array"
      },
    },
    edit: ({ setAttributes, attributes }) => {
      const {
        department,
        subject,
        subjectOrDept,
      } = attributes;

      // Toggleable columns the visitor sees in the front-end Filter modal. Status,
      // Course ID, and Title are always shown and are intentionally not listed here.
      const columnOptions = [
        { key: 'seats', label: 'Seats' },
        { key: 'days', label: 'Days' },
        { key: 'time', label: 'Time' },
        { key: 'location', label: 'Location' },
        { key: 'instructor', label: 'Instructor' },
        { key: 'class-num', label: 'Class #' },
        { key: 'enrollment', label: 'Enrollment' },
      ];

      // Undefined means "never configured" — fall back to the historical Seats + Days defaults.
      const defaultColumns = attributes.defaultColumns || ['seats', 'days'];

      const toggleColumn = (key, isChecked) => {
        const current = attributes.defaultColumns || ['seats', 'days'];
        const next = isChecked
          ? (current.includes(key) ? current : [...current, key])
          : current.filter(c => c !== key);
        setAttributes({ defaultColumns: next });
      };

      let localSubjectOrDept;
      let setLocalSubjectOrDept;
      if (typeof subjectOrDept === 'undefined') {
        [localSubjectOrDept, setLocalSubjectOrDept] = useState("dept");
        setAttributes({
          subjectOrDept: "dept",
        });
      } else {
        [localSubjectOrDept, setLocalSubjectOrDept] = useState(subjectOrDept);
      }

      const isDevEnvironment = () => {
        const isDevEnv = window.location.href.includes('https://wordpress-dev.ucsc.edu/')
                    || window.location.href.includes('wp-dev.ucsc');
        return isDevEnv;
      }

      const options = [
        { label: 'Department', value: 'dept' },
        { label: 'Subject', value: 'subject' },
      ];

      return (
        <>
          <Panel header="Class Schedule Block">
            <PanelBody title="Set Department or Subject" initialOpen>
              <div className="vertical_radio">
                <RadioControl
                  selected={localSubjectOrDept}
                  onChange={newLocalSubjectOrDept => {
                    setLocalSubjectOrDept(newLocalSubjectOrDept);
                    setAttributes({
                      subjectOrDept: newLocalSubjectOrDept,
                    });
                  }}
                  options={options}
                />
              </div>
              <hr />
              <DepartmentDropdown
                label="Departments"
                department={department}
                setAttributes={setAttributes}
                disabled={subjectOrDept !== "dept"}
              />
              <SubjectDropdown
                label="Subjects"
                subject={subject}
                setAttributes={setAttributes}
                disabled={subjectOrDept !== "subject"}
              />
              <small style={{ display: 'block', marginTop: '3em', fontSize: '0.7em', color: '#666' }}>version 1.1.38</small>
            </PanelBody>
            <PanelBody title="Default Visible Columns" initialOpen={false}>
              <p style={{ fontSize: '0.85em', color: '#555' }}>
                Choose which columns show by default. Status, Course ID, and Title are always shown.
                Visitors can still change columns using the Filter button on the page.
              </p>
              {columnOptions.map(opt => (
                <CheckboxControl
                  key={opt.key}
                  label={opt.label}
                  checked={defaultColumns.includes(opt.key)}
                  onChange={(isChecked) => toggleColumn(opt.key, isChecked)}
                />
              ))}
            </PanelBody>
          </Panel>
        </>
      );
    },
    save: (props) => {
      return null;
    },
    deprecated: [
      // The useNewServer attribute was removed from the block in the class_schedule_2.0 branch. Without a 
      // deprecated entry, existing posts that saved this attribute will trigger a block validation error in 
      // the editor. A deprecation migration silently strips it.
      {
        attributes: {
          subjectOrDept: { type: "string" },
          department:    { type: "string" },
          subject:       { type: "string" },
          useNewServer:  { type: "boolean" },
        },
        migrate( attributes ) {
          const { useNewServer, ...rest } = attributes;
          return rest;
        },
        save: () => null,
      },
    ],
  })
}

export default ClassSchedule;
