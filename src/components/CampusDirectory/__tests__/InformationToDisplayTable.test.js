import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

// Mock @wordpress/components: RadioControl and CheckboxControl
jest.mock('@wordpress/components', () => ({
  RadioControl: ({ selected, options, onChange }) => (
    <div data-testid="radio-control" data-selected={selected}>
      {options.map(opt => (
        <button key={String(opt.value)} onClick={() => onChange(String(opt.value))}>
          {opt.label}
        </button>
      ))}
    </div>
  ),
  CheckboxControl: ({ label, checked, onChange, disabled }) => (
    <label>
      <input
        type="checkbox"
        aria-label={label}
        checked={checked}
        disabled={disabled}
        onChange={e => onChange(e.target.checked)}
      />
      {label}
    </label>
  ),
  TextareaControl: () => null,
}), { virtual: true });

// Mock the child CheckboxGroupControl so this file tests wiring, not its internals
// (CheckboxGroupControl has its own suite in CheckboxGroupControl.test.js).
jest.mock('../CheckboxGroupControl', () => (props) => (
  <div
    data-testid="checkbox-group-control"
    data-attribute-str={props.attributeStr}
    data-current-attributes={props.currentAttributes}
    data-checked-by-default={JSON.stringify(props.checkedByDefault)}
    data-labels={JSON.stringify(props.arrOfLabels)}
  />
));

import InformationToDisplayTable from '../InformationToDisplayTable';

describe('InformationToDisplayTable', () => {
  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('linkToProfile attribute wiring', () => {
    it('defaults linkToProfile to true and calls setAttributes when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <InformationToDisplayTable
          setAttributes={setAttributes}
          linkToProfile={undefined}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={undefined}
        />
      );
      expect(setAttributes).toHaveBeenCalledWith({ linkToProfile: true });
      expect(screen.getByTestId('radio-control')).toHaveAttribute('data-selected', 'true');
    });

    it('respects an explicit linkToProfile value without re-calling setAttributes for it', () => {
      const setAttributes = jest.fn();
      render(
        <InformationToDisplayTable
          setAttributes={setAttributes}
          linkToProfile={false}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={undefined}
        />
      );
      expect(setAttributes).not.toHaveBeenCalledWith({ linkToProfile: true });
      expect(screen.getByTestId('radio-control')).toHaveAttribute('data-selected', 'false');
    });

    it('updates linkToProfile via the radio control onChange', () => {
      const setAttributes = jest.fn();
      render(
        <InformationToDisplayTable
          setAttributes={setAttributes}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={undefined}
        />
      );
      setAttributes.mockClear();

      fireEvent.click(screen.getByText('No'));

      expect(setAttributes).toHaveBeenCalledWith({ linkToProfile: false });
    });
  });

  describe('linkOutToCampusDirectory attribute wiring', () => {
    it('defaults linkOutToCampusDirectory to true and calls setAttributes when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <InformationToDisplayTable
          setAttributes={setAttributes}
          linkToProfile={true}
          linkOutToCampusDirectory={undefined}
          strInformationTypesTable={undefined}
        />
      );
      expect(setAttributes).toHaveBeenCalledWith({ linkOutToCampusDirectory: true });
    });

    it('toggles linkOutToCampusDirectory via the checkbox onChange', () => {
      const setAttributes = jest.fn();
      render(
        <InformationToDisplayTable
          setAttributes={setAttributes}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={undefined}
        />
      );
      setAttributes.mockClear();

      fireEvent.click(screen.getByLabelText('Link to campusdirectory.ucsc.edu'));

      expect(setAttributes).toHaveBeenCalledWith({ linkOutToCampusDirectory: false });
    });

    it('disables the checkbox when linkToProfile is false', () => {
      render(
        <InformationToDisplayTable
          setAttributes={jest.fn()}
          linkToProfile={false}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={undefined}
        />
      );
      expect(screen.getByLabelText('Link to campusdirectory.ucsc.edu')).toBeDisabled();
    });

    it('enables the checkbox when linkToProfile is true', () => {
      render(
        <InformationToDisplayTable
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={undefined}
        />
      );
      expect(screen.getByLabelText('Link to campusdirectory.ucsc.edu')).not.toBeDisabled();
    });
  });

  describe('table layout heading', () => {
    it('shows "Table Layout Information to Display"', () => {
      render(
        <InformationToDisplayTable
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={undefined}
        />
      );
      expect(
        screen.getByText('Table Layout Information to Display')
      ).toBeInTheDocument();
    });
  });

  describe('CheckboxGroupControl callback contract', () => {
    it('passes strInformationTypesTable as currentAttributes', () => {
      const strInformationTypesTable = JSON.stringify({ Title: true });
      render(
        <InformationToDisplayTable
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={strInformationTypesTable}
        />
      );
      expect(screen.getByTestId('checkbox-group-control')).toHaveAttribute(
        'data-current-attributes',
        strInformationTypesTable
      );
    });

    it('passes "strInformationTypesTable" as the attributeStr', () => {
      render(
        <InformationToDisplayTable
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={undefined}
        />
      );
      expect(screen.getByTestId('checkbox-group-control')).toHaveAttribute(
        'data-attribute-str',
        'strInformationTypesTable'
      );
    });

    it('passes the expected default checked labels', () => {
      render(
        <InformationToDisplayTable
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={undefined}
        />
      );
      const expected = ['Title', 'Department', 'Phone', 'Campus Email'];
      expect(screen.getByTestId('checkbox-group-control')).toHaveAttribute(
        'data-checked-by-default',
        JSON.stringify(expected)
      );
    });

    it('passes the full list of table information-to-display labels (no Photo)', () => {
      render(
        <InformationToDisplayTable
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypesTable={undefined}
        />
      );
      const expected = [
        'Pronouns',
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
      expect(screen.getByTestId('checkbox-group-control')).toHaveAttribute(
        'data-labels',
        JSON.stringify(expected)
      );
    });
  });
});
