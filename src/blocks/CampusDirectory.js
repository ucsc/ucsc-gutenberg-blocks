import { useEffect, useState } from '@wordpress/element';
import { dispatch } from '@wordpress/data';
import { Panel, PanelBody, PanelRow, Notice, SelectControl } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';

import CampusDirectoryDepartmentDropdown from '../components//CampusDirectory/CampusDirectoryDepartmentDropdown';
import DivisionDropdown from '../components/DivisionDropdown';
import { fetchJson } from '../components/shared/fetchJson';
import PageLayout from '../components/CampusDirectory/PageLayout';
import PeopleAndInformation from '../components/CampusDirectory/PeopleAndInformation';

const CampusDirectory = () => {
  wp.blocks.registerBlockType("ucscblocks/campusdirectory", {
    apiVersion: 3,
    title: "Campus Directory",
    icon: "welcome-learn-more",
    category: "common",
    attributes: {
      pageLayout: { type: "string" },
      automatedFeeds: { type: "boolean" },
      cruzidList: { type: "string" },
      strFacultyTypes: { type: "string" },
      strStaffTypes: { type: "string" },
      strGradTypes: { type: "string" },
      manualAdd: { type: "boolean" },
      addCruzids: { type: "string" },
      excludeCruzids: { type: "string" },
      displayDeptartmentAffiliates: { type: "boolean" },
      linkToProfile: { type: "boolean" },
      linkOutToCampusDirectory: { type: "boolean" },
      strInformationTypes: { type: "string" },
      strInformationTypesTable: { type: "string" },
      department: { type: "string" },
      division: { type: "string" },
      deptOrDiv: { type: "string" },
      headingLevel: { type: "number", default: 3 },
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
        headingLevel,
      } = attributes;
      const blockProps = useBlockProps();

      const [configuredCorrectly, setConfiguredCorrectly] = useState(true);
      const [resp, setResp] = useState({});
      const [isInvalidState, setIsInvalidState] = useState(false);

      // Lock or unlock post saving based on attribute state
      useEffect(() => {
        const invalidState = automatedFeeds && (
          (deptOrDiv === 'dept' && department === '---') ||
          (deptOrDiv === 'div' && division === '---')
        );
        setIsInvalidState(invalidState);

        if (invalidState) {
          dispatch('core/editor').lockPostSaving('campusDirectoryInvalidState');
        } else {
          dispatch('core/editor').unlockPostSaving('campusDirectoryInvalidState');
        }
      }, [automatedFeeds, department, division, deptOrDiv]);

      useEffect(() => {
        fetchJson("/wp-json/ucscgutenbergblocks/v1/campusdirectoryrequirements")
          .then((resp) => {
            if (!resp.ldap_pass) setConfiguredCorrectly(false);
            setResp(resp);
          })
          // WPM-207: if the check itself fails we cannot tell, so leave the
          // block usable instead of throwing or reporting it misconfigured.
          .catch(() => {});
      }, []);

      return (
        <div {...blockProps}>
          {isInvalidState && (
            <Notice
              status="error"
              isDismissible={false}
            >
              <strong>Unable to publish:</strong> Please select a valid department or division when using automated feeds, or select 'Create My Own List of People to Display' to enable publishing.
            </Notice>
          )}
          {configuredCorrectly && (
            <Panel className="directory-block" header="Directory Block">
              <PanelBody title="Layout Type" initialOpen>
                <PanelRow>
                  <PageLayout
                    setAttributes={setAttributes}
                    pageLayout={pageLayout}
                  />
                </PanelRow>
                {pageLayout !== 'table' && (
                  <PanelRow>
                    {/* WPM-92: let the names fit the page outline (H2 when the page has no section headings) */}
                    <SelectControl
                      label="Person name heading level"
                      help="Pick the level below the nearest heading above this block."
                      value={String(headingLevel ?? 3)}
                      options={[
                        { label: 'H2', value: '2' },
                        { label: 'H3', value: '3' },
                        { label: 'H4', value: '4' },
                      ]}
                      onChange={(value) => setAttributes({ headingLevel: Number(value) })}
                    />
                  </PanelRow>
                )}
              </PanelBody>
              <PanelBody title="People and Information to Display" initialOpen>
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
                    displayDeptartmentAffiliates={displayDeptartmentAffiliates}
                    linkToProfile={linkToProfile}
                    linkOutToCampusDirectory={linkOutToCampusDirectory}
                    strInformationTypes={strInformationTypes}
                    strInformationTypesTable={strInformationTypesTable}
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
              <p><strong>This Block is not Configured Correctly</strong></p>
              {!resp.ldap_pass && resp.multisite && (
                <>
                  <p>
                    The LDAP password can be set at the network level{" "}
                    <a
                      target="_blank"
                      href="/wp-admin/network/settings.php?page=ucsc-gutenberg-blocks-network-settings"
                    >
                      here.
                    </a>
                  </p>
                  <p>
                    Or the LDAP password can be set at the site level below.
                  </p>
                </>
              )}
              {!resp.ldap_pass && (
                <p>
                  The LDAP password needs to be set here{" "}
                  <a
                    target="_blank"
                    href="/wp-admin/options-general.php?page=ucsc_gutenberg_blocks_settings_page"
                  >
                    here.
                  </a>
                </p>
              )}
            </>
          )}
        </div>
      );
    },
    save: () => {
      return null;
    },
  });
};

export default CampusDirectory;
