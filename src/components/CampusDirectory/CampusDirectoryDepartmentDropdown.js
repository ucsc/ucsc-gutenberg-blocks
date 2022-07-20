import { SelectControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element'

const DepartmentDropdown = ({
  department,
  setAttributes,
  label,
  disabled = false
}) => {
  const [departments, setDepartments] = useState([]);


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
    fetch('/wp-json/ucscgutenbergblocks/v1/departmentcode')
    .then(res => res.text())
    .then((text) => {
      const resp = JSON.parse(text).map(item => {return {label: item.label, value: item.label}});
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
            onChange={ ( newDepartment ) => {
              setAttributes( {department: newDepartment} );
              setLocalDepartment(newDepartment);
            }}
            disabled={disabled}
          />
        </div>
      )}
    </>
  )
}

export default DepartmentDropdown;
