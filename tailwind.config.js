/** @type {import('tailwindcss').Config} */
module.exports = {
	content: ['./templates/**/*.html.twig'],
	theme: {
		extend: {
			colors: {
				progress: {
					text: '#fff',
					default: '#337ab7',
					done: '#7cdb62',
					ignored: 'red',
				}
			},
			boxShadow: {},
			fontFamily: {},
			fontSize: {},
			transitionProperty: {
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
	plugins: [],
}
