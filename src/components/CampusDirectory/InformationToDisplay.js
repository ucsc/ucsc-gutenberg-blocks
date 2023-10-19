import CheckboxGroupControl from './CheckboxGroupControl';

import {
	RadioControl,
	TextareaControl,
	CheckboxControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';

const InformationToDisplay = ({
	setAttributes,
	linkToProfile,
	linkOutToCampusDirectory,
	strInformationTypes,
	pageLayout,
}) => {
	let localLinkToProfile;
	let setLocalLinkToProfile;
	if (typeof linkToProfile === 'undefined') {
		[localLinkToProfile, setLocalLinkToProfile] = useState(true);
		setAttributes({
			linkToProfile: true,
		});
	} else {
		[localLinkToProfile, setLocalLinkToProfile] = useState(linkToProfile);
	}

	let boolLinkOutToCampusDirectory;
	let setBoolLinkOutToCampusDirectory;
	if (typeof linkOutToCampusDirectory === 'undefined') {
		[boolLinkOutToCampusDirectory, setBoolLinkOutToCampusDirectory] =
			useState(false);
		setAttributes({
			linkOutToCampusDirectory: false,
		});
	} else {
		[boolLinkOutToCampusDirectory, setBoolLinkOutToCampusDirectory] =
			useState(linkOutToCampusDirectory);
	}

	const options = [
		{ label: 'Yes', value: true },
		{ label: 'No', value: false },
	];

	// List of checkbox labels/values
	const arrInformationToDisplay = [
		'Pronouns',
		'Photo',
		'Title',
		'Department',
		'Phone',
		'Campus Email',
		'Other Email',
		'Fax',
		'Website',
		'Office Location',
		'Office Hours',
		'Mailstop',
		'Mailing Address',
		'Faculty Areas of Expertise',
		'Summary of Expertise',
	];

	const checkedByDefault = [
		'Photo',
		'Title',
		'Department',
		'Phone',
		'Campus Email',
		'Website',
		'Office Location',
		'Office Hours',
	];

	return (
		<div>
			<hr />
			<h5>Link Name to Individual Profile?</h5>
			<p>
				<i>
					In a small number of cases, units do not want a person's
					name to link to a more detailed profile page.
				</i>
			</p>
			<div className="vertical_radio">
				<RadioControl
					selected={localLinkToProfile}
					onChange={(newLocalLinkToProfile) => {
						setLocalLinkToProfile(newLocalLinkToProfile === 'true');
						setAttributes({
							linkToProfile: newLocalLinkToProfile === 'true',
						});
					}}
					options={options}
				/>
			</div>
			<div style={{ paddingTop: '10px' }} className="flex-labels">
				<CheckboxControl
					label="Link to campusdirectory.ucsc.edu"
					checked={boolLinkOutToCampusDirectory}
					onChange={(newValue) => {
						setBoolLinkOutToCampusDirectory(newValue);
						setAttributes({
							linkOutToCampusDirectory: newValue,
						});
					}}
					disabled={!localLinkToProfile}
				/>
			</div>
			<hr />
			<div className="flex-labels">
				<h5>
					{pageLayout === 'list' ? 'List' : 'Tiled'} Layout
					Information to Display
				</h5>
				<CheckboxGroupControl
					setAttributes={setAttributes}
					currentAttributes={strInformationTypes}
					arrOfLabels={arrInformationToDisplay}
					attributeStr="strInformationTypes"
					checkedByDefault={checkedByDefault}
				/>
			</div>
		</div>
	);
};

export default InformationToDisplay;
