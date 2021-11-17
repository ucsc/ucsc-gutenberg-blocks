import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const CheckboxGroupControl = ({
  setAttributes,
  currentAttributes,
  arrOfLabels,
  flexCheckboxes,
  attributeStr,
  checkedByDefault,
}) => {
  // Initialize array that will render checkboxes
  const arrRender = [];
  arrOfLabels.map(label => arrRender.push({ label }));

  // If the attribute hasn't been defined yet (creating a new record)
  // initialize strFacultyTypes to a stringafied object of {labels} => false
  if (typeof currentAttributes === 'undefined') {
    const objAttributeLabels = {};
    arrOfLabels.map(label => {
      if (typeof checkedByDefault === 'undefined') {
        objAttributeLabels[label] = false;
      } else {
        objAttributeLabels[label] = checkedByDefault.includes(label);
      }
    });
    const attrObj = {};
    attrObj[attributeStr] = JSON.stringify(objAttributeLabels);
    setAttributes(attrObj);
  }

  // add state vars/fxn to each object to be rendered
  arrRender.map(label => {
    if (typeof currentAttributes === 'undefined') {
      if (typeof checkedByDefault === 'undefined') {
        label.arrCheckedState = useState(false);
      } else {
        label.arrCheckedState = useState(checkedByDefault.includes(label.label));
      }
    } else {
      const objLabels = JSON.parse(currentAttributes);
      label.arrCheckedState = useState(objLabels[label.label]);
    }
  });

  return (
    <div className={flexCheckboxes ? 'flex-checkboxes' : ''}>
      {arrRender.map(label => (
        <CheckboxControl
          label={label.label}
          checked={label.arrCheckedState[0]}
          onChange={newValue => {
            label.arrCheckedState[1](newValue);
            const objLabels = JSON.parse(currentAttributes);
            objLabels[label.label] = newValue;
            const attrObj = {};
            attrObj[attributeStr] = JSON.stringify(objLabels);
            setAttributes(attrObj);
          }}
        />
      ))}
    </div>
  );
};

export default CheckboxGroupControl;
