/** @type {import('tailwindcss').Config} */
module.exports = {
  theme: {
    screens: {
      sm: {max: '600px'},
      xs: {max: '425px'}
    },
    colors: {
      wpGray: '#f0f0f1',
      white: '#ffffff',
      black: '#333',
      yellow: '#f4bf3e',
      blue: '#009fff',
      darkBlue: '#1E73BE',
      green: '#2e8a37',
      red: '#D7263D',
      pink: '#E35899',
      orange: '#f39c12',
      wpBlue: '#007cba',
      yellowFaded: '#fdf4df',
      blueFaded: '#ecf8fe',
      darkBlueFaded: '#ebf2f9',
      greenFaded: '#ecf4ed',
      redFaded: '#fbebed',
      pinkFaded: '#fceff5',
      orangeFaded: '#fef5ea',
      wpBlueFaded: '#c6e0ef',
      grey: {
        100: '#f0f0f1',
        200: '#f7f7f7',
        300: '#ededed',
        400: '#f0f0f1',
        500: '#a6a6a8',
        600: '#8c8c8e',
        700: '#737373',
        800: '#696969',
        900: '#404041'
      }
    },
    borderRadius: {
      none: '0px',
      sm: '5px',
      DEFAULT: '8px',
      lg: '12px'
    },
    extend: {
      spacing: {
        screen: '100vw',
        full: '100%',
        0: '0',
        0.5: '2px',
        1: '4px',
        1.5: '6px',
        2: '8px',
        2.5: '10px',
        3: '12px',
        3.5: '14px',
        4: '16px',
        4.5: '18px',
        5: '20px',
        5.5: '22px',
        6: '24px',
        6.5: '26px',
        7: '28px',
        7.5: '30px',
        8: '32px',
        8.5: '34px',
        9: '36px',
        9.5: '38px',
        10: '40px',
        11: '44px',
        12: '48px',
        14: '56px',
        16: '64px',
        20: '80px',
        24: '96px',
        28: '112px',
        32: '128px',
        36: '144px',
        40: '160px',
        44: '176px',
        48: '192px',
        52: '208px',
        56: '224px',
        60: '240px',
        64: '256px',
        72: '288px',
        80: '320px',
        96: '384px'
      },
      borderRadius: {
        none: '0px',
        sm: '2px',
        DEFAULT: '4px',
        md: '6px',
        lg: '8px',
        xl: '12px',
        '2xl': '16px',
        '3xl': '24px'
      },
      boxShadow: {
        sm: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        DEFAULT: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1)',
        md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1)',
        lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1)',
        xl: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)',
        '2xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
        inner: 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.05)'
      },
      fontFamily: {
        sans: [ 'ui-sans-serif', 'system-ui', '-apple-system', '"Segoe UI"', 'sans-serif' ],
        serif: [ 'ui-serif', 'Georgia', 'Cambria', '"Times New Roman"', 'Times', 'serif' ],
        mono: [ 'ui-monospace', 'Menlo', 'Consolas', 'monospace' ]
      },
      fontSize: {
        0: '0',
        xxs: '11px',
        xs: '12px',
        '2xs': '13px',
        sm: '14px',
        '2sm': '15px',
        base: '16px',
        lg: '18px',
        xl: '20px',
        '2xl': '24px',
        '3xl': '30px',
        '4xl': '36px',
        '5xl': '48px',
        '6xl': '60px',
        '7xl': '72px',
        '8xl': '96px',
        '9xl': '128px'
      },
      letterSpacing: theme => ({
        ...theme( 'width' )
      }),
      lineHeight: theme => ({
        ...theme( 'width' )
      }),
      maxWidth: theme => ({
        ...theme( 'width' ),
        xs: '160px',
        sm: '192px',
        md: '224px',
        lg: '256px',
        xl: '288px',
        '2xl': '336px',
        '3xl': '384px',
        '4xl': '448px',
        '5xl': '512px',
        '6xl': '576px',
        '7xl': '640px'
      }),
      minHeight: theme => ({
        ...theme( 'width' )
      }),
      minWidth: theme => ({
        ...theme( 'width' )
      })
    }
  },
  corePlugins: {
    preflight: false,
    backgroundOpacity: false,
    borderOpacity: false,
    divideOpacity: false,
    placeholderOpacity: false,
    textOpacity: false
  },
  plugins: [
    require( 'tailwindcss-mso' ),
    require( 'tailwindcss-box-shadow' ),
    require( 'tailwindcss-email-variants' )
  ]
};
