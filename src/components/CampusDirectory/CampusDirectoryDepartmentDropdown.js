import { SelectControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element'
import { fetchOptions } from '../shared/fetchJson';

const DepartmentDropdown = ({
  department,
  setAttributes,
  label,
  disabled = false
}) => {
  const [departments, setDepartments] = useState([]);
  const [loadFailed, setLoadFailed] = useState(false);


  let localDepartment;
  let setLocalDepartment;
  if (typeof department === 'undefined') {
    [localDepartment, setLocalDepartment] = useState('---');
    setAttributes({
      department: '---'
    });
  } else {
    [localDepartment, setLocalDepartment] = useState(department);
  }
  useEffect(() => {
    fetchOptions('/wp-json/ucscgutenbergblocks/v1/cddepartmentcode')
    .then(setDepartments)
    .catch(() => setLoadFailed(true));
  }, []);

  return (
    <>
      <div style={{width: "max-content"}}>
        {departments.length > 0 && (
          <SelectControl
            label={ label }
            value={ department }
            options={ departments }
            onChange={ ( newDepartment ) => {
              setAttributes( {department: newDepartment} );
              setLocalDepartment(newDepartment);
            }}
            disabled={disabled}
          />
        )}
        {!departments.length && !loadFailed && (
          <span>Department Dropdown Loading...</span>
        )}
        {loadFailed && (
          <span role="alert">Could not load departments. Reload the page to try again.</span>
        )}
      </div>
    </>
  )
}

export default DepartmentDropdown;
