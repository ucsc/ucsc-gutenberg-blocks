import { RadioControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const PageLayout = ({ pageLayout, setAttributes }) => {
  const options = [
    { label: 'List Layout', value: 'list' },
    { label: 'Tiled Layout', value: 'tiled' },
    { label: 'Table Layout', value: 'table' },
  ];
  let localPageLayout;
  let setLocalPageLayout;
  if (typeof pageLayout === 'undefined') {
    [localPageLayout, setLocalPageLayout] = useState('list');
    setAttributes({
      pageLayout: 'list'
    });
  } else {
    [localPageLayout, setLocalPageLayout] = useState(pageLayout);
  }

  return (
    <div className="vertical_radio">
      <RadioControl
        selected={localPageLayout}
        onChange={newPageLayout => {
          setLocalPageLayout(newPageLayout);
          setAttributes({
            pageLayout: newPageLayout,
          });
        }}
        options={options}
      />
    </div>
  );
}

export default PageLayout;
