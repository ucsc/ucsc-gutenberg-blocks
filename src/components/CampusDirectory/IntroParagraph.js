import { RadioControl, TextareaControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const IntroParagraph = ({
  boolIntroParagraph,
  introParagraph,
  setAttributes,
}) => {

  let showIntro;
  let setShowIntro;
  if (typeof boolIntroParagraph === 'undefined') {
    setAttributes({
      boolIntroParagraph: false,
    });
    [showIntro, setShowIntro] = useState(false);
  } else {
    [showIntro, setShowIntro] = useState(boolIntroParagraph);
  }

  let introParagraphState;
  let setIntroParagraphState;
  if (typeof introParagraph === 'undefined') {
    [introParagraphState, setIntroParagraphState] = useState('');
    setAttributes({
      introParagraph: '',
    });
  } else {
    [introParagraphState, setIntroParagraphState] = useState(introParagraph);
  }

  const options = [
    { label: 'Yes', value: true },
    { label: 'No', value: false },
  ];

  return (
    <div id="introParagraph" className="vertical_radio">
      <RadioControl
        selected={showIntro}
        onChange={newShowIntro => {
          setShowIntro(newShowIntro === 'true');
          setAttributes({
            boolIntroParagraph: newShowIntro === 'true',
          });
        }}
        options={options}
      />
      {showIntro && (
        <TextareaControl
          value={introParagraphState}
          onChange={newIntroParagraph => {
            setIntroParagraphState(newIntroParagraph);
            setAttributes({
              introParagraph: newIntroParagraph,
            });
          }}
        />
      )}
    </div>
  );
}

export default IntroParagraph;
