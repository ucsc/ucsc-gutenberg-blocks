import { SelectControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element'

const DepartmentDropdown = ({
  department,
  setAttributes,
  label,
  disabled = false
}) => {
  const [departments, setDepartments] = useState([]);
  useEffect(() => {
    fetch('/wp-json/ucscgutenbergblocks/v1/departmentcode')
    .then(res => res.text())
    .then((text) => {
      const resp = JSON.parse(text);
      setDepartments(resp);
    });
  }, []);

  return (
    <>
      {departments.length > 0 && (
        <div style={{width: "max-content"}}>
          <SelectControl
            label={ label }
            value={ department }
            options={ departments }
            onChange={ ( newDepartment ) => setAttributes( {department: newDepartment} ) }
            disabled={disabled}
          />
        </div>
      )}
    </>
  )
}

export default DepartmentDropdown;
