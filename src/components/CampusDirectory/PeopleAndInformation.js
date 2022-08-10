import AutomatedFeeds from './AutomatedFeeds';
import InformationToDisplay from './InformationToDisplay';
import InformationToDisplayTable from './InformationToDisplayTable';
import CampusDirectoryDepartmentDropdown from './CampusDirectoryDepartmentDropdown';
import DivisionDropdown from '../DivisionDropdown';

import { RadioControl, TextareaControl, CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const PeopleAndInformation = ({
  setAttributes,
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
  strInformationTypesTable,
  pageLayout,
  division,
  department,
  deptOrDiv
}) => {
  let localAutomatedFeeds;
  let setLocalAutomatedFeeds;
  if (typeof automatedFeeds === 'undefined') {
    [localAutomatedFeeds, setLocalAutomatedFeeds] = useState(true);
    setAttributes({
      automatedFeeds: true,
    });
  } else {
    [localAutomatedFeeds, setLocalAutomatedFeeds] = useState(automatedFeeds);
  }

  let localManualAdd;
  let setLocalManualAdd;
  if (typeof manualAdd === 'undefined') {
    [localManualAdd, setLocalManualAdd] = useState(false);
    setAttributes({
      manualAdd: false,
    })
  } else {
    [localManualAdd, setLocalManualAdd] = useState(manualAdd);
  }

  let cruzidListState;
  let setCruzidListState;
  if (typeof cruzidList === 'undefined') {
    [cruzidListState, setCruzidListState] = useState('');
    setAttributes({
      cruzidList: '',
    })
  } else {
    [cruzidListState, setCruzidListState] = useState(cruzidList);

  }

  let addCruzidsList;
  let setAddCruzidsList;
  if (typeof addCruzids === 'undefined') {
    [addCruzidsList, setAddCruzidsList] = useState('');
    setAttributes({
      addCruzids: '',
    })
  } else {
    [addCruzidsList, setAddCruzidsList] = useState(addCruzids);
  }

  let excludeCruzidsList;
  let setExcludeCruzidsList;
  if (typeof excludeCruzids === 'undefined') {
    [excludeCruzidsList, setExcludeCruzidsList] = useState('');
    setAttributes({
      excludeCruzids: '',
    })
  } else {
    [excludeCruzidsList, setExcludeCruzidsList] = useState(excludeCruzids);
  }

  let boolDisplayDepartmentAffiliates;
  let setBoolDisplayDepartmentAffiliates;
  if (typeof displayDeptartmentAffiliates === 'undefined') {
    [
      boolDisplayDepartmentAffiliates,
      setBoolDisplayDepartmentAffiliates,
    ] = useState(false);
    setAttributes({
      displayDeptartmentAffiliates: false,
    });
  } else {
    [
      boolDisplayDepartmentAffiliates,
      setBoolDisplayDepartmentAffiliates,
    ] = useState(displayDeptartmentAffiliates);
  }

  let localDeptOrDiv;
  let setLocalDeptOrDiv;
  if (typeof deptOrDiv === 'undefined') {
    [localDeptOrDiv, setLocalDeptOrDiv] = useState('dept');
    setAttributes({
      deptOrDiv: 'dept'
    });
  } else {
    [localDeptOrDiv, setLocalDeptOrDiv] = useState(deptOrDiv);
  }

  const options = [
    { label: 'Use Automated Feed(s)', value: true },
    { label: 'Create My Own List of People to Display', value: false },
  ];

  const manualAddOptions = [
    { label: 'Yes', value: true },
    { label: 'No', value: false },
  ]

  return (
    <div>
      <div className="vertical_radio">
        <RadioControl
          selected={localAutomatedFeeds}
          onChange={newLocalAutomatedFeeds => {
            setLocalAutomatedFeeds(newLocalAutomatedFeeds === 'true');
            setAttributes({
              automatedFeeds: newLocalAutomatedFeeds === 'true',
            });
          }}
          options={options}
        />
      </div>
      {!localAutomatedFeeds && (
        <div>
          <h5>List Individuals (Enter CruzIDs separated by commas)</h5>
          <p><i>List individuals in the order you'd like them to appear on the page.</i></p>
          <TextareaControl
            value={cruzidListState}
            onChange={newCruzidListState => {
              setCruzidListState(newCruzidListState);
              setAttributes({
                cruzidList: newCruzidListState,
              });
            }}
          />
        </div>
      )}
      {localAutomatedFeeds && (
        <div>
          <h5>Set Department or Division</h5>
          <div className="vertical_radio">
            <RadioControl
                selected={ deptOrDiv }
                options={ [
                    { label: 'Department', value: 'dept' },
                    { label: 'Division', value: 'div' },
                ] }
                onChange={ ( value ) => {
                  setAttributes( { deptOrDiv: value } );
                  setLocalDeptOrDiv(value);
                }}
            />
            <hr />
            <CampusDirectoryDepartmentDropdown
              label="Department"
              department={department}
              setAttributes={setAttributes}
              disabled={!(deptOrDiv === 'dept')}
            />
            <DivisionDropdown
              label="Division"
              division={division}
              setAttributes={setAttributes}
              disabled={!(deptOrDiv === 'div')}
            />
          </div>
          <AutomatedFeeds
            setAttributes={setAttributes}
            strFacultyTypes={strFacultyTypes}
            strStaffTypes={strStaffTypes}
            strGradTypes={strGradTypes}
          />
          <h5>Manually Add or Exclude Individuals from the feed?</h5>
          <div className="vertical_radio">
            <RadioControl
              selected={localManualAdd}
              onChange={newLocalManualAdd => {
                setLocalManualAdd(newLocalManualAdd === 'true');
                setAttributes({
                  manualAdd: newLocalManualAdd === 'true',
                });
              }}
              options={manualAddOptions}
            />
          </div>
          {localManualAdd && (
            <div>
              <h5>
                Add Individuals to the Feed. (Enter CruzIDs separated by commas)
              </h5>
              <TextareaControl
                value={addCruzidsList}
                onChange={newAddCruzidsList => {
                  setAddCruzidsList(newAddCruzidsList);
                  setAttributes({
                    addCruzids: newAddCruzidsList,
                  });
                }}
              />
              <h5>
                Exclude Individuals from the Feed. (Enter CruzIDs separated by
                commas)
              </h5>
              <TextareaControl
                value={excludeCruzidsList}
                onChange={newExcludeCruzidsList => {
                  setExcludeCruzidsList(newExcludeCruzidsList);
                  setAttributes({
                    excludeCruzids: newExcludeCruzidsList,
                  });
                }}
              />
            </div>
          )}
          {deptOrDiv === "dept" && (
            <div>
              <h5>
                Display Department Affiliates? (Rare for administrative units.)
              </h5>
              <p>
                <i>
                  For example, a faculty member is officially associated with the
                  History Department but is also affiliated with College Nine
                  because they teach a College Nine Core Course.
                </i>
              </p>
              <div className="flex-labels">
                <CheckboxControl
                  label="Display Affiliates"
                  checked={boolDisplayDepartmentAffiliates}
                  onChange={newValue => {
                    setBoolDisplayDepartmentAffiliates(newValue);
                    setAttributes({
                      displayDeptartmentAffiliates: newValue
                    });
                  }}
                />
              </div>
            </div>
          )}
        </div>
      )}
      {pageLayout !== "table" ? (
        <InformationToDisplay
          setAttributes={setAttributes}
          linkToProfile={linkToProfile}
          strInformationTypes={strInformationTypes}
        />
      ) : (
        <InformationToDisplayTable
          setAttributes={setAttributes}
          linkToProfile={linkToProfile}
          strInformationTypesTable={strInformationTypesTable}
        />
      )}
    </div>
  );
}

export default PeopleAndInformation;
