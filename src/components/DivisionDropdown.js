import { SelectControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element'
import { fetchOptions } from './shared/fetchJson';

const DivisionDropdown = ({
  division,
  setAttributes,
  label,
  disabled = false
}) => {
  const [divisions, setDivisions] = useState([]);
  const [loadFailed, setLoadFailed] = useState(false);

  let localDivision;
  let setLocalDivision;
  if (typeof division === 'undefined') {
    [localDivision, setLocalDivision] = useState('---');
    setAttributes({
      division: '---'
    });
  } else {
    [localDivision, setLocalDivision] = useState(division);
  }

  useEffect(() => {
    fetchOptions('/wp-json/ucscgutenbergblocks/v1/divisioncode')
    .then(setDivisions)
    .catch(() => setLoadFailed(true));
  }, []);

  return (
    <>
      <div style={{width: "max-content"}}>
        {divisions.length > 0 && (
          <SelectControl
            label={ label }
            value={ division }
            options={ divisions }
            onChange={ ( newDivision ) => {
              setAttributes( {division: newDivision} );
              setLocalDivision(newDivision)
            }}
            disabled={disabled}
          />
        )}
        {!divisions.length && !loadFailed && (
          <span>Divisions Dropdown Loading...</span>
        )}
        {loadFailed && (
          <span role="alert">Could not load divisions. Reload the page to try again.</span>
        )}
      </div>
    </>
  )
}

export default DivisionDropdown;
