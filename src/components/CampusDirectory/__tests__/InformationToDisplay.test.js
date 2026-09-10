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

import InformationToDisplay from '../InformationToDisplay';

describe('InformationToDisplay', () => {
  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('linkToProfile attribute wiring', () => {
    it('defaults linkToProfile to true and calls setAttributes when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <InformationToDisplay
          setAttributes={setAttributes}
          linkToProfile={undefined}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="list"
        />
      );
      expect(setAttributes).toHaveBeenCalledWith({ linkToProfile: true });
      expect(screen.getByTestId('radio-control')).toHaveAttribute('data-selected', 'true');
    });

    it('respects an explicit linkToProfile value without re-calling setAttributes for it', () => {
      const setAttributes = jest.fn();
      render(
        <InformationToDisplay
          setAttributes={setAttributes}
          linkToProfile={false}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="list"
        />
      );
      expect(setAttributes).not.toHaveBeenCalledWith({ linkToProfile: true });
      expect(screen.getByTestId('radio-control')).toHaveAttribute('data-selected', 'false');
    });

    it('updates linkToProfile via the radio control onChange', () => {
      const setAttributes = jest.fn();
      render(
        <InformationToDisplay
          setAttributes={setAttributes}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="list"
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
        <InformationToDisplay
          setAttributes={setAttributes}
          linkToProfile={true}
          linkOutToCampusDirectory={undefined}
          strInformationTypes={undefined}
          pageLayout="list"
        />
      );
      expect(setAttributes).toHaveBeenCalledWith({ linkOutToCampusDirectory: true });
    });

    it('toggles linkOutToCampusDirectory via the checkbox onChange', () => {
      const setAttributes = jest.fn();
      render(
        <InformationToDisplay
          setAttributes={setAttributes}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="list"
        />
      );
      setAttributes.mockClear();

      fireEvent.click(screen.getByLabelText('Link to campusdirectory.ucsc.edu'));

      expect(setAttributes).toHaveBeenCalledWith({ linkOutToCampusDirectory: false });
    });

    it('disables the checkbox when linkToProfile is false', () => {
      render(
        <InformationToDisplay
          setAttributes={jest.fn()}
          linkToProfile={false}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="list"
        />
      );
      expect(screen.getByLabelText('Link to campusdirectory.ucsc.edu')).toBeDisabled();
    });

    it('enables the checkbox when linkToProfile is true', () => {
      render(
        <InformationToDisplay
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="list"
        />
      );
      expect(screen.getByLabelText('Link to campusdirectory.ucsc.edu')).not.toBeDisabled();
    });
  });

  describe('pageLayout heading', () => {
    it('shows "List Layout Information to Display" when pageLayout is "list"', () => {
      render(
        <InformationToDisplay
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="list"
        />
      );
      expect(
        screen.getByText('List Layout Information to Display')
      ).toBeInTheDocument();
    });

    it('shows "Tiled Layout Information to Display" when pageLayout is not "list"', () => {
      render(
        <InformationToDisplay
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="tiled"
        />
      );
      expect(
        screen.getByText('Tiled Layout Information to Display')
      ).toBeInTheDocument();
    });
  });

  describe('CheckboxGroupControl callback contract', () => {
    it('passes strInformationTypes as currentAttributes', () => {
      const strInformationTypes = JSON.stringify({ Pronouns: true });
      render(
        <InformationToDisplay
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypes={strInformationTypes}
          pageLayout="list"
        />
      );
      expect(screen.getByTestId('checkbox-group-control')).toHaveAttribute(
        'data-current-attributes',
        strInformationTypes
      );
    });

    it('passes "strInformationTypes" as the attributeStr', () => {
      render(
        <InformationToDisplay
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="list"
        />
      );
      expect(screen.getByTestId('checkbox-group-control')).toHaveAttribute(
        'data-attribute-str',
        'strInformationTypes'
      );
    });

    it('passes the expected default checked labels', () => {
      render(
        <InformationToDisplay
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="list"
        />
      );
      const expected = [
        'Photo',
        'Title',
        'Department',
        'Phone',
        'Campus Email',
        'Website',
        'Office Location',
        'Office Hours',
      ];
      expect(screen.getByTestId('checkbox-group-control')).toHaveAttribute(
        'data-checked-by-default',
        JSON.stringify(expected)
      );
    });

    it('passes the full list of information-to-display labels', () => {
      render(
        <InformationToDisplay
          setAttributes={jest.fn()}
          linkToProfile={true}
          linkOutToCampusDirectory={true}
          strInformationTypes={undefined}
          pageLayout="list"
        />
      );
      const expected = [
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
      expect(screen.getByTestId('checkbox-group-control')).toHaveAttribute(
        'data-labels',
        JSON.stringify(expected)
      );
    });
  });
});
