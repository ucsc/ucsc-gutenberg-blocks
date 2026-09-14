import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

jest.mock('@wordpress/components', () => ({
  RadioControl: ({ selected, options, onChange }) => (
    <div data-testid="radio-control" data-selected={String(selected)}>
      {options.map((opt) => (
        <button key={String(opt.value)} onClick={() => onChange(String(opt.value))}>
          {opt.label}
        </button>
      ))}
    </div>
  ),
  TextareaControl: ({ value, onChange }) => (
    <textarea
      data-testid="textarea-control"
      value={value}
      onChange={(e) => onChange(e.target.value)}
    />
  ),
  CheckboxControl: ({ label, checked, onChange }) => (
    <label>
      <input
        type="checkbox"
        aria-label={label}
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
      />
      {label}
    </label>
  ),
}), { virtual: true });

// Children below have their own dedicated test suites; mock them here so this
// file tests PeopleAndInformation's own wiring/visibility logic in isolation.
jest.mock('../AutomatedFeeds', () => () => <div data-testid="automated-feeds" />);
jest.mock('../InformationToDisplay', () => (props) => (
  <div data-testid="information-to-display" data-page-layout={props.pageLayout} />
));
jest.mock('../InformationToDisplayTable', () => () => (
  <div data-testid="information-to-display-table" />
));
jest.mock('../CampusDirectoryDepartmentDropdown', () => (props) => (
  <div
    data-testid="department-dropdown"
    data-department={props.department}
    data-disabled={String(props.disabled)}
  />
));
jest.mock('../../DivisionDropdown', () => (props) => (
  <div
    data-testid="division-dropdown"
    data-division={props.division}
    data-disabled={String(props.disabled)}
  />
));

import PeopleAndInformation from '../PeopleAndInformation';

const defaultProps = {
  setAttributes: jest.fn(),
  automatedFeeds: true,
  cruzidList: '',
  strFacultyTypes: undefined,
  strStaffTypes: undefined,
  strGradTypes: undefined,
  manualAdd: false,
  addCruzids: '',
  excludeCruzids: '',
  displayDeptartmentAffiliates: false,
  linkToProfile: true,
  linkOutToCampusDirectory: true,
  strInformationTypes: undefined,
  strInformationTypesTable: undefined,
  pageLayout: 'list',
  division: undefined,
  department: undefined,
  deptOrDiv: 'dept',
};

describe('PeopleAndInformation', () => {
  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('attribute defaults', () => {
    it('defaults automatedFeeds to true when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <PeopleAndInformation {...defaultProps} setAttributes={setAttributes} automatedFeeds={undefined} />
      );
      expect(setAttributes).toHaveBeenCalledWith({ automatedFeeds: true });
    });

    it('defaults manualAdd to false when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <PeopleAndInformation {...defaultProps} setAttributes={setAttributes} manualAdd={undefined} />
      );
      expect(setAttributes).toHaveBeenCalledWith({ manualAdd: false });
    });

    it('defaults cruzidList to an empty string when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <PeopleAndInformation {...defaultProps} setAttributes={setAttributes} cruzidList={undefined} />
      );
      expect(setAttributes).toHaveBeenCalledWith({ cruzidList: '' });
    });

    it('defaults addCruzids to an empty string when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <PeopleAndInformation {...defaultProps} setAttributes={setAttributes} addCruzids={undefined} />
      );
      expect(setAttributes).toHaveBeenCalledWith({ addCruzids: '' });
    });

    it('defaults excludeCruzids to an empty string when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <PeopleAndInformation {...defaultProps} setAttributes={setAttributes} excludeCruzids={undefined} />
      );
      expect(setAttributes).toHaveBeenCalledWith({ excludeCruzids: '' });
    });

    it('defaults displayDeptartmentAffiliates to false when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <PeopleAndInformation
          {...defaultProps}
          setAttributes={setAttributes}
          displayDeptartmentAffiliates={undefined}
        />
      );
      expect(setAttributes).toHaveBeenCalledWith({ displayDeptartmentAffiliates: false });
    });

    it('defaults deptOrDiv to "dept" when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <PeopleAndInformation {...defaultProps} setAttributes={setAttributes} deptOrDiv={undefined} />
      );
      expect(setAttributes).toHaveBeenCalledWith({ deptOrDiv: 'dept' });
    });

    it('does not re-default already-provided values', () => {
      const setAttributes = jest.fn();
      render(<PeopleAndInformation {...defaultProps} setAttributes={setAttributes} />);
      expect(setAttributes).not.toHaveBeenCalled();
    });
  });

  describe('audience-mode switching (automatedFeeds)', () => {
    it('shows the department/division section and hides the manual CruzID list when automatedFeeds is true', () => {
      render(<PeopleAndInformation {...defaultProps} automatedFeeds={true} />);

      expect(screen.getByText('Set Department or Division')).toBeInTheDocument();
      expect(screen.queryByText('List Individuals (Enter CruzIDs separated by commas)')).not.toBeInTheDocument();
    });

    it('shows the manual CruzID list and hides the department/division section when automatedFeeds is false', () => {
      render(<PeopleAndInformation {...defaultProps} automatedFeeds={false} />);

      expect(screen.getByText('List Individuals (Enter CruzIDs separated by commas)')).toBeInTheDocument();
      expect(screen.queryByText('Set Department or Division')).not.toBeInTheDocument();
      expect(screen.queryByTestId('automated-feeds')).not.toBeInTheDocument();
    });

    it('switches to the manual list view and calls setAttributes when the radio is toggled off', () => {
      const setAttributes = jest.fn();
      render(<PeopleAndInformation {...defaultProps} setAttributes={setAttributes} automatedFeeds={true} />);

      fireEvent.click(screen.getByText('Create My Own List of People to Display'));

      expect(setAttributes).toHaveBeenCalledWith({ automatedFeeds: false });
      expect(screen.getByText('List Individuals (Enter CruzIDs separated by commas)')).toBeInTheDocument();
    });

    it('updates cruzidList via the manual textarea onChange', () => {
      const setAttributes = jest.fn();
      render(<PeopleAndInformation {...defaultProps} setAttributes={setAttributes} automatedFeeds={false} />);

      fireEvent.change(screen.getByTestId('textarea-control'), { target: { value: 'abc123,def456' } });

      expect(setAttributes).toHaveBeenCalledWith({ cruzidList: 'abc123,def456' });
    });
  });

  describe('dept/div exclusivity rendering', () => {
    it('enables the department dropdown and disables the division dropdown when deptOrDiv is "dept"', () => {
      render(<PeopleAndInformation {...defaultProps} deptOrDiv="dept" />);

      expect(screen.getByTestId('department-dropdown')).toHaveAttribute('data-disabled', 'false');
      expect(screen.getByTestId('division-dropdown')).toHaveAttribute('data-disabled', 'true');
    });

    it('enables the division dropdown and disables the department dropdown when deptOrDiv is "div"', () => {
      render(<PeopleAndInformation {...defaultProps} deptOrDiv="div" />);

      expect(screen.getByTestId('department-dropdown')).toHaveAttribute('data-disabled', 'true');
      expect(screen.getByTestId('division-dropdown')).toHaveAttribute('data-disabled', 'false');
    });

    it('shows the "Display Affiliates" section only when deptOrDiv is "dept"', () => {
      render(<PeopleAndInformation {...defaultProps} deptOrDiv="dept" />);
      expect(screen.getByLabelText('Display Affiliates')).toBeInTheDocument();
    });

    it('hides the "Display Affiliates" section when deptOrDiv is "div"', () => {
      render(<PeopleAndInformation {...defaultProps} deptOrDiv="div" />);
      expect(screen.queryByLabelText('Display Affiliates')).not.toBeInTheDocument();
    });

    it('switches to division mode and calls setAttributes when the Division radio option is chosen', () => {
      const setAttributes = jest.fn();
      render(<PeopleAndInformation {...defaultProps} setAttributes={setAttributes} deptOrDiv="dept" />);

      fireEvent.click(screen.getByText('Division'));

      expect(setAttributes).toHaveBeenCalledWith({ deptOrDiv: 'div' });
    });
  });

  describe('manual add/exclude visibility', () => {
    it('hides the add/exclude CruzID fields when manualAdd is false', () => {
      render(<PeopleAndInformation {...defaultProps} manualAdd={false} />);

      expect(
        screen.queryByText('Add Individuals to the Feed. (Enter CruzIDs separated by commas)')
      ).not.toBeInTheDocument();
      expect(
        screen.queryByText('Exclude Individuals from the Feed. (Enter CruzIDs separated by commas)')
      ).not.toBeInTheDocument();
    });

    it('shows the add/exclude CruzID fields when manualAdd is true', () => {
      render(<PeopleAndInformation {...defaultProps} manualAdd={true} />);

      expect(
        screen.getByText('Add Individuals to the Feed. (Enter CruzIDs separated by commas)')
      ).toBeInTheDocument();
      expect(
        screen.getByText('Exclude Individuals from the Feed. (Enter CruzIDs separated by commas)')
      ).toBeInTheDocument();
    });

    it('reveals the fields and calls setAttributes when manual add is toggled on', () => {
      const setAttributes = jest.fn();
      render(<PeopleAndInformation {...defaultProps} setAttributes={setAttributes} manualAdd={false} />);

      fireEvent.click(screen.getByText('Yes'));

      expect(setAttributes).toHaveBeenCalledWith({ manualAdd: true });
      expect(
        screen.getByText('Add Individuals to the Feed. (Enter CruzIDs separated by commas)')
      ).toBeInTheDocument();
    });

    it('updates addCruzids and excludeCruzids via their respective textareas', () => {
      const setAttributes = jest.fn();
      render(<PeopleAndInformation {...defaultProps} setAttributes={setAttributes} manualAdd={true} />);

      const [addTextarea, excludeTextarea] = screen.getAllByTestId('textarea-control');

      fireEvent.change(addTextarea, { target: { value: 'add1,add2' } });
      expect(setAttributes).toHaveBeenCalledWith({ addCruzids: 'add1,add2' });

      fireEvent.change(excludeTextarea, { target: { value: 'exc1,exc2' } });
      expect(setAttributes).toHaveBeenCalledWith({ excludeCruzids: 'exc1,exc2' });
    });
  });

  describe('page layout switching', () => {
    it('renders InformationToDisplay (not the table variant) when pageLayout is not "table"', () => {
      render(<PeopleAndInformation {...defaultProps} pageLayout="list" />);

      expect(screen.getByTestId('information-to-display')).toBeInTheDocument();
      expect(screen.queryByTestId('information-to-display-table')).not.toBeInTheDocument();
    });

    it('renders InformationToDisplayTable when pageLayout is "table"', () => {
      render(<PeopleAndInformation {...defaultProps} pageLayout="table" />);

      expect(screen.getByTestId('information-to-display-table')).toBeInTheDocument();
      expect(screen.queryByTestId('information-to-display')).not.toBeInTheDocument();
    });
  });
});
