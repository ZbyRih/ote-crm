const path = require('path');
const webpack = require('webpack');

const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const WPManifestPlugin = require('webpack-manifest-plugin');
const BundleTracker = require('webpack-bundle-tracker');
const BundleClean = require('webpack-bundle-clean');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const FriendlyErrorsWebpackPlugin = require('friendly-errors-webpack-plugin');
const {
	styles
} = require('@ckeditor/ckeditor5-dev-utils');

const isDev = process.argv.indexOf("development") !== -1; // detect --mode development

console.info("");
console.info("Webpack building output in " + (isDev ? "DEV" : "PROD") + " mode");
console.info("");

const app = {
	target: 'web',
	devtool: isDev ? 'cheap-module-source-map' : 'none',
	devServer: {
		stats: 'minimal',
	},
	entry: {
		'app_js': [
			'./app/assets/js/libs/jquery-1.12.4.min.js',
			'./app/assets/js/libs/jquery.cookie.js',
			'./app/assets/js/libs/jquery.nanoscroller.min.js',
			'./app/assets/js/libs/bootstrap.min.js',
			'./app/assets/js/libs/bootstrap-datepicker.js',
			'./app/assets/js/libs/bootstrap-datepicker.cs.js',
			'./app/assets/js/libs/bootstrap-colorpicker.min.js',
			'./app/assets/js/libs/bootstrap-select/js/bootstrap-select.min.js',
			'./app/assets/js/libs/jquery.popupoverlay.js',
			'./app/assets/js/libs/jquery.mask.js',
			'./app/assets/js/libs/jquery.dataTables.min.js',
			'./app/assets/js/libs/bootstrap-tagsinput.min.js',
			'./app/assets/js/libs/typeahead.jquery.min.js',

			'./app/assets/js/material/App.js',
			'./app/assets/js/material/AppNavigation.js',
			'./app/assets/js/material/AppOffcanvas.js',
			'./app/assets/js/material/AppCard.js',
			'./app/assets/js/material/AppForm.js',
			'./app/assets/js/material/AppNavSearch.js',
			'./app/assets/js/material/AppVendor.js',

			'./node_modules/select2/dist/js/select2.min.js',
			'./node_modules/select2/dist/js/i18n/cs.js',

			'./app/assets/js/app.js',

			'./app/assets/js/admin/admin.js',
			'./app/assets/js/admin/admin.link.js',
			'./app/assets/js/admin/admin.tags.js',
			'./app/assets/js/admin/admin.autonum.js',
			'./app/assets/js/admin/admin.forms.js',
			'./app/assets/js/admin/admin.lists.js',
			'./app/assets/js/admin/admin.popup.js',
			'./app/assets/js/admin/admin.modal.js',

			'./vendor/ublaboo/datagrid/assets/dist/datagrid.js',
			'./vendor/ublaboo/datagrid/assets/dist/datagrid-instant-url-refresh.js',
			'./vendor/ublaboo/datagrid/assets/dist/datagrid-spinners.js',
		],
		'plot_js': [
			'./app/assets/js/libs/jqplot/autoresize.jquery.min.js',
			'./app/assets/js/libs/jqplot/excanvas.min.js',
			'./app/assets/js/libs/jqplot/jquery.jqplot.min.js',
		]
	},
	module: {
		rules: [{
				test: /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/,
				use: ['raw-loader']
			},
			{
				test: /\.js$/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [require('@babel/preset-env')]
					}
				},
				exclude: /node_modules/
			},
			{
				test: /ckeditor5-[^/\\]+[/\\]theme[/\\].+\.css/,
				use: [{
						loader: 'style-loader',
						options: {
							singleton: true
						}
					},
					{
						loader: 'postcss-loader',
						options: styles.getPostCssConfig({
							themeImporter: {
								themePath: require.resolve('@ckeditor/ckeditor5-theme-lark')
							},
							minify: true
						})
					}
				]
			},
			{
				test: require.resolve('jquery'),
				use: [{
					loader: 'expose-loader',
					options: 'jQuery'
				}, {
					loader: 'expose-loader',
					options: '$'
				}]
			},
		]
	},
	output: {
		path: path.resolve(__dirname, './../dist/js/'),
		filename: isDev ? '[name].js' : '[name].[chunkhash].js',
		chunkFilename: isDev ? '[name].js' : '[name].[chunkhash].js',
	},
	resolve: {
		alias: {
			Theme: path.resolve(__dirname, './app/assets/theme/')
		}
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: isDev ? "[name].css" : "[name].[chunkhash].css",
			chunkFilename: "[name].css"
		}),
		// BC: import jQuery to old plugins... ¯\_(ツ)_/¯
		new webpack.ProvidePlugin({
			"$": "jquery",
			"jquery": "jquery",
			"jQuery": "jquery",
			"window.$": "jquery",
			"window.jQuery": "jquery",
			'window.Nette': 'nette-forms',
		}),
		new WPManifestPlugin()
	].concat(isDev ? [
		new FriendlyErrorsWebpackPlugin({
			clearConsole: true
		}),
	] : [
		new CleanWebpackPlugin(['./../dist/js/*.*']),
		new BundleClean({
			path: __dirname,
			filename: './temp/webpack-stats.json'
		}),
		new BundleTracker({
			path: __dirname,
			filename: './temp/webpack-stats.json',
			indent: '  '
		}),
	])
};

module.exports = app;