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

      return (
        <>
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
        </>
      )
    },
    save: (props) => {
      return null;
    }
  })
}

export default CampusDirectory;
