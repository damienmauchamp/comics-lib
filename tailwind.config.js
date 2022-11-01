/** @type {import('tailwindcss').Config} */

const inputsPadding = {
	paddingLeft: '40px',
	paddingRight: '38px',
	mobile: {
		paddingLeftRight: '30px',
	},
};
const inputs = {
	borderRadius: '8px',

	//
	height: '44px',
	fontSize: '17px',
	paddingLeft: '40px',
	paddingRight: '38px',
	padding: `0 ${inputsPadding.paddingRight} 0 ${inputsPadding.paddingLeft}`,
	// width: `calc(100% - ${this.paddingRight} - ${this.paddingLeft}))`,

	//
	mobile: {
		borderRadius: '8px',
		// height: '1.71419em',
		height: '36px',
		paddingLeftRight: '30px',
		padding: `3px ${inputsPadding.mobile.paddingLeftRight} 4px`,
		// width: `calc(100% - (${this.paddingLeftRight} * 2))`,
	}
};

const navBottom = {
	height: '45px'
}

const searchBar = {
	input: {
		// height: '36px', // '52px', // '48px',
		// height: '2.11765em', // '52px', // '48px',
		height: inputs.height,
		'mobile-height': inputs.mobile.height, // '52px', // '48px',
		// maxHeight: '22px', // '38px', // '32px',

	}
}

module.exports = {
	content: ['./templates/**/*.html.twig'],
	theme: {
		extend: {
			colors: {
				background: '#fff',
				search: {
					input: {
						background: '#eeeeef',
						find: '#838287',
						reset: '#838287',
					},
					filters: {
						background: '#eeeeee',
						'background-selected': '#fff',
						color: '#000000',
					}
				},
				progress: {
					text: '#fff',
					default: '#337ab7',
					done: '#7cdb62',
					ignored: 'red',
				}
			},
			borderRadius: {
				'search-input': inputs.borderRadius,
				'search-filter': '6px',
			},
			boxShadow: {},
			fontFamily: {},
			fontSize: {
				'sections-title': ['1.25rem', {
					lineHeight: '1.75rem',
					fontWeight: '700',
				}],
				'comics-item-title': ['0.875rem', {
					lineHeight: '1.25rem',
					fontWeight: '500',
				}],
				'comics-item-subtitle': ['0.75rem', {
					lineHeight: '1rem',
					fontWeight: '400',
				}],
				'comics-item-remaining': ['0.75rem', {
					lineHeight: '1rem',
					fontWeight: '300',
				}],
				home: {}
			},
			height: {
				'nav-bottom': navBottom.height,
				'search-input': searchBar.input.height,
				'search-input-mobile': searchBar.input['mobile-height'],
			},
			minHeight: {
				'search-input': searchBar.input.height,
				// 'search-input-mobile': searchBar.input.mobile.height,
			},
			// maxHeight: {
			// 	'search-input': searchBar.input.maxHeight,
			// },
			margin: {
				'nav-bottom': navBottom.height,
			},
			paddingLeft: {
				input: inputs.paddingLeft,
				'input-mobile': inputs.mobile.paddingLeftRight,
			},
			paddingRight: {
				input: inputs.paddingRight,
				'input-mobile': inputs.mobile.paddingLeftRight,
			},
			padding: {
				input: inputs.padding,
				'input-mobile': inputs.mobile.padding,
				'search-filter': '2px',
			},
			transitionProperty: {
				'search-bar': 'height .6s ease',
				'search-bar-width': 'width .3s',
				'search-bar-opacity': 'opacity .3s cubic-bezier(0.25, 0.1, 0.25, 1)',
				'search-filters-font-weight': 'font-weight .3s',
				'progress-bar': 'width .6s ease',
			},

			screens: {
				// mobile : 320px-480px
				'min-mobile': '320px',
				'max-mobile': {'max': '480px'},
				mobile: {'max': '480px'},
				// tablet : 481px-768px
				'min-tablet': '481px',
				'max-tablet': {'max': '768px'},
				tablet: {'max': '768px'},
				// laptop : 769px-1024px
				'min-laptop': '769px',
				'max-laptop': {'max': '1024px'},
				laptop: {'max': '1024px'},
				// desktop : 1025px-1200px
				'min-desktop': '1025px',
				'max-desktop': {'max': '1200px'},
				desktop: {'max': '1200px'},
				// tv : 1201px-...
				'min-tv': '1201px',
				// 'max-tv': {'max': ''},
				// tv: null,
			}
		},
	},
	plugins: [
		require('tailwindcss-safe-area'),
		require('@tailwindcss/line-clamp')
	],
}
