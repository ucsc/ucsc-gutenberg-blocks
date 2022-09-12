import { InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { Panel, PanelBody, CheckboxControl } from '@wordpress/components';

const Accordion = () => {
  wp.blocks.registerBlockType("ucscblocks/accordion", {
    title: "Accordion",
    icon: "menu",
    category: "common",
    attributes: {
      openOnPageLoad: {
        type: 'boolean',
      },
      title: {
        type: 'string'
      }
    },
    edit: ({ setAttributes, attributes }) => {

      return (
        <>
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
                onChange={e => setAttributes({title: e.target.value})}
                style={{"width": "100%"}} /></summary>
              <InnerBlocks />
          </details>
        </>
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
