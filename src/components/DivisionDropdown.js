import { SelectControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

const DivisionDropdown = ({
	division,
	setAttributes,
	label,
	disabled = false,
}) => {
	const [divisions, setDivisions] = useState([]);

	let localDivision;
	let setLocalDivision;
	if (typeof division === 'undefined') {
		[localDivision, setLocalDivision] = useState('---');
		setAttributes({
			division: '---',
		});
	} else {
		[localDivision, setLocalDivision] = useState(division);
	}

	useEffect(() => {
		fetch('/wp-json/ucscgutenbergblocks/v1/divisioncode')
			.then((res) => res.text())
			.then((text) => {
				const resp = JSON.parse(text);
				setDivisions(resp);
			});
	}, []);

	return (
		<>
			<div style={{ width: 'max-content' }}>
				{divisions.length > 0 && (
					<SelectControl
						label={label}
						value={division}
						options={divisions}
						onChange={(newDivision) => {
							setAttributes({ division: newDivision });
							setLocalDivision(newDivision);
						}}
						disabled={disabled}
					/>
				)}
				{!divisions.length && (
					<span>Divisions Dropdown Loading...</span>
				)}
			</div>
		</>
	);
};

export default DivisionDropdown;
