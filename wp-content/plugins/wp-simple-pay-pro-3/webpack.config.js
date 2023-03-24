/**
 * External dependencies
 */
const path = require( 'path' );

/**
 * WordPress dependencies
 */
const wordpressConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const webpack = require( 'webpack' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const FixStyleOnlyEntriesPlugin = require( 'webpack-fix-style-only-entries' );

/**
 * Creates a generic configuration object based on the type of plugin.
 *
 * @param {string} plugin Type of plugin. Used to determine the entry context.
 * @return {Object}
 */
const createConfig = ( plugin ) => ( {
	...wordpressConfig,
	devtool: 'source-map',
	externals: {
		jquery: 'jQuery',
	},
	context: path.resolve( __dirname, `includes/${ plugin }/assets` ),
	resolve: {
		...wordpressConfig.resolve,
		modules: [
			path.resolve( __dirname, `includes/${ plugin }/assets/js` ),
			'node_modules',
		],
	},
	output: {
		filename: '[name].min.js',
		path: path.resolve( __dirname, `includes/${ plugin }/assets/js` ),
	},
	plugins: [
		new webpack.ProvidePlugin( {
			$: 'jquery',
			jQuery: 'jquery',
		} ),
		new MiniCssExtractPlugin( {
			moduleFilename: ( { name } ) =>
				`./../css/${ name.replace( '-css', '' ) }.min.css`,
		} ),
		new FixStyleOnlyEntriesPlugin(),
	],
} );

const coreGutenbergConfig = () => {
	const coreConfig = createConfig( 'core' );

	return {
		...coreConfig,
		entry: {
			'simpay-admin-page-setup-wizard':
				'./js/admin/pages/setup-wizard/index.js',

			'simpay-admin-form-template-explorer':
				'./js/admin/payment-form/template-explorer/index.js',

			'simpay-block-payment-form': './js/blocks/payment-form/index.js',
			'simpay-block-button': './js/blocks/button/index.js',

			'simpay-admin-notifications': './js/admin/notifications/index.js',

			'simpay-admin-help': './js/admin/help/index.js',

			'simpay-admin-dashboard-widget-report':
				'./js/admin/dashboard-widget-report/index.js',

			// CSS
			'simpay-admin-page-setup-wizard-css':
				'./css/admin/page-setup-wizard.scss',

			'simpay-admin-form-template-explorer-css':
				'./css/admin/form-template-explorer.scss',

			'simpay-block-payment-form-css': './css/blocks/payment-form.scss',

			'simpay-admin-notifications-css': './css/admin/notifications.scss',

			'simpay-admin-help-css': './css/admin/help.scss',

			'simpay-admin-dashboard-widget-report-css':
				'./css/admin/admin-dashboard-widget-report.scss',
		},
		plugins: [
			...coreConfig.plugins,
			new DependencyExtractionWebpackPlugin(),
		],
	};
};

const coreConfig = () => {
	const config = createConfig( 'core' );

	return {
		...config,
		entry: {
			// Javascript.
			'simpay-polyfill': '@babel/polyfill',
			'simpay-admin': './js/admin',
			'simpay-admin-notices': './js/admin/notices.js',
			'simpay-admin-page-smtp': './js/admin/pages/smtp.js',
			'simpay-public': './js/frontend',
			// Create a separate file for `simpay-shared` legacy enqueued script.
			'simpay-public-shared': './js/packages/utils/src/legacy.js',

			// CSS.
			'simpay-admin-css': './css/admin/admin.scss',
			'simpay-admin-all-pages-css': './css/admin/all-pages.scss',
			'simpay-admin-page-smtp-css': './css/admin/page-smtp.scss',
			'simpay-admin-bar-css': './css/admin/admin-bar.scss',
			'simpay-public-css': './css/frontend/public.scss',
		},
	};
};

const proConfig = () => {
	const config = createConfig( 'pro' );

	return {
		...config,
		entry: {
			// Javascript.
			'simpay-admin-pro': './js/admin',
			'simpay-public-pro': './js/frontend',
			'simpay-public-pro-update-payment-method':
				'./js/frontend/update-payment-method.js',

			// CSS.
			'simpay-admin-pro-css': './css/admin/admin.scss',
			'simpay-public-pro-css': './css/frontend/public.scss',
		},
	};
};

module.exports = [ coreConfig, coreGutenbergConfig, proConfig ];
