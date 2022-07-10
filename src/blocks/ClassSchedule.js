import { Panel, PanelBody } from '@wordpress/components';

import DepartmentDropdown from '../components/DepartmentDropdown';


const ClassSchedule = () => {
  wp.blocks.registerBlockType("ucscblocks/classschedule", {
    title: "Class Schedule",
    icon: "smiley",
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
          <Panel header="Class Schedule Block">
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

export default ClassSchedule;
