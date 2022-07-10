import { SelectControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element'

const DivisionDropdown = ({
  division,
  setAttributes,
  label,
  disabled = false
}) => {
  const [divisions, setDivisions] = useState([]);
  useEffect(() => {
    fetch('/wp-json/ucscgutenbergblocks/v1/divisioncode')
    .then(res => res.text())
    .then((text) => {
      const resp = JSON.parse(text);
      setDivisions(resp);
    });
  }, []);

  return (
    <>
      {divisions.length > 0 && (
        <div style={{width: "max-content"}}>
          <SelectControl
            label={ label }
            value={ division }
            options={ divisions }
            onChange={ ( newDivisions ) => setAttributes( {division: newDivisions} ) }
            disabled={disabled}
          />
        </div>
      )}
    </>
  )
}

export default DivisionDropdown;
