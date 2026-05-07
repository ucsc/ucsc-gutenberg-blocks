import { render, screen, act, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';

// Mock WordPress components
jest.mock('@wordpress/components', () => ({
  Panel: ({ children, header, className }) => (
    <div data-testid="panel" data-header={header} className={className}>{children}</div>
  ),
  PanelBody: ({ children, title }) => <div data-testid="panel-body" data-title={title}>{children}</div>,
  PanelRow: ({ children }) => <div data-testid="panel-row">{children}</div>,
  Notice: ({ children, status, isDismissible }) => (
    <div data-testid="notice" data-status={status} data-dismissible={isDismissible}>
      {children}
    </div>
  ),
}), { virtual: true });

// Mock @wordpress/data
const mockLockPostSaving = jest.fn();
const mockUnlockPostSaving = jest.fn();
jest.mock('@wordpress/data', () => ({
  dispatch: (store) => {
    if (store === 'core/editor') {
      return {
        lockPostSaving: mockLockPostSaving,
        unlockPostSaving: mockUnlockPostSaving,
      };
    }
    return {};
  },
}), { virtual: true });

// Mock child components
jest.mock('../../components/CampusDirectory/CampusDirectoryDepartmentDropdown', () => (props) => (
  <div data-testid="cd-department-dropdown" />
));
jest.mock('../../components/DivisionDropdown', () => (props) => (
  <div data-testid="division-dropdown" />
));
jest.mock('../../components/CampusDirectory/PageLayout', () => ({ pageLayout }) => (
  <div data-testid="page-layout" data-layout={pageLayout} />
));
jest.mock('../../components/CampusDirectory/PeopleAndInformation', () => (props) => (
  <div data-testid="people-and-information" />
));

// Capture the block registration config
let registeredBlock = null;
global.wp = {
  blocks: {
    registerBlockType: (name, config) => {
      registeredBlock = { name, ...config };
    },
  },
};

// Default: API returns configured correctly
beforeEach(() => {
  global.fetch = jest.fn(() =>
    Promise.resolve({
      text: () => Promise.resolve(JSON.stringify({ ldap_pass: true, multisite: false })),
    })
  );
  mockLockPostSaving.mockClear();
  mockUnlockPostSaving.mockClear();
});

// Import after mocks are set up
const CampusDirectory = require('../CampusDirectory').default;

// Call the function to trigger registerBlockType
CampusDirectory();

describe('CampusDirectory block', () => {
  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('registration', () => {
    it('registers with the correct block name', () => {
      expect(registeredBlock.name).toBe('ucscblocks/campusdirectory');
    });

    it('has the correct title', () => {
      expect(registeredBlock.title).toBe('Campus Directory');
    });

    it('has the correct icon', () => {
      expect(registeredBlock.icon).toBe('welcome-learn-more');
    });

    it('is in the common category', () => {
      expect(registeredBlock.category).toBe('common');
    });

    it('defines all expected attributes', () => {
      const attrNames = Object.keys(registeredBlock.attributes);
      expect(attrNames).toEqual(
        expect.arrayContaining([
          'pageLayout',
          'automatedFeeds',
          'cruzidList',
          'strFacultyTypes',
          'strStaffTypes',
          'strGradTypes',
          'manualAdd',
          'addCruzids',
          'excludeCruzids',
          'displayDeptartmentAffiliates',
          'linkToProfile',
          'linkOutToCampusDirectory',
          'strInformationTypes',
          'strInformationTypesTable',
          'department',
          'division',
          'deptOrDiv',
        ])
      );
    });
  });

  describe('save', () => {
    it('returns null (server-rendered)', () => {
      expect(registeredBlock.save()).toBeNull();
    });
  });

  describe('edit', () => {
    const Edit = registeredBlock.edit;

    const defaultAttributes = {
      pageLayout: 'list',
      automatedFeeds: true,
      cruzidList: '',
      strFacultyTypes: '',
      strStaffTypes: '',
      strGradTypes: '',
      manualAdd: false,
      addCruzids: '',
      excludeCruzids: '',
      displayDeptartmentAffiliates: false,
      linkToProfile: true,
      linkOutToCampusDirectory: false,
      strInformationTypes: '',
      strInformationTypesTable: '',
      department: 'CMPS',
      division: '---',
      deptOrDiv: 'dept',
    };

    it('renders without crashing', async () => {
      const setAttributes = jest.fn();
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={defaultAttributes} />);
      });
      expect(screen.getByTestId('panel')).toBeInTheDocument();
    });

    it('renders the panel with correct header', async () => {
      const setAttributes = jest.fn();
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={defaultAttributes} />);
      });
      expect(screen.getByTestId('panel')).toHaveAttribute('data-header', 'Directory Block');
    });

    it('renders PageLayout and PeopleAndInformation components', async () => {
      const setAttributes = jest.fn();
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={defaultAttributes} />);
      });
      expect(screen.getByTestId('page-layout')).toBeInTheDocument();
      expect(screen.getByTestId('people-and-information')).toBeInTheDocument();
    });

    it('fetches campus directory requirements on mount', async () => {
      const setAttributes = jest.fn();
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={defaultAttributes} />);
      });
      expect(global.fetch).toHaveBeenCalledWith(
        '/wp-json/ucscgutenbergblocks/v1/campusdirectoryrequirements'
      );
    });

    it('shows error notice when in invalid state (automated feeds with no dept/div)', async () => {
      const setAttributes = jest.fn();
      const invalidAttributes = {
        ...defaultAttributes,
        automatedFeeds: true,
        department: '---',
        division: '---',
      };
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={invalidAttributes} />);
      });
      const notice = screen.getByTestId('notice');
      expect(notice).toHaveAttribute('data-status', 'error');
      expect(notice).toHaveTextContent('Unable to publish');
    });

    it('does not show error notice when a department is selected', async () => {
      const setAttributes = jest.fn();
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={defaultAttributes} />);
      });
      expect(screen.queryByTestId('notice')).not.toBeInTheDocument();
    });

    it('locks post saving when in invalid state', async () => {
      const setAttributes = jest.fn();
      const invalidAttributes = {
        ...defaultAttributes,
        automatedFeeds: true,
        department: '---',
        division: '---',
      };
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={invalidAttributes} />);
      });
      expect(mockLockPostSaving).toHaveBeenCalledWith('campusDirectoryInvalidState');
    });

    it('unlocks post saving when state is valid', async () => {
      const setAttributes = jest.fn();
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={defaultAttributes} />);
      });
      expect(mockUnlockPostSaving).toHaveBeenCalledWith('campusDirectoryInvalidState');
    });

    it('shows configuration error when LDAP password is missing', async () => {
      global.fetch = jest.fn(() =>
        Promise.resolve({
          text: () => Promise.resolve(JSON.stringify({ ldap_pass: false, multisite: false })),
        })
      );
      const setAttributes = jest.fn();
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={defaultAttributes} />);
      });
      expect(screen.getByText('This Block is not Configured Correctly')).toBeInTheDocument();
    });

    it('shows multisite link when LDAP password is missing and multisite is true', async () => {
      global.fetch = jest.fn(() =>
        Promise.resolve({
          text: () => Promise.resolve(JSON.stringify({ ldap_pass: false, multisite: true })),
        })
      );
      const setAttributes = jest.fn();
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={defaultAttributes} />);
      });
      expect(screen.getByText(/network level/)).toBeInTheDocument();
    });

    it('hides the directory panel when not configured correctly', async () => {
      global.fetch = jest.fn(() =>
        Promise.resolve({
          text: () => Promise.resolve(JSON.stringify({ ldap_pass: false, multisite: false })),
        })
      );
      const setAttributes = jest.fn();
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={defaultAttributes} />);
      });
      expect(screen.queryByTestId('panel')).not.toBeInTheDocument();
    });
  });
});
