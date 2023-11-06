import { useEffect, useState } from '@wordpress/element';
import {
	Panel,
	PanelBody,
	PanelRow,
	RadioControl,
} from '@wordpress/components';

import CampusDirectoryDepartmentDropdown from '../components//CampusDirectory/CampusDirectoryDepartmentDropdown';
import DivisionDropdown from '../components/DivisionDropdown';
import PageLayout from '../components/CampusDirectory/PageLayout';
import PeopleAndInformation from '../components/CampusDirectory/PeopleAndInformation';

const CampusDirectory = () => {
	wp.blocks.registerBlockType('ucscservice/campusdirectory', {
		title: 'Campus Directory',
		icon: 'welcome-learn-more',
		category: 'common',
		attributes: {
			pageLayout: {
				type: 'string',
			},
			automatedFeeds: {
				type: 'boolean',
			},
			cruzidList: {
				type: 'string',
			},
			strFacultyTypes: {
				type: 'string',
			},
			strStaffTypes: {
				type: 'string',
			},
			strGradTypes: {
				type: 'string',
			},
			manualAdd: {
				type: 'boolean',
			},
			addCruzids: {
				type: 'string',
			},
			excludeCruzids: {
				type: 'string',
			},
			displayDeptartmentAffiliates: {
				type: 'boolean',
			},
			linkToProfile: {
				type: 'boolean',
			},
			linkOutToCampusDirectory: {
				type: 'boolean',
			},
			strInformationTypes: {
				type: 'string',
			},
			strInformationTypesTable: {
				type: 'string',
			},
			department: {
				type: 'string',
			},
			division: {
				type: 'string',
			},
			deptOrDiv: {
				type: 'string',
			},
		},
		edit: ({ setAttributes, attributes }) => {
			const {
				pageLayout,
				automatedFeeds,
				cruzidList,
				strFacultyTypes,
				strStaffTypes,
				strGradTypes,
				manualAdd,
				addCruzids,
				excludeCruzids,
				displayDeptartmentAffiliates,
				linkToProfile,
				linkOutToCampusDirectory,
				strInformationTypes,
				strInformationTypesTable,
				department,
				division,
				deptOrDiv,
			} = attributes;

			const [configuredCorrectly, setConfiguredCorrectly] =
				useState(true);
			const [resp, setResp] = useState({});

			useEffect(() => {
				fetch(
					'/wp-json/ucscgutenbergblocks/v1/campusdirectoryrequirements'
				)
					.then((res) => res.text())
					.then((text) => {
						const resp = JSON.parse(text);
						if (!resp.ldap_pass) setConfiguredCorrectly(false);
						setResp(resp);
					});
			}, []);

			return (
				<>
					{configuredCorrectly && (
						<Panel
							className="directory-block"
							header="Directory Block"
						>
							<PanelBody title="Layout Type" initialOpen>
								<PanelRow>
									<PageLayout
										setAttributes={setAttributes}
										pageLayout={pageLayout}
									/>
								</PanelRow>
							</PanelBody>
							<PanelBody
								title="People and Information to Display"
								initialOpen
							>
								<PanelRow>
									<PeopleAndInformation
										setAttributes={setAttributes}
										automatedFeeds={automatedFeeds}
										cruzidList={cruzidList}
										strFacultyTypes={strFacultyTypes}
										strStaffTypes={strStaffTypes}
										strGradTypes={strGradTypes}
										manualAdd={manualAdd}
										addCruzids={addCruzids}
										excludeCruzids={excludeCruzids}
										displayDeptartmentAffiliates={
											displayDeptartmentAffiliates
										}
										linkToProfile={linkToProfile}
										linkOutToCampusDirectory={
											linkOutToCampusDirectory
										}
										strInformationTypes={
											strInformationTypes
										}
										strInformationTypesTable={
											strInformationTypesTable
										}
										pageLayout={pageLayout}
										division={division}
										department={department}
										deptOrDiv={deptOrDiv}
									/>
								</PanelRow>
							</PanelBody>
						</Panel>
					)}
					{!configuredCorrectly && (
						<>
							<h2>This Block is not Configured Correctly</h2>
							{!resp.ldap_pass && resp.multisite && (
								<>
									<h4>
										The LDAP password can be set at the
										network level{' '}
										<a
											target="_blank"
											href="/wp-admin/network/settings.php?page=ucsc-gutenberg-blocks-network-settings"
										>
											here.
										</a>
									</h4>
									<h6>
										Or the LDAP password can be set at the
										site level below.
									</h6>
								</>
							)}
							{!resp.ldap_pass && (
								<h4>
									The LDAP password needs to be set here{' '}
									<a
										target="_blank"
										href="/wp-admin/options-general.php?page=ucsc-service-blocks-plugin-settings"
									>
										here.
									</a>
								</h4>
							)}
						</>
					)}
				</>
			);
		},
		save: (props) => {
			return null;
		},
	});
};

export default CampusDirectory;
