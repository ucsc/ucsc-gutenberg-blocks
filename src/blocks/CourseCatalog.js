import { Panel, PanelBody } from '@wordpress/components';


import DepartmentDropdown from '../components/DepartmentDropdown';

const CourseCatalog = () => {
  wp.blocks.registerBlockType("ucscblocks/coursecatalog", {
    title: "Course Catalog",
    icon: "book-alt",
    category: "common",
    attributes: {
      department: {
        type: "string"
      },
    },
    edit: ({ setAttributes, attributes }) => {
      const {
        department
      } = attributes;

      return (
        <>
          <Panel header="Course Catalog Block">
            <PanelBody title="Set Subject" initialOpen>
              <DepartmentDropdown
                label="Subject"
                department={department}
                setAttributes={setAttributes}
              />
            </PanelBody>
          </Panel>
        </>
      );
    },
    save: (props) => {
      return null;
    }
  })
}

export default CourseCatalog;
