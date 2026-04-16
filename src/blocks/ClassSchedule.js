import { Panel, PanelBody, RadioControl } from '@wordpress/components';

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
    },
    edit: ({ setAttributes, attributes }) => {
      const {
        department,
        subject,
        subjectOrDept,
      } = attributes;

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
              <small style={{ display: 'block', marginTop: '3em', fontSize: '0.7em', color: '#666' }}>version 1.1.34</small>
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
