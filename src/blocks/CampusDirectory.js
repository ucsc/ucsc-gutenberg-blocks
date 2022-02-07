import { useEffect, useState } from '@wordpress/element'
import { Panel, PanelBody, PanelRow } from '@wordpress/components';

import IntroParagraph from '../components/CampusDirectory/IntroParagraph';
import PageLayout from '../components/CampusDirectory/PageLayout';
import PeopleAndInformation from '../components/CampusDirectory/PeopleAndInformation';


const CampusDirectory = () => {
  wp.blocks.registerBlockType("ucscblocks/campusdirectory", {
    title: "Campus Directory",
    icon: "welcome-learn-more",
    category: "common",
    attributes: {
      boolIntroParagraph: {
        type: 'boolean',
      },
      introParagraph: {
        type: 'string',
      },
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
      strInformationTypes: {
        type: 'string',
      },
    },
    edit: ({ setAttributes, attributes }) => {
      const {
        boolIntroParagraph,
        introParagraph,
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
        strInformationTypes,
      } = attributes;

      const [configuredCorrectly, setConfiguredCorrectly] = useState(true);
      const [resp, setResp] = useState({});

      useEffect(() => {
        fetch('/wp-json/ucscgutenbergblocks/v1/campusdirectoryrequirements')
          .then(res => res.text())
          .then((text) => {
            const resp = JSON.parse(text);
            if (!resp.ldap_pass || !resp.deptdiv) setConfiguredCorrectly(false);
            setResp(resp);
          });
      }, []);

      return (
        <>
          {configuredCorrectly && (
            <Panel header="Directory Block">
              <PanelBody title="Intro Paragraph">
                <IntroParagraph
                  setAttributes={setAttributes}
                  boolIntroParagraph={boolIntroParagraph}
                  introParagraph={introParagraph}
                />
              </PanelBody>
              <PanelBody title="Page Layout" initialOpen>
                <PanelRow>
                  <PageLayout
                    setAttributes={setAttributes}
                    pageLayout={pageLayout}
                  />
                </PanelRow>
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
                    strInformationTypes={strInformationTypes}
                  />
                </PanelRow>
              </PanelBody>
            </Panel>
          )}
          {!configuredCorrectly && (
            <>
              <h2>This Block is not Configured Correctly</h2>
              {!resp.deptdiv && (
                <h4>
                  The Department or Division needs to be set <a target="_blank" href="/wp-admin/options-general.php?page=ucsc_gutenberg_blocks_settings_page">here.</a>
                </h4>
              )}
              {!resp.ldap_pass && resp.multisite && (
                <>
                  <h4>
                    The LDAP password can be set at the network level <a target="_blank" href="/wp-admin/network/settings.php?page=ucsc-gutenberg-blocks-network-settings">here.</a>
                  </h4>
                  <h6>Or the LDAP password can be set at the site level below.</h6>
                </>
              )}
              {!resp.ldap_pass && (
                <h4>
                  The LDAP password needs to be set here <a target="_blank" href="/wp-admin/options-general.php?page=ucsc_gutenberg_blocks_settings_page">here.</a>
                </h4>
              )}
            </>
          )}
        </>
      )
    },
    save: (props) => {
      return null;
    }
  })
}

export default CampusDirectory;
