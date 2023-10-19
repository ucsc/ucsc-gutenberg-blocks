import { SelectControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

const DepartmentDropdown = ({
	department,
	setAttributes,
	label,
	disabled = false,
}) => {
	const [departments, setDepartments] = useState([]);

	let localDepartment;
	let setLocalDepartment;
	if (typeof department === 'undefined') {
		[localDepartment, setLocalDepartment] = useState('---');
		setAttributes({
			department: '---',
		});
	} else {
		[localDepartment, setLocalDepartment] = useState(department);
	}
	useEffect(() => {
		fetch('/wp-json/ucscgutenbergblocks/v1/departmentcode')
			.then((res) => res.text())
			.then((text) => {
				const resp = JSON.parse(text);
				setDepartments(resp);
			});
	}, []);

	return (
		<>
			<div style={{ width: 'max-content' }}>
				{departments.length > 0 && (
					<SelectControl
						label={label}
						value={department}
						options={departments}
						onChange={(newDepartment) => {
							setAttributes({ department: newDepartment });
							setLocalDepartment(newDepartment);
						}}
						disabled={disabled}
					/>
				)}
				{!departments.length && (
					<span>Subject Dropdown Loading...</span>
				)}
			</div>
		</>
	);
};

export default DepartmentDropdown;
