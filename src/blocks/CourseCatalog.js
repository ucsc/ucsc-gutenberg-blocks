import { Panel, PanelBody, RadioControl } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';

import DepartmentDropdown from '../components/DepartmentDropdown';
import SubjectDropdown from '../components/SubjectDropdown';

import { useState } from '@wordpress/element';

const CourseCatalog = () => {
  wp.blocks.registerBlockType("ucscblocks/coursecatalog", {
    apiVersion: 3,
    title: "Course Catalog",
    icon: "book-alt",
    category: "common",
    attributes: {
      subjectOrDept: {
        type: "string",
      },
      department: {
        type: "string",
      },
      subject: {
        type: "string",
      },
    },
    edit: ({ setAttributes, attributes }) => {
      const { department, subject, subjectOrDept } = attributes;
      const blockProps = useBlockProps();

      let localSubjectOrDept;
      let setLocalSubjectOrDept;
      if (typeof subjectOrDept === "undefined") {
        [localSubjectOrDept, setLocalSubjectOrDept] = useState("dept");
        setAttributes({
          subjectOrDept: "dept",
        });
      } else {
        [localSubjectOrDept, setLocalSubjectOrDept] = useState(subjectOrDept);
      }

      const options = [
        { label: "Department", value: "dept" },
        { label: "Subject", value: "subject" },
      ];

      return (
        <div {...blockProps}>
          <Panel header="Course Catalog Block">
            <PanelBody title="Set Department or Subject" initialOpen>
              <div className="vertical_radio">
                <RadioControl
                  selected={localSubjectOrDept}
                  onChange={(newLocalSubjectOrDept) => {
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
            </PanelBody>
          </Panel>
        </div>
      );
    },
    save: (props) => {
      return null;
    },
  });
}

export default CourseCatalog;
