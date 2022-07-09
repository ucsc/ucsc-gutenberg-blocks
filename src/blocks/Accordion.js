import { InnerBlocks, useBlockProps, BlockControls, InspectorControls } from '@wordpress/block-editor';
import { Panel, PanelBody, CheckboxControl } from '@wordpress/components';

const Accordion = () => {
  wp.blocks.registerBlockType("ucscblocks/accordion", {
    title: "Accordion",
    icon: "menu",
    category: "common",
    attributes: {
      title: {
        type: 'string',
      },
      openOnPageLoad: {
        type: 'boolean',
      }
    },
    edit: ({ setAttributes, attributes }) => {
      const blockProps = useBlockProps();

      return (
        <>
          {
            <BlockControls>
              <input type="checkbox" />
            </BlockControls>
          }
          <InspectorControls key="setting">
            <Panel>
              <PanelBody>
                <CheckboxControl
                  label="Open on page load"
                  checked={attributes.openOnPageLoad}
                  onChange={newValue =>  setAttributes({openOnPageLoad: newValue})}
                ></CheckboxControl>
              </PanelBody>
            </Panel>
          </InspectorControls>
          <details className='ucsc-accordion' open="true" >
              <summary><input
                placeholder='Accordion Item Title'
                value={attributes.title}
                onKeyUp={event => {
                  event.preventDefault();
                }}
                onChange={e => setAttributes({title: e.target.value})} /></summary>
              <InnerBlocks />
          </details>
        </>
      );
    },
    save:() => {
      const blockProps = useBlockProps.save();

      return (
          <div { ...blockProps }>
              <InnerBlocks.Content />
          </div>
      );
    },
  })
}

export default Accordion;
