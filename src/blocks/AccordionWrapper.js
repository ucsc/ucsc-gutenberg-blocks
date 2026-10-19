/* istanbul ignore file -- Accordion block is slated for retirement; intentionally untested by decision (WPM-115). */
import { InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Panel, PanelBody, CheckboxControl } from '@wordpress/components';

const Accordion = () => {
  wp.blocks.registerBlockType("ucscblocks/accordion-wrapper", {
    apiVersion: 3,
    title: "Accordion Wrapper (deprecated)",
    icon: "button",
    category: "common",
    attributes: {

    },
    edit: ({ setAttributes, attributes }) => {
      const blockProps = useBlockProps({ style: {padding: "0 25px 25px 25px", border: "1px solid"} });

      return (
        <div {...blockProps}>
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
