import { InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { Panel, PanelBody, CheckboxControl } from '@wordpress/components';

const Accordion = () => {
  wp.blocks.registerBlockType("ucscblocks/accordion-wrapper", {
    title: "Accordion Wrapper",
    icon: "button",
    category: "common",
    attributes: {

    },
    edit: ({ setAttributes, attributes }) => {

      return (
        <div style={{padding: "0 25px 25px 25px", border: "1px solid"}}>
          <p style={{paddingTop: "5px", marginTop: "0px", fontSize: "14px"}}>Accordion Wrapper</p>
          <InnerBlocks />
        </div>
      );
    },
    save:() => {

      return (
          <div>
              <InnerBlocks.Content />
          </div>
      );
    },
  })
}

export default Accordion;
